<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @include('partials.front.styles')

        <title>Trezo - Stok Masuk</title>

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
                    <div class="hero-panel hero-panel-emerald mb-[25px]">
                        <div class="relative z-[1] flex flex-col gap-[18px] p-[22px] md:p-[28px] lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-start gap-[16px]">
                                <div class="flex h-[56px] w-[56px] shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-md">
                                    <i class="material-symbols-outlined !text-[30px]">move_to_inbox</i>
                                </div>
                                <div>
                                    <h5 class="!mb-[4px] !text-white lg:!text-[24px]">Stok Masuk</h5>
                                    <p class="!mb-0 text-sm text-white/80">Catat barang masuk dari pemasok dan barang kembali dari teknisi.</p>
                                </div>
                            </div>
                            <a href="{{ route('stok-keluar.index') }}" class="inline-flex items-center justify-center gap-[7px] rounded-xl border border-white/30 bg-white/15 px-[18px] py-[11px] text-sm font-semibold text-white backdrop-blur-md transition-all hover:bg-white/25">
                                <i class="material-symbols-outlined !text-[20px]">outbox</i>
                                Lihat Stok Keluar
                            </a>
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
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Total Unit Masuk</span>
                                    <h5 class="!mb-0">{{ number_format($totalUnit, 0, ',', '.') }}</h5>
                                </div>
                                <div class="grad-icon grad-success"><i class="material-symbols-outlined !text-[26px]">move_to_inbox</i></div>
                            </div>
                        </div>
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Masuk Hari Ini</span>
                                    <h5 class="!mb-0">{{ number_format($masukHariIni, 0, ',', '.') }}</h5>
                                </div>
                                <div class="grad-icon grad-warning"><i class="material-symbols-outlined !text-[26px]">today</i></div>
                            </div>
                        </div>
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Jenis Barang</span>
                                    <h5 class="!mb-0">{{ $totalBarang }}</h5>
                                </div>
                                <div class="grad-icon grad-info"><i class="material-symbols-outlined !text-[26px]">inventory_2</i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <div class="glass-card mb-[25px] p-[20px] md:p-[25px]">
                        <div class="mb-[20px] flex items-center gap-[12px]">
                            <div class="grad-icon grad-primary !h-[42px] !w-[42px] !rounded-xl"><i class="material-symbols-outlined !text-[22px]">add_box</i></div>
                            <div>
                                <h5 class="!mb-0">Tambah Stok Masuk</h5>
                                <p class="!mb-0 text-xs text-gray-400">Stok barang akan otomatis bertambah setelah disimpan.</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('stok-masuk.store') }}">
                            @csrf
                            <div class="grid grid-cols-1 gap-[18px] sm:grid-cols-2 xl:grid-cols-3">
                                <div class="sm:col-span-2 xl:col-span-3">
                                    <label class="mb-[10px] block font-medium text-black dark:text-white">Barang</label>
                                    <select name="barang_id" required class="glass-select" data-searchable data-placeholder="Pilih barang">
                                        <option value="">Pilih barang</option>
                                        @foreach($barangs as $barang)
                                            <option value="{{ $barang->id }}" data-tipe="{{ $barang->tipe_pelacakan }}" @selected(old('barang_id') == $barang->id)>
                                                {{ $barang->kode_barang }} - {{ $barang->nama_barang }} (stok: {{ $barang->stok }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-[8px] text-xs text-gray-400">Untuk barang <strong>per unit</strong>, sistem membuat Asset ID &amp; barcode otomatis. Untuk <strong>per jumlah</strong>, cukup tambah stok.</p>
                                </div>
                                <div>
                                    <label class="mb-[10px] block font-medium text-black dark:text-white">Jumlah</label>
                                    <input type="number" name="jumlah" min="1" required value="{{ old('jumlah', 1) }}" placeholder="E.g. 10" class="glass-input">
                                </div>
                                <div>
                                    <label class="mb-[10px] block font-medium text-black dark:text-white">Nama Pemasok</label>
                                    <input type="text" name="nama_pemasok" value="{{ old('nama_pemasok') }}" placeholder="E.g. PT Sumber" class="glass-input">
                                </div>
                                <div>
                                    <label class="mb-[10px] block font-medium text-black dark:text-white">Tanggal Masuk</label>
                                    <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', now()->format('Y-m-d')) }}" class="glass-input">
                                </div>
                                <div>
                                    <label class="mb-[10px] block font-medium text-black dark:text-white">Lokasi Penyimpanan</label>
                                    <select name="lokasi_id" class="glass-select">
                                        <option value="">- Tanpa lokasi -</option>
                                        @foreach($lokasis as $lokasi)
                                            <option value="{{ $lokasi->id }}" @selected(old('lokasi_id') == $lokasi->id)>{{ $lokasi->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-[10px] block font-medium text-black dark:text-white">Serial Numbers <span class="text-xs text-gray-400">(opsional, satu per baris)</span></label>
                                    <textarea name="serial_numbers" rows="2" class="glass-input" placeholder="SN-123&#10;SN-124"></textarea>
                                </div>
                            </div>
                            <div class="mt-[22px]">
                                <button type="submit" class="btn-grad">
                                    <i class="material-symbols-outlined !text-[20px]">save</i>
                                    Simpan Stok Masuk
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tabel -->
                    <div class="glass-card mb-[25px]">
                        <div class="flex flex-col gap-[14px] border-b border-white/50 p-[20px] dark:border-white/5 xl:flex-row xl:items-center xl:justify-between">
                            <div>
                                <h5 class="!mb-[2px]">Riwayat Stok Masuk</h5>
                                <p class="!mb-0 text-xs text-gray-400">{{ $stokMasuks->total() }} transaksi</p>
                            </div>
                            <form method="GET" action="{{ route('stok-masuk.index') }}" class="flex flex-col gap-[10px] sm:flex-row sm:items-center">
                                <div class="relative w-full sm:w-[220px]">
                                    <i class="material-symbols-outlined pointer-events-none absolute left-[14px] top-1/2 -translate-y-1/2 !text-[20px] text-gray-400">search</i>
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari barang / pemasok..." class="glass-input !h-[46px] ltr:!pl-[44px]">
                                </div>
                                <input type="date" name="date" value="{{ request('date') }}" class="glass-input !h-[46px] sm:!w-[170px]">
                                <select name="tipe" class="glass-select !h-[46px] !py-0 sm:!w-[150px]">
                                    <option value="">Semua Tipe</option>
                                    <option value="masuk" @selected(request('tipe') === 'masuk')>Masuk</option>
                                    <option value="kembali" @selected(request('tipe') === 'kembali')>Kembali</option>
                                </select>
                                <button type="submit" class="btn-grad !py-[11px]">Filter</button>
                                @if(request()->hasAny(['search', 'date', 'tipe']))
                                    <a href="{{ route('stok-masuk.index') }}" class="btn-glass !py-[11px]">Reset</a>
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
                                        <th>Tipe</th>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th>Asset ID</th>
                                        <th>Jumlah</th>
                                        <th>Pemasok / Pengambil</th>
                                        <th>Petugas</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stokMasuks as $index => $item)
                                    <tr>
                                        <td class="text-gray-400">{{ $stokMasuks->firstItem() + $index }}</td>
                                        <td>{{ $item->tanggal->format('d M Y H:i') }}</td>
                                        <td class="text-gray-400">{{ $item->kode_transaksi }}</td>
                                        <td>
                                            @if($item->tipe === \App\Models\Transaksi::TIPE_KEMBALI)
                                                <span class="pill pill-info"><i class="material-symbols-outlined !text-[14px]">assignment_return</i> Kembali</span>
                                            @else
                                                <span class="pill pill-success"><i class="material-symbols-outlined !text-[14px]">move_to_inbox</i> Masuk</span>
                                            @endif
                                        </td>
                                        <td><span class="font-semibold">{{ $item->barang->kode_barang ?? '-' }}</span></td>
                                        <td>{{ $item->barang->nama_barang ?? '-' }}</td>
                                        <td>
                                            @if($item->unit)
                                                <span class="pill pill-primary">{{ $item->unit->asset_id }}</span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td><span class="pill pill-success"><i class="material-symbols-outlined !text-[14px]">arrow_downward</i> +{{ $item->jumlah }}</span></td>
                                        <td class="text-gray-400">{{ $item->nama_pemasok ?: ($item->nama_pengambil ?: '-') }}</td>
                                        <td class="text-gray-400">{{ $item->user ?: '-' }}</td>
                                        <td class="text-gray-400">{{ $item->catatan ?: '-' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="11" class="!text-center text-gray-400">Belum ada riwayat stok masuk.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($stokMasuks->hasPages())
                            <div class="flex flex-wrap items-center justify-between gap-[12px] border-t border-white/50 p-[20px] dark:border-white/5">
                                <p class="!mb-0 text-sm text-gray-400">Menampilkan {{ $stokMasuks->firstItem() }}–{{ $stokMasuks->lastItem() }} dari {{ $stokMasuks->total() }}</p>
                                <div class="flex items-center gap-[8px]">
                                    @if($stokMasuks->onFirstPage())
                                        <span class="icon-btn cursor-not-allowed opacity-40"><i class="material-symbols-outlined !text-[20px]">chevron_left</i></span>
                                    @else
                                        <a href="{{ $stokMasuks->previousPageUrl() }}" class="icon-btn hover:border-primary-500 hover:bg-primary-500 hover:text-white"><i class="material-symbols-outlined !text-[20px]">chevron_left</i></a>
                                    @endif
                                    <span class="pill pill-primary">{{ $stokMasuks->currentPage() }} / {{ $stokMasuks->lastPage() }}</span>
                                    @if($stokMasuks->hasMorePages())
                                        <a href="{{ $stokMasuks->nextPageUrl() }}" class="icon-btn hover:border-primary-500 hover:bg-primary-500 hover:text-white"><i class="material-symbols-outlined !text-[20px]">chevron_right</i></a>
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
