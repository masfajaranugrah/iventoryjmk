<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangUnit;
use App\Models\Lokasi;
use App\Models\OpnameItem;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class LaporanController extends Controller
{
    private const JENIS = ['ringkasan', 'mutasi', 'stok', 'lokasi', 'hilang_rusak', 'selisih'];

    public function index(Request $request): View
    {
        $jenis = in_array($request->jenis, self::JENIS, true) ? $request->jenis : 'mutasi';
        $dari = $request->filled('dari') ? Carbon::parse($request->dari)->startOfDay() : now()->startOfMonth();
        $sampai = $request->filled('sampai') ? Carbon::parse($request->sampai)->endOfDay() : now()->endOfDay();

        $rows = $this->rows($jenis, $request, $dari, $sampai);
        $barangs = Barang::orderBy('nama_barang')->get(['id', 'kode_barang', 'nama_barang']);

        $title = [
            'ringkasan' => 'Laporan Ringkasan Stok (Masuk / Keluar)',
            'mutasi' => 'Laporan Mutasi Stok',
            'stok' => 'Laporan Stok Barang',
            'lokasi' => 'Laporan Stok per Lokasi',
            'hilang_rusak' => 'Laporan Barang Hilang / Rusak',
            'selisih' => 'Laporan Selisih Opname',
        ][$jenis];

        return view('dashboard.laporan', compact(
            'jenis', 'rows', 'barangs', 'dari', 'sampai', 'title'
        ));
    }

    public function export(Request $request): Response
    {
        $jenis = in_array($request->jenis, self::JENIS, true) ? $request->jenis : 'mutasi';
        $dari = $request->filled('dari') ? Carbon::parse($request->dari)->startOfDay() : now()->startOfMonth();
        $sampai = $request->filled('sampai') ? Carbon::parse($request->sampai)->endOfDay() : now()->endOfDay();
        $rows = $this->rows($jenis, $request, $dari, $sampai);

        $html = view('dashboard.exports.laporan-excel', compact('jenis', 'rows', 'dari', 'sampai'))->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="laporan-' . $jenis . '-' . $dari->format('Ymd') . '.xls"',
        ]);
    }

    public function print(Request $request): View
    {
        $jenis = in_array($request->jenis, self::JENIS, true) ? $request->jenis : 'mutasi';
        $dari = $request->filled('dari') ? Carbon::parse($request->dari)->startOfDay() : now()->startOfMonth();
        $sampai = $request->filled('sampai') ? Carbon::parse($request->sampai)->endOfDay() : now()->endOfDay();
        $rows = $this->rows($jenis, $request, $dari, $sampai);
        $title = [
            'ringkasan' => 'Laporan Ringkasan Stok (Masuk / Keluar)',
            'mutasi' => 'Laporan Mutasi Stok',
            'stok' => 'Laporan Stok Barang',
            'lokasi' => 'Laporan Stok per Lokasi',
            'hilang_rusak' => 'Laporan Barang Hilang / Rusak',
            'selisih' => 'Laporan Selisih Opname',
        ][$jenis];

        return view('dashboard.exports.laporan-print', compact('jenis', 'rows', 'dari', 'sampai', 'title'));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rows(string $jenis, Request $request, Carbon $dari, Carbon $sampai): array
    {
        return match ($jenis) {
            'ringkasan' => $this->rowsRingkasan($request, $dari, $sampai),
            'stok' => $this->rowsStok(),
            'lokasi' => $this->rowsLokasi(),
            'hilang_rusak' => $this->rowsHilangRusak(),
            'selisih' => $this->rowsSelisih(),
            default => $this->rowsMutasi($request, $dari, $sampai),
        };
    }

    private function rowsRingkasan(Request $request, Carbon $dari, Carbon $sampai): array
    {
        $masuk = Transaksi::query()
            ->whereNull('voided_at')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->whereIn('tipe', [Transaksi::TIPE_MASUK, Transaksi::TIPE_KEMBALI])
            ->selectRaw('barang_id, SUM(jumlah) as total')
            ->groupBy('barang_id')
            ->pluck('total', 'barang_id');

        $keluar = Transaksi::query()
            ->whereNull('voided_at')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->where('tipe', Transaksi::TIPE_KELUAR)
            ->selectRaw('barang_id, SUM(jumlah) as total')
            ->groupBy('barang_id')
            ->pluck('total', 'barang_id');

        $query = Barang::query()->orderBy('nama_barang');

        if ($request->filled('barang_id')) {
            $query->whereKey($request->barang_id);
        }

        $rows = $query->get()->map(function (Barang $b) use ($masuk, $keluar) {
            $jumlahMasuk = (int) ($masuk[$b->id] ?? 0);
            $jumlahKeluar = (int) ($keluar[$b->id] ?? 0);

            return [
                'Kode Barang' => $b->kode_barang,
                'Nama Barang' => $b->nama_barang,
                'Kategori' => $b->kategori ?? '-',
                'Tipe' => $b->isTrackedPerUnit() ? 'Per Unit' : 'Per Jumlah',
                'Jumlah Masuk' => $jumlahMasuk,
                'Jumlah Keluar' => $jumlahKeluar,
                'Stok Saat Ini' => (int) $b->stok,
                'Satuan' => $b->satuan ?? '-',
            ];
        })->all();

        if ($rows) {
            $rows[] = [
                'Kode Barang' => '',
                'Nama Barang' => 'TOTAL',
                'Kategori' => '',
                'Tipe' => '',
                'Jumlah Masuk' => array_sum(array_column($rows, 'Jumlah Masuk')),
                'Jumlah Keluar' => array_sum(array_column($rows, 'Jumlah Keluar')),
                'Stok Saat Ini' => array_sum(array_column($rows, 'Stok Saat Ini')),
                'Satuan' => '',
            ];
        }

        return $rows;
    }

    private function rowsMutasi(Request $request, Carbon $dari, Carbon $sampai): array
    {
        $query = Transaksi::with(['barang', 'unit', 'lokasi'])
            ->whereBetween('tanggal', [$dari, $sampai]);

        if ($request->filled('barang_id')) {
            $query->where('barang_id', $request->barang_id);
        }

        return $query->orderBy('tanggal')->orderBy('id')->limit(2000)->get()->map(function (Transaksi $t) {
            return [
                'Tanggal' => $t->tanggal->format('d/m/Y H:i'),
                'Kode Transaksi' => $t->kode_transaksi,
                'Tipe' => $t->tipeLabel(),
                'Barang' => $t->barang->nama_barang ?? '-',
                'Kode Barang' => $t->barang->kode_barang ?? '-',
                'Asset ID' => $t->unit->asset_id ?? '-',
                'Jumlah' => $t->jumlah,
                'Pengambil/Pemasok' => $t->nama_pengambil ?: ($t->nama_pemasok ?: '-'),
                'Lokasi' => $t->lokasi->nama ?? '-',
                'Petugas' => $t->user ?? '-',
                'Status' => $t->isVoided() ? 'Dibatalkan' : 'Aktif',
                'Catatan' => $t->catatan ?? '-',
            ];
        })->all();
    }

    private function rowsStok(): array
    {
        return Barang::withCount(['units' => fn ($q) => $q->where('status', BarangUnit::STATUS_TERSEDIA)])
            ->orderBy('nama_barang')
            ->get()
            ->map(function (Barang $b) {
                return [
                    'Kode Barang' => $b->kode_barang,
                    'Nama Barang' => $b->nama_barang,
                    'Kategori' => $b->kategori ?? '-',
                    'Merek' => $b->merek ?? '-',
                    'Tipe' => $b->isTrackedPerUnit() ? 'Per Unit' : 'Per Jumlah',
                    'Stok Tersedia' => $b->stok,
                    'Satuan' => $b->satuan ?? '-',
                ];
            })->all();
    }

    private function rowsLokasi(): array
    {
        $units = BarangUnit::with(['barang', 'lokasi'])
            ->where('status', '!=', BarangUnit::STATUS_DIBATALKAN)
            ->orderBy('lokasi_id')
            ->orderBy('asset_id')
            ->get();

        return $units->map(function (BarangUnit $u) {
            return [
                'Lokasi' => $u->lokasi->nama ?? 'Tanpa Lokasi',
                'Asset ID' => $u->asset_id,
                'Barang' => $u->barang->nama_barang ?? '-',
                'Kode Barang' => $u->barang->kode_barang ?? '-',
                'Serial Number' => $u->serial_number ?? '-',
                'Status' => $u->statusLabel(),
            ];
        })->all();
    }

    private function rowsHilangRusak(): array
    {
        $units = BarangUnit::with(['barang', 'lokasi'])
            ->whereIn('status', [BarangUnit::STATUS_HILANG, BarangUnit::STATUS_RUSAK])
            ->orderBy('status')
            ->orderBy('asset_id')
            ->get();

        return $units->map(function (BarangUnit $u) {
            return [
                'Status' => $u->statusLabel(),
                'Asset ID' => $u->asset_id,
                'Barang' => $u->barang->nama_barang ?? '-',
                'Kode Barang' => $u->barang->kode_barang ?? '-',
                'Serial Number' => $u->serial_number ?? '-',
                'Lokasi' => $u->lokasi->nama ?? '-',
                'Catatan' => $u->catatan ?? '-',
            ];
        })->all();
    }

    private function rowsSelisih(): array
    {
        $items = OpnameItem::with(['barang', 'opname'])
            ->whereNotNull('selisih')
            ->where('selisih', '!=', 0)
            ->orderByDesc('id')
            ->get();

        return $items->map(function (OpnameItem $i) {
            return [
                'Kode Opname' => $i->opname->kode_opname ?? '-',
                'Tanggal' => optional($i->opname?->tanggal)->format('d/m/Y') ?? '-',
                'Kode Barang' => $i->barang->kode_barang ?? '-',
                'Barang' => $i->barang->nama_barang ?? '-',
                'Stok Sistem' => $i->stok_sistem,
                'Stok Fisik' => $i->stok_fisik ?? '-',
                'Selisih' => $i->selisih,
                'Catatan' => $i->catatan ?? '-',
            ];
        })->all();
    }
}
