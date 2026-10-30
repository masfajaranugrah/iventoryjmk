<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('partials.front.styles')

        <title>Trezo - Opname {{ $opname->kode_opname }}</title>

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
                    <div class="hero-panel mb-[25px]">
                        <div class="relative z-[1] flex flex-col gap-[18px] p-[22px] md:p-[28px] lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-start gap-[16px]">
                                <div class="flex h-[56px] w-[56px] shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-md">
                                    <i class="material-symbols-outlined !text-[30px]">fact_check</i>
                                </div>
                                <div>
                                    <h5 class="!mb-[4px] !text-white lg:!text-[24px]">{{ $opname->kode_opname }}</h5>
                                    <p class="!mb-0 text-sm text-white/80">
                                        {{ $opname->tanggal->format('d M Y') }} &middot; {{ $opname->lokasi->nama ?? 'Semua Lokasi' }}
                                        &middot; {{ $opname->isSelesai() ? 'Selesai' : 'Berproses' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-[10px]">
                                <a href="{{ route('opname.export', $opname) }}" class="inline-flex items-center justify-center gap-[7px] rounded-xl border border-white/30 bg-white/15 px-[18px] py-[11px] text-sm font-semibold text-white backdrop-blur-md transition-all hover:bg-white/25">
                                    <i class="material-symbols-outlined !text-[20px]">grid_on</i>
                                    Excel
                                </a>
                                <a href="{{ route('opname.print', $opname) }}" target="_blank" class="inline-flex items-center justify-center gap-[7px] rounded-xl border border-white/30 bg-white/15 px-[18px] py-[11px] text-sm font-semibold text-white backdrop-blur-md transition-all hover:bg-white/25">
                                    <i class="material-symbols-outlined !text-[20px]">print</i>
                                    Cetak / PDF
                                </a>
                                <a href="{{ route('opname.index') }}" class="inline-flex items-center justify-center gap-[7px] rounded-xl bg-white px-[18px] py-[11px] text-sm font-semibold text-primary-600 shadow-lg shadow-black/10 transition-all hover:-translate-y-px hover:bg-white/90">
                                    <i class="material-symbols-outlined !text-[20px]">arrow_back</i>
                                    Kembali
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Ringkasan -->
                    <div class="mb-[25px] grid grid-cols-3 gap-[18px]">
                        <div class="glass-card p-[20px]">
                            <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Total Item</span>
                            <h5 class="!mb-0">{{ $stats['total'] }}</h5>
                        </div>
                        <div class="glass-card p-[20px]">
                            <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Sudah Dihitung</span>
                            <h5 class="!mb-0 text-success-600">{{ $stats['terisi'] }}</h5>
                        </div>
                        <div class="glass-card p-[20px]">
                            <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Item Selisih</span>
                            <h5 class="!mb-0 {{ $stats['selisih'] > 0 ? 'text-danger-500' : 'text-success-600' }}">{{ $stats['selisih'] }}</h5>
                        </div>
                    </div>

                    <div class="glass-card mb-[25px]">
                            <div class="flex flex-col gap-[14px] border-b border-white/50 p-[20px] dark:border-white/5 xl:flex-row xl:items-center xl:justify-between">
                                <div>
                                    <h5 class="!mb-[2px]">Daftar Barang</h5>
                                    <p class="!mb-0 text-xs text-gray-400">Masukkan jumlah fisik hasil hitung. Kolom selisih terisi otomatis.</p>
                                </div>
                                <form method="GET" action="{{ route('opname.show', $opname) }}" class="flex flex-wrap items-center gap-[10px]">
                                    <div class="relative w-full sm:w-[200px]">
                                        <i class="material-symbols-outlined pointer-events-none absolute left-[14px] top-1/2 -translate-y-1/2 !text-[20px] text-gray-400">search</i>
                                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari barang..." class="glass-input !h-[46px] ltr:!pl-[44px]">
                                    </div>
                                    <select name="filter" class="glass-select !h-[46px] !py-0 sm:!w-[160px]">
                                        <option value="">Semua Item</option>
                                        <option value="selisih" @selected(request('filter') === 'selisih')>Hanya Selisih</option>
                                    </select>
                                    <button type="submit" class="btn-grad !py-[11px]">Filter</button>
                                    @if(request()->hasAny(['search', 'filter']))
                                        <a href="{{ route('opname.show', $opname) }}" class="btn-glass !py-[11px]">Reset</a>
                                    @endif
                                </form>
                            </div>

                            <form method="POST" action="{{ route('opname.items', $opname) }}">
                                @csrf
                                @method('PUT')
                                <div class="overflow-x-auto p-[12px]">
                                <table class="modern-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Kode</th>
                                            <th>Barang</th>
                                            <th>Tipe</th>
                                            <th class="!text-center">Stok Sistem</th>
                                            <th class="!text-center">Stok Fisik</th>
                                            <th class="!text-center">Selisih</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($items as $index => $item)
                                        <tr>
                                            <td class="text-gray-400">{{ $items->firstItem() + $index }}</td>
                                            <td><span class="font-semibold">{{ $item->barang->kode_barang ?? '-' }}</span></td>
                                            <td>{{ $item->barang->nama_barang ?? '-' }}</td>
                                            <td>
                                                @if($item->barang?->isTrackedPerUnit())
                                                    <span class="pill pill-primary">Per Unit</span>
                                                @else
                                                    <span class="pill pill-dark">Per Jumlah</span>
                                                @endif
                                            </td>
                                            <td class="!text-center font-semibold">{{ $item->stok_sistem }}</td>
                                            <td class="!text-center">
                                                <input type="number" min="0"
                                                    name="items[{{ $item->id }}]"
                                                    value="{{ $item->stok_fisik }}"
                                                    data-sistem="{{ $item->stok_sistem }}"
                                                    class="glass-input !h-[42px] !w-[90px] !py-0 text-center selisih-input"
                                                    @disabled($opname->isSelesai())>
                                            </td>
                                            <td class="!text-center">
                                                <span class="selisih-cell font-semibold
                                                    @if($item->selisih > 0) text-success-600 @elseif($item->selisih < 0) text-danger-500 @else text-gray-400 @endif">
                                                    {{ $item->selisih === null ? '-' : ($item->selisih > 0 ? '+' : '') . $item->selisih }}
                                                </span>
                                            </td>
                                            <td>
                                                <input type="text" name="catatan[{{ $item->id }}]" value="{{ $item->catatan }}" maxlength="255"
                                                    class="glass-input !h-[42px] !py-0" placeholder="Opsional"
                                                    @disabled($opname->isSelesai())>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="!text-center text-gray-400">Tidak ada item.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($items->hasPages())
                                <div class="flex flex-wrap items-center justify-between gap-[12px] border-t border-white/50 p-[20px] dark:border-white/5">
                                    <p class="!mb-0 text-sm text-gray-400">Menampilkan {{ $items->firstItem() }}–{{ $items->lastItem() }} dari {{ $items->total() }}</p>
                                    <div class="flex items-center gap-[8px]">
                                        @if($items->onFirstPage())
                                            <span class="icon-btn cursor-not-allowed opacity-40"><i class="material-symbols-outlined !text-[20px]">chevron_left</i></span>
                                        @else
                                            <a href="{{ $items->previousPageUrl() }}" class="icon-btn hover:border-primary-500 hover:bg-primary-500 hover:text-white"><i class="material-symbols-outlined !text-[20px]">chevron_left</i></a>
                                        @endif
                                        <span class="pill pill-primary">{{ $items->currentPage() }} / {{ $items->lastPage() }}</span>
                                        @if($items->hasMorePages())
                                            <a href="{{ $items->nextPageUrl() }}" class="icon-btn hover:border-primary-500 hover:bg-primary-500 hover:text-white"><i class="material-symbols-outlined !text-[20px]">chevron_right</i></a>
                                        @else
                                            <span class="icon-btn cursor-not-allowed opacity-40"><i class="material-symbols-outlined !text-[20px]">chevron_right</i></span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @unless($opname->isSelesai())
                                <div class="flex flex-wrap items-center justify-between gap-[12px] border-t border-white/50 p-[20px] dark:border-white/5">
                                    <p class="!mb-0 text-xs text-gray-400">Simpan hasil hitung dulu sebelum menyelesaikan opname.</p>
                                    <button type="submit" class="btn-grad">
                                        <i class="material-symbols-outlined !text-[20px]">save</i>
                                        Simpan Hitung Fisik
                                    </button>
                                </div>
                            @endunless
                            </form>
                        </div>

                    @if(auth()->user()?->isAdmin() && !$opname->isSelesai())
                        <div class="glass-card mb-[25px] border-warning-200/70 p-[20px] md:p-[25px]">
                            <div class="flex flex-col gap-[14px] lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex items-start gap-[12px]">
                                    <div class="grad-icon grad-warning !h-[42px] !w-[42px] !rounded-xl"><i class="material-symbols-outlined !text-[22px]">warning</i></div>
                                    <div>
                                        <h5 class="!mb-[2px]">Selesaikan Opname</h5>
                                        <p class="!mb-0 text-xs text-gray-400">
                                            Semua item dengan selisih akan disesuaikan otomatis. Untuk barang per unit, kekurangan ditandai <strong>Hilang</strong> dan kelebihan dibuatkan unit baru.
                                        </p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('opname.finalize', $opname) }}" onsubmit="return confirm('Selesaikan opname dan terapkan penyesuaian stok?');">
                                    @csrf
                                    <button type="submit" class="btn-grad-danger">
                                        <i class="material-symbols-outlined !text-[20px]">done_all</i>
                                        Selesaikan &amp; Sesuaikan
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    <div class="grow"></div>
                    @include('partials.dashboard.footer')
                </div>
            </div>
        </div>

        @include('partials.front.scripts')
        <script>
            document.querySelectorAll('.selisih-input').forEach(function (input) {
                input.addEventListener('input', function () {
                    const sistem = parseInt(input.getAttribute('data-sistem'), 10) || 0;
                    const cell = input.closest('tr').querySelector('.selisih-cell');

                    if (input.value === '') {
                        cell.textContent = '-';
                        cell.className = 'selisih-cell font-semibold text-gray-400';
                        return;
                    }

                    const delta = (parseInt(input.value, 10) || 0) - sistem;
                    cell.textContent = (delta > 0 ? '+' : '') + delta;
                    cell.className = 'selisih-cell font-semibold ' + (delta > 0 ? 'text-success-600' : (delta < 0 ? 'text-danger-500' : 'text-gray-400'));
                });
            });
        </script>
    </body>
</html>
