<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangUnit;
use App\Models\Lokasi;
use App\Models\Transaksi;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use RuntimeException;

class StokKeluarController extends Controller
{
    public function __construct(private StockService $stock) {}

    public function index(Request $request): View
    {
        $query = Transaksi::with(['barang', 'unit', 'lokasi'])
            ->notVoided()
            ->where('tipe', Transaksi::TIPE_KELUAR);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_pengambil', 'like', "%{$search}%")
                    ->orWhere('user', 'like', "%{$search}%")
                    ->orWhere('kode_transaksi', 'like', "%{$search}%")
                    ->orWhereHas('barang', function ($b) use ($search) {
                        $b->where('nama_barang', 'like', "%{$search}%")
                            ->orWhere('kode_barang', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('tanggal', $request->date);
        }

        $stat = Transaksi::notVoided()
            ->where('tipe', Transaksi::TIPE_KELUAR)
            ->selectRaw(
                'COUNT(*) as total_transaksi, '.
                'COALESCE(SUM(jumlah), 0) as total_unit, '.
                'COALESCE(SUM(CASE WHEN tanggal >= ? AND tanggal <= ? THEN jumlah ELSE 0 END), 0) as hari_ini, '.
                'COUNT(DISTINCT CASE WHEN nama_pengambil IS NOT NULL THEN nama_pengambil END) as total_pengambil',
                [today()->startOfDay(), today()->endOfDay()]
            )
            ->first();

        $totalTransaksi = (int) ($stat->total_transaksi ?? 0);
        $totalUnit = (int) ($stat->total_unit ?? 0);
        $keluarHariIni = (int) ($stat->hari_ini ?? 0);
        $totalPengambil = (int) ($stat->total_pengambil ?? 0);

        $stokKeluars = $query->orderByDesc('tanggal')->paginate(10)->withQueryString();
        $barangs = Barang::where('stok', '>', 0)
            ->orderBy('nama_barang')
            ->get(['id', 'kode_barang', 'nama_barang', 'stok', 'satuan', 'tipe_pelacakan']);
        $lokasis = Lokasi::orderBy('nama')->get(['id', 'nama']);

        return view('dashboard.stok-keluar', compact(
            'stokKeluars',
            'barangs',
            'lokasis',
            'totalTransaksi',
            'totalUnit',
            'keluarHariIni',
            'totalPengambil'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'barang_id' => ['required', 'exists:barangs,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'nama_pengambil' => ['required', 'string', 'max:100'],
            'lokasi_id' => ['nullable', 'exists:lokasis,id'],
            'tanggal_keluar' => ['nullable', 'date'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $barang = Barang::findOrFail($data['barang_id']);
        $tanggal = isset($data['tanggal_keluar']) ? Carbon::parse($data['tanggal_keluar']) : null;

        try {
            $this->stock->issue(
                $barang,
                $data['jumlah'],
                $data['nama_pengambil'],
                auth()->user()?->name,
                $data['catatan'] ?? null,
                BarangUnit::STATUS_DI_TEKNISI,
                $data['lokasi_id'] ?? null,
                auth()->id(),
                $tanggal
            );
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('stok-keluar.index')
            ->with('success', 'Stok keluar berhasil dicatat dan stok barang telah diperbarui.');
    }
}
