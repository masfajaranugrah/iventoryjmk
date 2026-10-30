<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangUnit;
use App\Models\Lokasi;
use App\Models\Opname;
use App\Models\OpnameItem;
use App\Services\AuditLogger;
use App\Services\CodeGenerator;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class OpnameController extends Controller
{
    public function __construct(
        private StockService $stock,
        private CodeGenerator $codes,
        private AuditLogger $audit
    ) {}

    public function index(Request $request): View
    {
        $query = Opname::with('lokasi')->withCount([
            'items',
            'items as terisi_count' => fn ($q) => $q->whereNotNull('stok_fisik'),
            'items as selisih_count' => fn ($q) => $q->whereNotNull('selisih')->where('selisih', '!=', 0),
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('kode_opname', 'like', "%{$search}%");
        }

        $opnames = $query->orderByDesc('id')->paginate(15)->withQueryString();
        $lokasis = Lokasi::orderBy('nama')->get(['id', 'nama']);

        $summary = [
            'total' => Opname::count(),
            'proses' => Opname::where('status', Opname::STATUS_PROSES)->count(),
            'selesai' => Opname::where('status', Opname::STATUS_SELESAI)->count(),
        ];

        return view('dashboard.opname', compact('opnames', 'lokasis', 'summary'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'lokasi_id' => ['nullable', 'exists:lokasis,id'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $opname = DB::transaction(function () use ($data) {
            $opname = Opname::create([
                'kode_opname' => $this->codes->opnameCode(),
                'tanggal' => $data['tanggal'],
                'lokasi_id' => $data['lokasi_id'] ?? null,
                'status' => Opname::STATUS_PROSES,
                'user' => auth()->user()?->name,
                'catatan' => $data['catatan'] ?? null,
            ]);

            $lokasiId = $data['lokasi_id'] ?? null;
            $now = now();
            $rows = [];

            $unitCounts = BarangUnit::query()
                ->where('status', BarangUnit::STATUS_TERSEDIA)
                ->when($lokasiId, fn ($q) => $q->where('lokasi_id', $lokasiId))
                ->selectRaw('barang_id, COUNT(*) as total')
                ->groupBy('barang_id')
                ->pluck('total', 'barang_id');

            foreach (Barang::orderBy('nama_barang')->get(['id', 'stok', 'tipe_pelacakan']) as $barang) {
                if ($barang->isTrackedPerUnit()) {
                    $stokSistem = (int) ($unitCounts[$barang->id] ?? 0);
                } else {
                    $stokSistem = $barang->stok;
                }

                $rows[] = [
                    'opname_id' => $opname->id,
                    'barang_id' => $barang->id,
                    'stok_sistem' => $stokSistem,
                    'stok_fisik' => null,
                    'selisih' => null,
                    'catatan' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($rows) {
                OpnameItem::insert($rows);
            }

            return $opname;
        });

        return redirect()
            ->route('opname.show', $opname)
            ->with('success', 'Sesi opname '.$opname->kode_opname.' dibuat. Silakan masukkan hasil hitung fisik.');
    }

    public function show(Request $request, Opname $opname): View
    {
        $query = $opname->items()->with('barang');

        if ($request->filled('filter') && $request->filter === 'selisih') {
            $query->whereNotNull('selisih')->where('selisih', '!=', 0);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('barang', function ($b) use ($search) {
                $b->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_barang', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('id')->paginate(25)->withQueryString();

        $stats = [
            'total' => $opname->items()->count(),
            'terisi' => $opname->items()->whereNotNull('stok_fisik')->count(),
            'selisih' => $opname->items()->whereNotNull('selisih')->where('selisih', '!=', 0)->count(),
        ];

        return view('dashboard.opname-detail', compact('opname', 'items', 'stats'));
    }

    public function updateItems(Request $request, Opname $opname): RedirectResponse
    {
        if ($opname->isSelesai()) {
            return back()->with('error', 'Sesi opname sudah diselesaikan dan tidak bisa diubah.');
        }

        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*' => ['nullable', 'integer', 'min:0'],
            'catatan' => ['nullable', 'array'],
            'catatan.*' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($opname, $data) {
            foreach ($data['items'] as $itemId => $stokFisik) {
                $item = $opname->items()->whereKey($itemId)->first();
                if (! $item) {
                    continue;
                }

                if ($stokFisik === null || $stokFisik === '') {
                    $item->stok_fisik = null;
                    $item->selisih = null;
                } else {
                    $item->stok_fisik = (int) $stokFisik;
                    $item->selisih = (int) $stokFisik - $item->stok_sistem;
                }

                $item->catatan = $data['catatan'][$itemId] ?? null;
                $item->save();
            }
        });

        return back()->with('success', 'Hasil hitung fisik disimpan.');
    }

    public function finalize(Request $request, Opname $opname): RedirectResponse
    {
        if ($opname->isSelesai()) {
            return back()->with('error', 'Sesi opname sudah diselesaikan.');
        }

        $user = auth()->user()?->name;
        $userId = auth()->id();
        $adjusted = 0;

        DB::transaction(function () use ($opname, $user, $userId, &$adjusted) {
            $items = $opname->items()->with('barang')->whereNotNull('selisih')->where('selisih', '!=', 0)->lockForUpdate()->get();

            foreach ($items as $item) {
                $this->stock->applyAdjustment(
                    $item->barang,
                    $item->selisih,
                    $user,
                    'Penyesuaian opname '.$opname->kode_opname,
                    $opname->lokasi_id,
                    $userId
                );
                $adjusted++;
            }

            $opname->update([
                'status' => Opname::STATUS_SELESAI,
                'finalized_at' => now(),
                'user' => $user,
            ]);
        });

        $this->audit->log('opname.finalize', 'Menyelesaikan opname '.$opname->kode_opname, $opname, [
            'adjusted_items' => $adjusted,
        ]);

        return redirect()
            ->route('opname.show', $opname)
            ->with('success', 'Opname '.$opname->kode_opname.' diselesaikan. '.$adjusted.' item disesuaikan.');
    }

    public function destroy(Opname $opname): RedirectResponse
    {
        if ($opname->isSelesai()) {
            return back()->with('error', 'Sesi opname yang sudah selesai tidak bisa dihapus.');
        }

        $opname->delete();

        return redirect()->route('opname.index')->with('success', 'Sesi opname dihapus.');
    }

    public function export(Opname $opname): Response
    {
        $items = $opname->items()->with('barang')->orderBy('id')->get();
        $html = view('dashboard.exports.opname-excel', compact('opname', 'items'))->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="opname-'.$opname->kode_opname.'.xls"',
        ]);
    }

    public function print(Opname $opname): View
    {
        $items = $opname->items()->with('barang')->orderBy('id')->get();

        return view('dashboard.exports.opname-print', compact('opname', 'items'));
    }
}
