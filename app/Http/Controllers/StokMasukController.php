<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Lokasi;
use App\Models\Transaksi;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use RuntimeException;

class StokMasukController extends Controller
{
    public function __construct(private StockService $stock)
    {
    }

    public function index(Request $request): View
    {
        $query = Transaksi::with(['barang', 'unit', 'lokasi'])
            ->notVoided()
            ->whereIn('tipe', [Transaksi::TIPE_MASUK, Transaksi::TIPE_KEMBALI]);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_pemasok', 'like', "%{$search}%")
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

        if ($request->filled('tipe') && in_array($request->tipe, [Transaksi::TIPE_MASUK, Transaksi::TIPE_KEMBALI], true)) {
            $query->where('tipe', $request->tipe);
        }

        $totalTransaksi = Transaksi::notVoided()->whereIn('tipe', [Transaksi::TIPE_MASUK, Transaksi::TIPE_KEMBALI])->count();
        $totalUnit = Transaksi::notVoided()->whereIn('tipe', [Transaksi::TIPE_MASUK, Transaksi::TIPE_KEMBALI])->sum('jumlah');
        $masukHariIni = Transaksi::notVoided()->whereIn('tipe', [Transaksi::TIPE_MASUK, Transaksi::TIPE_KEMBALI])->whereDate('tanggal', today())->sum('jumlah');
        $totalBarang = Barang::count();

        $stokMasuks = $query->orderByDesc('tanggal')->paginate(10)->withQueryString();
        $barangs = Barang::orderBy('nama_barang')
            ->get(['id', 'kode_barang', 'nama_barang', 'stok', 'satuan', 'tipe_pelacakan']);
        $lokasis = Lokasi::orderBy('nama')->get(['id', 'nama']);

        return view('dashboard.stok-masuk', compact(
            'stokMasuks',
            'barangs',
            'lokasis',
            'totalTransaksi',
            'totalUnit',
            'masukHariIni',
            'totalBarang'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'barang_id' => ['required', 'exists:barangs,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'nama_pemasok' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'lokasi_id' => ['nullable', 'exists:lokasis,id'],
            'tanggal_masuk' => ['nullable', 'date'],
            'serial_numbers' => ['nullable', 'string'],
        ]);

        $barang = Barang::findOrFail($data['barang_id']);
        $serials = $this->parseSerials($data['serial_numbers'] ?? null);
        $tanggal = isset($data['tanggal_masuk']) ? Carbon::parse($data['tanggal_masuk']) : null;

        try {
            $this->stock->receive(
                $barang,
                $data['jumlah'],
                $serials,
                $data['lokasi_id'] ?? null,
                auth()->user()?->name,
                $data['nama_pemasok'] ?? null,
                $data['keterangan'] ?? null,
                auth()->id(),
                $tanggal
            );
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $message = $barang->isTrackedPerUnit()
            ? 'Penerimaan berhasil. ' . $data['jumlah'] . ' unit dibuat dengan Asset ID & barcode baru.'
            : 'Stok masuk berhasil disimpan dan stok barang telah diperbarui.';

        return redirect()
            ->route('stok-masuk.index')
            ->with('success', $message);
    }

    /**
     * @return array<int, string>
     */
    private function parseSerials(?string $raw): array
    {
        if (! $raw) {
            return [];
        }

        $lines = preg_split('/[\r\n,]+/', $raw);

        return array_values(array_filter(array_map('trim', $lines), fn ($line) => $line !== ''));
    }
}
