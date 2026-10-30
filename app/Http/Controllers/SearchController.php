<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangUnit;
use App\Models\Lokasi;
use App\Models\Opname;
use App\Models\Transaksi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->q);
        $groups = $q === '' ? [] : $this->cachedPerform($request, $q);

        return view('dashboard.search', compact('q', 'groups'));
    }

    public function suggest(Request $request): JsonResponse
    {
        $q = trim((string) $request->q);

        if (mb_strlen($q) < 2) {
            return response()->json(['groups' => [], 'query' => $q]);
        }

        return response()->json(['groups' => $this->cachedPerform($request, $q), 'query' => $q]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function cachedPerform(Request $request, string $q): array
    {
        $role = $request->user()?->role ?? 'guest';
        $key = 'search:'.$role.':'.md5(mb_strtolower($q));

        return Cache::remember($key, 30, fn () => $this->perform($request, $q));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function perform(Request $request, string $q): array
    {
        $like = '%'.$q.'%';
        $limit = 5;
        $groups = [];

        // 1. Menu aplikasi
        $menus = $this->menus($request, $q);
        if ($menus) {
            $groups[] = ['label' => 'Menu', 'icon' => 'apps', 'items' => $menus];
        }

        // 2. Barang
        $barangs = Barang::select(['id', 'nama_barang', 'kode_barang', 'stok', 'satuan'])
            ->where(function ($q) use ($like) {
                $q->where('nama_barang', 'like', $like)
                    ->orWhere('kode_barang', 'like', $like)
                    ->orWhere('kategori', 'like', $like)
                    ->orWhere('merek', 'like', $like)
                    ->orWhere('model', 'like', $like);
            })
            ->limit($limit)
            ->get()
            ->map(fn (Barang $b) => [
                'title' => $b->nama_barang,
                'subtitle' => $b->kode_barang.' · stok '.$b->stok.' '.($b->satuan ?? ''),
                'url' => route('stok-barang.index', ['search' => $b->kode_barang]),
            ])->all();
        if ($barangs) {
            $groups[] = ['label' => 'Barang', 'icon' => 'inventory_2', 'items' => $barangs];
        }

        // 3. Unit / Asset ID
        $units = BarangUnit::with('barang:id,nama_barang,kode_barang')
            ->select(['id', 'barang_id', 'asset_id', 'serial_number', 'status'])
            ->where(function ($q) use ($like) {
                $q->where('asset_id', 'like', $like)
                    ->orWhere('serial_number', 'like', $like)
                    ->orWhereIn('barang_id', Barang::select('id')->where(function ($b) use ($like) {
                        $b->where('nama_barang', 'like', $like)->orWhere('kode_barang', 'like', $like);
                    }));
            })
            ->limit($limit)
            ->get()
            ->map(fn (BarangUnit $u) => [
                'title' => $u->asset_id,
                'subtitle' => ($u->barang->nama_barang ?? '-').' · '.$u->statusLabel(),
                'url' => route('barang-units.index', ['search' => $u->asset_id]),
            ])->all();
        if ($units) {
            $groups[] = ['label' => 'Unit / Asset', 'icon' => 'qr_code_2', 'items' => $units];
        }

        // 4. Transaksi
        $transaksis = Transaksi::with('barang:id,nama_barang,kode_barang')
            ->notVoided()
            ->select(['id', 'kode_transaksi', 'barang_id', 'tipe', 'tanggal'])
            ->where(function ($q) use ($like) {
                $q->where('kode_transaksi', 'like', $like)
                    ->orWhere('nama_pengambil', 'like', $like)
                    ->orWhere('nama_pemasok', 'like', $like)
                    ->orWhere('user', 'like', $like)
                    ->orWhereIn('barang_id', Barang::select('id')->where(function ($b) use ($like) {
                        $b->where('nama_barang', 'like', $like)->orWhere('kode_barang', 'like', $like);
                    }));
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (Transaksi $t) => [
                'title' => $t->kode_transaksi,
                'subtitle' => $t->tipeLabel().' · '.($t->barang->nama_barang ?? '-').' · '.optional($t->tanggal)->format('d/m/Y'),
                'url' => route('riwayat.index', ['search' => $t->kode_transaksi]),
            ])->all();
        if ($transaksis) {
            $groups[] = ['label' => 'Transaksi', 'icon' => 'swap_horiz', 'items' => $transaksis];
        }

        // 5. Lokasi
        $lokasis = Lokasi::select(['id', 'nama', 'keterangan'])
            ->where(function ($q) use ($like) {
                $q->where('nama', 'like', $like)->orWhere('keterangan', 'like', $like);
            })
            ->limit($limit)
            ->get()
            ->map(fn (Lokasi $l) => [
                'title' => $l->nama,
                'subtitle' => $l->keterangan ?: 'Lokasi',
                'url' => route('barang-units.index', ['search' => $l->nama]),
            ])->all();
        if ($lokasis) {
            $groups[] = ['label' => 'Lokasi', 'icon' => 'location_on', 'items' => $lokasis];
        }

        // 6. Opname
        $opnames = Opname::with('lokasi:id,nama')
            ->select(['id', 'kode_opname', 'tanggal', 'lokasi_id'])
            ->where(function ($q) use ($like) {
                $q->where('kode_opname', 'like', $like)
                    ->orWhereIn('lokasi_id', Lokasi::select('id')->where('nama', 'like', $like));
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (Opname $o) => [
                'title' => $o->kode_opname,
                'subtitle' => optional($o->tanggal)->format('d/m/Y').' · '.($o->lokasi->nama ?? 'Semua Lokasi'),
                'url' => route('opname.show', $o),
            ])->all();
        if ($opnames) {
            $groups[] = ['label' => 'Stock Opname', 'icon' => 'fact_check', 'items' => $opnames];
        }

        return $groups;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function menus(Request $request, string $q): array
    {
        $user = $request->user();
        $isAdmin = $user && $user->role === 'admin';

        $allowed = [
            '/dashboard',
            '/dashboard/stok-barang',
            '/dashboard/barang-units',
            '/dashboard/stok-masuk',
            '/dashboard/stok-keluar',
            '/dashboard/riwayat',
            '/dashboard/opname',
            '/dashboard/laporan',
            '/dashboard/scan-barcode',
        ];

        $needle = Str::lower($q);
        $results = [];

        foreach (config('sidebar') as $section) {
            foreach ($section['items'] as $item) {
                $entries = $item['type'] === 'link'
                    ? [$item]
                    : ($item['sub_items'] ?? []);

                foreach ($entries as $entry) {
                    $url = $entry['url'] ?? null;
                    if (! $url || str_starts_with($url, 'http') || $url === '#') {
                        continue;
                    }
                    if (! $isAdmin && ! in_array($url, $allowed, true)) {
                        continue;
                    }
                    if (Str::contains(Str::lower($entry['name']), $needle)) {
                        $results[] = [
                            'title' => $entry['name'],
                            'subtitle' => $url,
                            'url' => $url,
                        ];
                    }
                }
            }
        }

        return array_slice($results, 0, 8);
    }
}
