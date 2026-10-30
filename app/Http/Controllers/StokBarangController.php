<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangUnit;
use App\Models\Lokasi;
use App\Models\Transaksi;
use App\Services\AuditLogger;
use App\Services\CodeGenerator;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use RuntimeException;

class StokBarangController extends Controller
{
    public function __construct(
        private StockService $stock,
        private CodeGenerator $codes,
        private AuditLogger $audit
    ) {}

    public function index(Request $request)
    {
        $query = Barang::withCount('units');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_barang', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%")
                    ->orWhere('merek', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('satuan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipe')) {
            $query->where('tipe_pelacakan', $request->tipe);
        }

        $stat = Barang::selectRaw(
            'COUNT(*) as total_items, '.
            'COALESCE(SUM(stok), 0) as total_stok, '.
            'COALESCE(SUM(CASE WHEN stok > 0 AND stok <= 10 THEN 1 ELSE 0 END), 0) as low_stock, '.
            'COALESCE(SUM(CASE WHEN stok <= 0 THEN 1 ELSE 0 END), 0) as out_of_stock'
        )->first();

        $totalItems = (int) ($stat->total_items ?? 0);
        $totalStok = (int) ($stat->total_stok ?? 0);
        $lowStock = (int) ($stat->low_stock ?? 0);
        $outOfStock = (int) ($stat->out_of_stock ?? 0);

        $barangs = $query->orderBy('id', 'desc')->paginate(40)->withQueryString();

        $lokasis = Lokasi::orderBy('nama')->get(['id', 'nama']);

        return view('dashboard.stok-barang', compact(
            'barangs',
            'lokasis',
            'totalItems',
            'totalStok',
            'lowStock',
            'outOfStock'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kode_barang' => ['nullable', 'string', 'max:100', 'unique:barangs,kode_barang'],
            'nama_barang' => ['required', 'string', 'max:150'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'merek' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'satuan' => ['nullable', 'string', 'max:50'],
            'tipe_pelacakan' => ['required', Rule::in([Barang::TRACK_UNIT, Barang::TRACK_QTY])],
            'jumlah_awal' => ['nullable', 'integer', 'min:0'],
            'lokasi_id' => ['nullable', 'exists:lokasis,id'],
            'serial_numbers' => ['nullable', 'string'],
        ]);

        $jumlahAwal = (int) ($data['jumlah_awal'] ?? 0);

        $barang = DB::transaction(function () use ($data, $jumlahAwal) {
            $barang = Barang::create([
                'kode_barang' => ($data['kode_barang'] ?? null) ?: $this->codes->sku(),
                'nama_barang' => $data['nama_barang'],
                'kategori' => $data['kategori'] ?? null,
                'merek' => $data['merek'] ?? null,
                'model' => $data['model'] ?? null,
                'satuan' => $data['satuan'] ?? null,
                'tipe_pelacakan' => $data['tipe_pelacakan'],
                'stok' => 0,
            ]);

            if ($jumlahAwal > 0) {
                $serials = $this->parseSerials($data['serial_numbers'] ?? null);
                $this->stock->receive(
                    $barang,
                    $jumlahAwal,
                    $serials,
                    $data['lokasi_id'] ?? null,
                    auth()->user()?->name,
                    null,
                    null,
                    auth()->id()
                );
            }

            return $barang;
        });

        $this->audit->log('barang.create', 'Menambahkan barang '.$barang->nama_barang, $barang, [
            'kode_barang' => $barang->kode_barang,
            'jumlah_awal' => $jumlahAwal,
        ]);

        return redirect()
            ->route('stok-barang.index')
            ->with('success', 'Barang baru berhasil ditambahkan.');
    }

    public function update(Request $request, Barang $barang): RedirectResponse
    {
        $data = $request->validate([
            'kode_barang' => ['required', 'string', 'max:100', 'unique:barangs,kode_barang,'.$barang->id],
            'nama_barang' => ['required', 'string', 'max:150'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'merek' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'satuan' => ['nullable', 'string', 'max:50'],
            'tipe_pelacakan' => ['required', Rule::in([Barang::TRACK_UNIT, Barang::TRACK_QTY])],
        ]);

        if ($barang->isTrackedPerUnit() && $data['tipe_pelacakan'] !== Barang::TRACK_UNIT) {
            return back()->withInput()->with('error', 'Tidak bisa mengubah tipe pelacakan barang yang sudah memiliki unit terdaftar.');
        }

        $before = $barang->only(['kode_barang', 'nama_barang', 'kategori', 'merek', 'model', 'satuan', 'tipe_pelacakan']);
        $barang->update($data);

        $this->audit->log('barang.update', 'Memperbarui barang '.$barang->nama_barang, $barang, [
            'before' => $before,
            'after' => $barang->only(['kode_barang', 'nama_barang', 'kategori', 'merek', 'model', 'satuan', 'tipe_pelacakan']),
        ]);

        return redirect()
            ->route('stok-barang.index')
            ->with('success', 'Data barang berhasil diperbarui.');
    }

    public function destroy(Barang $barang): RedirectResponse
    {
        if ($barang->stok > 0) {
            return back()->with('error', 'Barang masih memiliki stok tersedia. Kosongkan stok terlebih dahulu sebelum menghapus.');
        }

        if ($barang->units()->where('status', '!=', BarangUnit::STATUS_DIBATALKAN)->exists()) {
            return back()->with('error', 'Barang masih memiliki unit aktif. Selesaikan atau batalkan unit terlebih dahulu.');
        }

        $barang->delete();

        $this->audit->log('barang.delete', 'Menghapus barang '.$barang->nama_barang, $barang, [
            'kode_barang' => $barang->kode_barang,
        ]);

        return redirect()
            ->route('stok-barang.index')
            ->with('success', 'Barang berhasil dihapus. Riwayat transaksi tetap tersimpan.');
    }

    public function scan()
    {
        $recent = Transaksi::with(['unit', 'barang'])
            ->notVoided()
            ->whereIn('tipe', [Transaksi::TIPE_KELUAR, Transaksi::TIPE_KEMBALI])
            ->orderByDesc('tanggal')
            ->take(10)
            ->get();

        return view('dashboard.scan-barcode', compact('recent'));
    }

    public function prosesScan(Request $request)
    {
        $data = $request->validate([
            'asset_id' => ['required', 'string'],
        ]);

        $unit = BarangUnit::with(['barang', 'lokasi'])->where('asset_id', $data['asset_id'])->first();

        if (! $unit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Asset ID '.$data['asset_id'].' tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Unit ditemukan.',
            'data' => [
                'asset_id' => $unit->asset_id,
                'kode_barang' => $unit->barang->kode_barang,
                'nama_barang' => $unit->barang->nama_barang,
                'serial_number' => $unit->serial_number,
                'status' => $unit->status,
                'status_label' => $unit->statusLabel(),
                'lokasi' => $unit->lokasi?->nama,
                'is_available' => $unit->isAvailable(),
            ],
        ]);
    }

    public function simpanScan(Request $request)
    {
        $data = $request->validate([
            'asset_id' => ['required', 'string'],
            'mode' => ['required', Rule::in(['keluar', 'kembali'])],
            'nama_pengambil' => ['nullable', 'string', 'max:100'],
            'lokasi_id' => ['nullable', 'exists:lokasis,id'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $user = auth()->user()?->name;

        try {
            if ($data['mode'] === 'keluar') {
                $unit = $this->stock->issueUnit(
                    $data['asset_id'],
                    $data['nama_pengambil'] ?? null,
                    $user,
                    $data['catatan'] ?? null
                );
                $message = $unit->barang->nama_barang.' ('.$unit->asset_id.') keluar.';
            } else {
                $unit = $this->stock->returnUnit(
                    $data['asset_id'],
                    $data['lokasi_id'] ?? null,
                    BarangUnit::STATUS_TERSEDIA,
                    $user,
                    $data['catatan'] ?? null,
                    $data['nama_pengambil'] ?? null
                );
                $message = $unit->barang->nama_barang.' ('.$unit->asset_id.') kembali ke gudang.';
            }
        } catch (RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => [
                'asset_id' => $unit->asset_id,
                'kode_barang' => $unit->barang->kode_barang,
                'nama_barang' => $unit->barang->nama_barang,
                'serial_number' => $unit->serial_number,
                'nama_pengambil' => $unit->transaksis()->latest('id')->value('nama_pengambil'),
                'status' => $unit->status,
                'status_label' => $unit->statusLabel(),
                'lokasi' => $unit->lokasi?->nama,
                'mode' => $data['mode'],
                'stok_barang' => $unit->barang->fresh()->stok,
                'tanggal' => now()->format('d M Y H:i'),
            ],
        ]);
    }

    public function lokasiOptions()
    {
        return Lokasi::orderBy('nama')->get(['id', 'nama']);
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

        return array_values(array_filter(array_map(function ($line) {
            return trim($line);
        }, $lines), fn ($line) => $line !== ''));
    }
}
