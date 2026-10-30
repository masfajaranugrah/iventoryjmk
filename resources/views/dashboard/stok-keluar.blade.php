<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @include('partials.front.styles')

        <title>Trezo - Stok Keluar</title>

        @vite('resources/css/app.css')

    </head>
    <body>
        @include('partials.dashboard.sidebar')
        @include('partials.dashboard.header')
        @include('partials.dashboard.flash')

        <div class="main-content transition-all flex flex-col overflow-hidden min-h-screen" id="main-content">
            <div class="inv-scene">
                @include('partials.dashboard.inv-glow')

                <div class="relative z-10">
                    <!-- Hero -->
                    <div class="hero-panel hero-panel-rose mb-[25px]">
                        <div class="relative z-[1] flex flex-col gap-[18px] p-[22px] md:p-[28px] lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-start gap-[16px]">
                                <div class="flex h-[56px] w-[56px] shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-md">
                                    <i class="material-symbols-outlined !text-[30px]">outbox</i>
                                </div>
                                <div>
                                    <h5 class="!mb-[4px] !text-white lg:!text-[24px]">Stok Keluar</h5>
                                    <p class="!mb-0 text-sm text-white/80">Catat pengambilan barang dan pantau pengurangan stok.</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-[10px]">
                                <a href="{{ route('stok-masuk.index') }}" class="inline-flex items-center justify-center gap-[7px] rounded-xl border border-white/30 bg-white/15 px-[18px] py-[11px] text-sm font-semibold text-white backdrop-blur-md transition-all hover:bg-white/25">
                                    <i class="material-symbols-outlined !text-[20px]">move_to_inbox</i>
                                    Stok Masuk
                                </a>
                                <a href="/dashboard/scan-barcode" class="inline-flex items-center justify-center gap-[7px] rounded-xl bg-white px-[18px] py-[11px] text-sm font-semibold text-danger-600 shadow-lg shadow-black/10 transition-all hover:-translate-y-px hover:bg-white/90">
                                    <i class="material-symbols-outlined !text-[20px]">qr_code_scanner</i>
                                    Scan
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Ringkasan -->
                    <div class="mb-[25px] grid grid-cols-1 gap-[18px] sm:grid-cols-2 xl:grid-cols-4">
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Total Transaksi</span>
                                    <h5 class="!mb-0">{{ $totalTransaksi }}</h5>
                                </div>
                                <div class="grad-icon grad-primary"><i class="material-symbols-outlined !text-[26px]">receipt_long</i></div>
                            </div>
                        </div>
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Total Unit Keluar</span>
                                    <h5 class="!mb-0">{{ number_format($totalUnit, 0, ',', '.') }}</h5>
                                </div>
                                <div class="grad-icon grad-danger"><i class="material-symbols-outlined !text-[26px]">outbox</i></div>
                            </div>
                        </div>
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Keluar Hari Ini</span>
                                    <h5 class="!mb-0">{{ number_format($keluarHariIni, 0, ',', '.') }}</h5>
                                </div>
                                <div class="grad-icon grad-warning"><i class="material-symbols-outlined !text-[26px]">today</i></div>
                            </div>
                        </div>
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Jumlah Pengambil</span>
                                    <h5 class="!mb-0">{{ $totalPengambil }}</h5>
                                </div>
                                <div class="grad-icon grad-info"><i class="material-symbols-outlined !text-[26px]">group</i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <div class="glass-card mb-[25px] p-[20px] md:p-[25px]">
                        <div class="mb-[20px] flex items-center gap-[12px]">
                            <div class="grad-icon grad-danger !h-[42px] !w-[42px] !rounded-xl"><i class="material-symbols-outlined !text-[22px]">remove_circle</i></div>
                            <div>
                                <h5 class="!mb-0">Catat Stok Keluar</h5>
                                <p class="!mb-0 text-xs text-gray-400">Pastikan stok mencukupi sebelum menyimpan.</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('stok-keluar.store') }}">
                            @csrf
                            <div class="grid grid-cols-1 gap-[18px] sm:grid-cols-2 xl:grid-cols-3">
                                <div class="sm:col-span-2 xl:col-span-3">
                                    <label class="mb-[10px] block font-medium text-black dark:text-white">Barang</label>
                                    <select name="barang_id" required class="glass-select" data-searchable data-placeholder="Pilih barang">
                                        <option value="">Pilih barang</option>
                                        @foreach($barangs as $barang)
                                            <option value="{{ $barang->id }}" @selected(old('barang_id') == $barang->id)>
                                                {{ $barang->kode_barang }} - {{ $barang->nama_barang }} (stok: {{ $barang->stok }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-[8px] text-xs text-gray-400">Barang per unit akan otomatis mengambil unit berstatus <strong>Tersedia</strong> (FIFO). Untuk pengambilan unit tertentu, gunakan menu Scan Barcode.</p>
                                </div>
                                <div>
                                    <label class="mb-[10px] block font-medium text-black dark:text-white">Jumlah</label>
                                    <input type="number" name="jumlah" min="1" required value="{{ old('jumlah', 1) }}" placeholder="E.g. 1" class="glass-input">
                                </div>
                                <div>
                                    <label class="mb-[10px] block font-medium text-black dark:text-white">Nama Pengambil</label>
                                    <input type="text" name="nama_pengambil" required maxlength="100" value="{{ old('nama_pengambil') }}" placeholder="E.g. Andi" class="glass-input">
                                </div>
                                <div>
                                    <label class="mb-[10px] block font-medium text-black dark:text-white">Tanggal Keluar</label>
                                    <input type="date" name="tanggal_keluar" value="{{ old('tanggal_keluar', now()->format('Y-m-d')) }}" class="glass-input">
                                </div>
                                <div>
                                    <label class="mb-[10px] block font-medium text-black dark:text-white">Tujuan / Lokasi</label>
                                    <select name="lokasi_id" class="glass-select">
                                        <option value="">- Tanpa lokasi -</option>
                                        @foreach($lokasis as $lokasi)
                                            <option value="{{ $lokasi->id }}" @selected(old('lokasi_id') == $lokasi->id)>{{ $lokasi->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-[10px] block font-medium text-black dark:text-white">Catatan</label>
                                    <input type="text" name="catatan" maxlength="255" value="{{ old('catatan') }}" placeholder="Opsional" class="glass-input">
                                </div>
                            </div>
                            <div class="mt-[22px]">
                                <button type="submit" class="btn-grad-danger">
                                    <i class="material-symbols-outlined !text-[20px]">save</i>
                                    Simpan Stok Keluar
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tabel -->
                    <div class="glass-card mb-[25px]">
                        <div class="flex flex-col gap-[14px] border-b border-white/50 p-[20px] dark:border-white/5 xl:flex-row xl:items-center xl:justify-between">
                            <div>
                                <h5 class="!mb-[2px]">Riwayat Stok Keluar</h5>
                                <p class="!mb-0 text-xs text-gray-400">{{ $stokKeluars->total() }} transaksi</p>
                            </div>
                            <form method="GET" action="{{ route('stok-keluar.index') }}" class="flex flex-col gap-[10px] sm:flex-row sm:items-center">
                                <div class="relative w-full sm:w-[220px]">
                                    <i class="material-symbols-outlined pointer-events-none absolute left-[14px] top-1/2 -translate-y-1/2 !text-[20px] text-gray-400">search</i>
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari barang / pengambil..." class="glass-input !h-[46px] ltr:!pl-[44px]">
                                </div>
                                <input type="date" name="date" value="{{ request('date') }}" class="glass-input !h-[46px] sm:!w-[170px]">
                                <button type="submit" class="btn-grad !py-[11px]">Filter</button>
                                @if(request('search') || request('date'))
                                    <a href="{{ route('stok-keluar.index') }}" class="btn-glass !py-[11px]">Reset</a>
                                @endif
                            </form>
                        </div>

                        <div class="overflow-x-auto p-[12px]">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Kode Transaksi</th>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th>Asset ID</th>
                                        <th>Jumlah</th>
                                        <th>Nama Pengambil</th>
                                        <th>Petugas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stokKeluars as $index => $item)
                                    <tr>
                                        <td class="text-gray-400">{{ $stokKeluars->firstItem() + $index }}</td>
                                        <td>{{ $item->tanggal->format('d M Y H:i') }}</td>
                                        <td class="text-gray-400">{{ $item->kode_transaksi }}</td>
                                        <td><span class="font-semibold">{{ $item->barang->kode_barang ?? '-' }}</span></td>
                                        <td>{{ $item->barang->nama_barang ?? '-' }}</td>
                                        <td>
                                            @if($item->unit)
                                                <span class="pill pill-primary">{{ $item->unit->asset_id }}</span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td><span class="pill pill-danger"><i class="material-symbols-outlined !text-[14px]">arrow_upward</i> -{{ $item->jumlah }}</span></td>
                                        <td>{{ $item->nama_pengambil ?: '-' }}</td>
                                        <td class="text-gray-400">{{ $item->user ?: '-' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="!text-center text-gray-400">Belum ada riwayat stok keluar.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($stokKeluars->hasPages())
                            <div class="flex flex-wrap items-center justify-between gap-[12px] border-t border-white/50 p-[20px] dark:border-white/5">
                                <p class="!mb-0 text-sm text-gray-400">Menampilkan {{ $stokKeluars->firstItem() }}–{{ $stokKeluars->lastItem() }} dari {{ $stokKeluars->total() }}</p>
                                <div class="flex items-center gap-[8px]">
                                    @if($stokKeluars->onFirstPage())
                                        <span class="icon-btn cursor-not-allowed opacity-40"><i class="material-symbols-outlined !text-[20px]">chevron_left</i></span>
                                    @else
                                        <a href="{{ $stokKeluars->previousPageUrl() }}" class="icon-btn hover:border-primary-500 hover:bg-primary-500 hover:text-white"><i class="material-symbols-outlined !text-[20px]">chevron_left</i></a>
                                    @endif
                                    <span class="pill pill-primary">{{ $stokKeluars->currentPage() }} / {{ $stokKeluars->lastPage() }}</span>
                                    @if($stokKeluars->hasMorePages())
                                        <a href="{{ $stokKeluars->nextPageUrl() }}" class="icon-btn hover:border-primary-500 hover:bg-primary-500 hover:text-white"><i class="material-symbols-outlined !text-[20px]">chevron_right</i></a>
                                    @else
                                        <span class="icon-btn cursor-not-allowed opacity-40"><i class="material-symbols-outlined !text-[20px]">chevron_right</i></span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="grow"></div>
                    @include('partials.dashboard.footer')
                </div>
            </div>
        </div>

        @include('partials.front.scripts')
        @include('partials.dashboard.searchable-select')
    </body>
</html>
