<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangUnit;
use App\Models\Lokasi;
use App\Models\Transaksi;
use App\Services\CodeGenerator;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BarangUnitController extends Controller
{
    public function __construct(private CodeGenerator $codes) {}

    public function index(Request $request): View
    {
        $activeBarangIds = Barang::select('id');
        $query = BarangUnit::with(['barang', 'lokasi'])->whereIn('barang_id', $activeBarangIds);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('asset_id', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhereHas('barang', function ($b) use ($search) {
                        $b->where('nama_barang', 'like', "%{$search}%")
                            ->orWhere('kode_barang', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('barang_id')) {
            $query->where('barang_id', $request->barang_id);
        }

        if ($request->filled('lokasi_id')) {
            $query->where('lokasi_id', $request->lokasi_id);
        }

        $units = $query->orderByDesc('id')->paginate(15)->withQueryString();
        $barangs = Barang::where('tipe_pelacakan', Barang::TRACK_UNIT)
            ->orderBy('nama_barang')
            ->get(['id', 'kode_barang', 'nama_barang']);
        $lokasis = Lokasi::orderBy('nama')->get(['id', 'nama']);
        $statusLabels = BarangUnit::statusLabels();

        $statusCounts = BarangUnit::whereIn('barang_id', $activeBarangIds)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $summary = [
            'total' => (int) $statusCounts->sum(),
            'tersedia' => (int) ($statusCounts[BarangUnit::STATUS_TERSEDIA] ?? 0),
            'di_teknisi' => (int) ($statusCounts[BarangUnit::STATUS_DI_TEKNISI] ?? 0),
            'perlu_pemeriksaan' => (int) ($statusCounts[BarangUnit::STATUS_PERLU_PEMERIKSAAN] ?? 0),
        ];

        return view('dashboard.barang-units', compact(
            'units',
            'barangs',
            'lokasis',
            'statusLabels',
            'summary'
        ));
    }

    public function update(Request $request, BarangUnit $unit): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(BarangUnit::statusLabels()))],
            'lokasi_id' => ['nullable', 'exists:lokasis,id'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($unit, $data) {
            $unit = BarangUnit::lockForUpdate()->findOrFail($unit->id);
            $previous = $unit->status;
            $previousLokasi = $unit->lokasi_id;

            $unit->status = $data['status'];
            $unit->lokasi_id = $data['lokasi_id'] ?? null;
            if (array_key_exists('catatan', $data)) {
                $unit->catatan = $data['catatan'];
            }
            $unit->save();

            Transaksi::create([
                'kode_transaksi' => $this->codes->transactionCode(),
                'barang_id' => $unit->barang_id,
                'barang_unit_id' => $unit->id,
                'tipe' => Transaksi::TIPE_PEMERIKSAAN,
                'jumlah' => 1,
                'status_sebelum' => $previous,
                'status_sesudah' => $unit->status,
                'lokasi_id' => $unit->lokasi_id,
                'lokasi_sebelum_id' => $previousLokasi,
                'user' => auth()->user()?->name,
                'user_id' => auth()->id(),
                'catatan' => $data['catatan'] ?? null,
                'tanggal' => now(),
            ]);

            app(StockService::class)->syncUnitStock($unit->barang);
        });

        return redirect()
            ->route('barang-units.index')
            ->with('success', 'Data unit '.$unit->asset_id.' berhasil diperbarui.');
    }

    public function labels(Request $request): View
    {
        $query = BarangUnit::with('barang')->where('status', '!=', BarangUnit::STATUS_DIBATALKAN);

        if ($request->filled('barang_id')) {
            $query->where('barang_id', $request->barang_id);
        }

        if ($request->filled('ids')) {
            $ids = array_filter(explode(',', $request->ids));
            $query->whereIn('asset_id', $ids);
        }

        $units = $query->orderBy('asset_id')->limit(200)->get();

        return view('dashboard.barang-unit-labels', compact('units'));
    }
}
