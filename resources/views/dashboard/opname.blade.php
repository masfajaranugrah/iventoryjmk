<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('partials.front.styles')

        <title>Trezo - Stock Opname</title>

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
                                    <h5 class="!mb-[4px] !text-white lg:!text-[24px]">Stock Opname</h5>
                                    <p class="!mb-0 text-sm text-white/80">Cek fisik stok, bandingkan dengan sistem, dan sesuaikan selisih.</p>
                                </div>
                            </div>
                            <a href="{{ route('laporan.index', ['jenis' => 'selisih']) }}" class="inline-flex items-center justify-center gap-[7px] rounded-xl border border-white/30 bg-white/15 px-[18px] py-[11px] text-sm font-semibold text-white backdrop-blur-md transition-all hover:bg-white/25">
                                <i class="material-symbols-outlined !text-[20px]">summarize</i>
                                Laporan Selisih
                            </a>
                        </div>
                    </div>

                    <!-- Ringkasan -->
                    <div class="mb-[25px] grid grid-cols-3 gap-[18px]">
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Total Sesi</span>
                                    <h5 class="!mb-0">{{ $summary['total'] }}</h5>
                                </div>
                                <div class="grad-icon grad-primary"><i class="material-symbols-outlined !text-[26px]">fact_check</i></div>
                            </div>
                        </div>
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Berproses</span>
                                    <h5 class="!mb-0">{{ $summary['proses'] }}</h5>
                                </div>
                                <div class="grad-icon grad-warning"><i class="material-symbols-outlined !text-[26px]">pending_actions</i></div>
                            </div>
                        </div>
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Selesai</span>
                                    <h5 class="!mb-0">{{ $summary['selesai'] }}</h5>
                                </div>
                                <div class="grad-icon grad-success"><i class="material-symbols-outlined !text-[26px]">check_circle</i></div>
                            </div>
                        </div>
                    </div>

                    @if(auth()->user()?->isAdmin())
                        <div class="glass-card mb-[25px] p-[20px] md:p-[25px]">
                            <div class="mb-[20px] flex items-center gap-[12px]">
                                <div class="grad-icon grad-primary !h-[42px] !w-[42px] !rounded-xl"><i class="material-symbols-outlined !text-[22px]">add_box</i></div>
                                <div>
                                    <h5 class="!mb-0">Mulai Sesi Opname Baru</h5>
                                    <p class="!mb-0 text-xs text-gray-400">Sistem akan mengambil snapshot stok seharusnya untuk semua barang.</p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('opname.store') }}">
                                @csrf
                                <div class="grid grid-cols-1 gap-[18px] sm:grid-cols-3">
                                    <div>
                                        <label class="mb-[10px] block font-medium text-black dark:text-white">Tanggal Opname</label>
                                        <input type="date" name="tanggal" required value="{{ old('tanggal', now()->format('Y-m-d')) }}" class="glass-input">
                                    </div>
                                    <div>
                                        <label class="mb-[10px] block font-medium text-black dark:text-white">Lokasi <span class="text-xs text-gray-400">(opsional)</span></label>
                                        <select name="lokasi_id" class="glass-select">
                                            <option value="">Semua Lokasi</option>
                                            @foreach($lokasis as $lokasi)
                                                <option value="{{ $lokasi->id }}" @selected(old('lokasi_id') == $lokasi->id)>{{ $lokasi->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-[10px] block font-medium text-black dark:text-white">Catatan</label>
                                        <input type="text" name="catatan" maxlength="255" value="{{ old('catatan') }}" placeholder="Opsional" class="glass-input">
                                    </div>
                                </div>
                                <div class="mt-[22px]">
                                    <button type="submit" class="btn-grad">
                                        <i class="material-symbols-outlined !text-[20px]">play_arrow</i>
                                        Mulai Opname
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <!-- Tabel -->
                    <div class="glass-card mb-[25px]">
                        <div class="flex flex-col gap-[14px] border-b border-white/50 p-[20px] dark:border-white/5 xl:flex-row xl:items-center xl:justify-between">
                            <div>
                                <h5 class="!mb-[2px]">Daftar Sesi Opname</h5>
                                <p class="!mb-0 text-xs text-gray-400">{{ $opnames->total() }} sesi</p>
                            </div>
                            <form method="GET" action="{{ route('opname.index') }}" class="flex flex-wrap items-center gap-[10px]">
                                <div class="relative w-full sm:w-[200px]">
                                    <i class="material-symbols-outlined pointer-events-none absolute left-[14px] top-1/2 -translate-y-1/2 !text-[20px] text-gray-400">search</i>
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode opname..." class="glass-input !h-[46px] ltr:!pl-[44px]">
                                </div>
                                <select name="status" class="glass-select !h-[46px] !py-0 sm:!w-[160px]">
                                    <option value="">Semua Status</option>
                                    <option value="proses" @selected(request('status') === 'proses')>Berproses</option>
                                    <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
                                </select>
                                <button type="submit" class="btn-grad !py-[11px]">Filter</button>
                                @if(request()->hasAny(['search', 'status']))
                                    <a href="{{ route('opname.index') }}" class="btn-glass !py-[11px]">Reset</a>
                                @endif
                            </form>
                        </div>

                        <div class="overflow-x-auto p-[12px]">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Tanggal</th>
                                        <th>Lokasi</th>
                                        <th>Progres</th>
                                        <th>Selisih</th>
                                        <th>Status</th>
                                        <th class="!text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($opnames as $index => $opname)
                                    <tr>
                                        <td class="text-gray-400">{{ $opnames->firstItem() + $index }}</td>
                                        <td><span class="font-semibold">{{ $opname->kode_opname }}</span></td>
                                        <td>{{ $opname->tanggal->format('d M Y') }}</td>
                                        <td class="text-gray-400">{{ $opname->lokasi->nama ?? 'Semua' }}</td>
                                        <td class="text-gray-400">{{ $opname->terisi_count }} / {{ $opname->items_count }}</td>
                                        <td>
                                            @if($opname->selisih_count > 0)
                                                <span class="pill pill-danger">{{ $opname->selisih_count }} item</span>
                                            @else
                                                <span class="pill pill-success">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($opname->isSelesai())
                                                <span class="pill pill-success">Selesai</span>
                                            @else
                                                <span class="pill pill-warning">Berproses</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex items-center justify-end gap-[8px]">
                                                <a href="{{ route('opname.show', $opname) }}" class="icon-btn icon-btn-primary" title="Buka">
                                                    <i class="material-symbols-outlined !text-[20px]">open_in_new</i>
                                                </a>
                                                @if(auth()->user()?->isAdmin() && !$opname->isSelesai())
                                                    <button type="button" class="icon-btn icon-btn-danger btn-del"
                                                        data-action="{{ route('opname.destroy', $opname) }}"
                                                        data-kode="{{ $opname->kode_opname }}">
                                                        <i class="material-symbols-outlined !text-[20px]">delete</i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="!text-center text-gray-400">Belum ada sesi opname.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($opnames->hasPages())
                            <div class="flex flex-wrap items-center justify-between gap-[12px] border-t border-white/50 p-[20px] dark:border-white/5">
                                <p class="!mb-0 text-sm text-gray-400">Menampilkan {{ $opnames->firstItem() }}–{{ $opnames->lastItem() }} dari {{ $opnames->total() }}</p>
                                <div class="flex items-center gap-[8px]">
                                    @if($opnames->onFirstPage())
                                        <span class="icon-btn cursor-not-allowed opacity-40"><i class="material-symbols-outlined !text-[20px]">chevron_left</i></span>
                                    @else
                                        <a href="{{ $opnames->previousPageUrl() }}" class="icon-btn hover:border-primary-500 hover:bg-primary-500 hover:text-white"><i class="material-symbols-outlined !text-[20px]">chevron_left</i></a>
                                    @endif
                                    <span class="pill pill-primary">{{ $opnames->currentPage() }} / {{ $opnames->lastPage() }}</span>
                                    @if($opnames->hasMorePages())
                                        <a href="{{ $opnames->nextPageUrl() }}" class="icon-btn hover:border-primary-500 hover:bg-primary-500 hover:text-white"><i class="material-symbols-outlined !text-[20px]">chevron_right</i></a>
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

        <form id="del-form" method="POST" action="" class="hidden">
            @csrf
            @method('DELETE')
        </form>

        @include('partials.front.scripts')
        <script>
            document.querySelectorAll('.btn-del').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (confirm('Hapus sesi opname ' + btn.getAttribute('data-kode') + '?')) {
                        const form = document.getElementById('del-form');
                        form.action = btn.getAttribute('data-action');
                        form.submit();
                    }
                });
            });
        </script>
    </body>
</html>
