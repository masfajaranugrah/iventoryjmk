<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Services\AuditLogger;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class RiwayatController extends Controller
{
    public function __construct(
        private StockService $stock,
        private AuditLogger $audit
    ) {}

    public function index(Request $request): View
    {
        $query = Transaksi::with(['barang', 'unit', 'lokasi']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('kode_transaksi', 'like', "%{$search}%")
                    ->orWhere('nama_pengambil', 'like', "%{$search}%")
                    ->orWhere('nama_pemasok', 'like', "%{$search}%")
                    ->orWhere('user', 'like', "%{$search}%")
                    ->orWhereHas('barang', function ($b) use ($search) {
                        $b->where('nama_barang', 'like', "%{$search}%")
                            ->orWhere('kode_barang', 'like', "%{$search}%");
                    })
                    ->orWhereHas('unit', function ($u) use ($search) {
                        $u->where('asset_id', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('tipe') && array_key_exists($request->tipe, Transaksi::tipeLabels())) {
            $query->where('tipe', $request->tipe);
        }

        if ($request->filled('date')) {
            $query->whereDate('tanggal', $request->date);
        }

        $summaryRow = Transaksi::notVoided()
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN tipe = ? THEN jumlah ELSE 0 END), 0) as masuk, '.
                'COALESCE(SUM(CASE WHEN tipe = ? THEN jumlah ELSE 0 END), 0) as keluar, '.
                'COALESCE(SUM(CASE WHEN tipe = ? THEN jumlah ELSE 0 END), 0) as kembali, '.
                'COUNT(*) as total',
                [Transaksi::TIPE_MASUK, Transaksi::TIPE_KELUAR, Transaksi::TIPE_KEMBALI]
            )
            ->first();

        $summary = [
            'masuk' => (int) ($summaryRow->masuk ?? 0),
            'keluar' => (int) ($summaryRow->keluar ?? 0),
            'kembali' => (int) ($summaryRow->kembali ?? 0),
            'total' => (int) ($summaryRow->total ?? 0),
        ];

        $transaksis = $query->orderByDesc('tanggal')->orderByDesc('id')->paginate(15)->withQueryString();
        $tipeLabels = Transaksi::tipeLabels();

        return view('dashboard.riwayat', compact('transaksis', 'tipeLabels', 'summary'));
    }

    public function void(Request $request, Transaksi $transaksi): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        try {
            $reversal = $this->stock->reverse(
                $transaksi,
                $data['reason'],
                auth()->id(),
                auth()->user()?->name
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->audit->log('transaksi.void', 'Membatalkan transaksi '.$transaksi->kode_transaksi, $transaksi, [
            'reason' => $data['reason'],
            'reversal_code' => $reversal->kode_transaksi,
        ]);

        return back()->with('success', 'Transaksi '.$transaksi->kode_transaksi.' dibatalkan. Transaksi pembatalan: '.$reversal->kode_transaksi.'.');
    }
}
