<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('partials.front.styles')

        <title>Trezo - Unit & Barcode</title>

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
                                    <i class="material-symbols-outlined !text-[30px]">barcode</i>
                                </div>
                                <div>
                                    <h5 class="!mb-[4px] !text-white lg:!text-[24px]">Unit &amp; Barcode</h5>
                                    <p class="!mb-0 text-sm text-white/80">Kelola setiap unit perangkat, status, lokasi, dan cetak label barcode.</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-[10px]">
                                <a href="{{ route('barang-units.labels') }}" target="_blank" class="inline-flex items-center justify-center gap-[7px] rounded-xl bg-white px-[18px] py-[11px] text-sm font-semibold text-primary-600 shadow-lg shadow-black/10 transition-all hover:-translate-y-px hover:bg-white/90">
                                    <i class="material-symbols-outlined !text-[20px]">print</i>
                                    Cetak Semua Label
                                </a>
                                <a href="/dashboard/scan-barcode" class="inline-flex items-center justify-center gap-[7px] rounded-xl border border-white/30 bg-white/15 px-[18px] py-[11px] text-sm font-semibold text-white backdrop-blur-md transition-all hover:bg-white/25">
                                    <i class="material-symbols-outlined !text-[20px]">qr_code_scanner</i>
                                    Scan
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Ringkasan -->
                    <div class="mb-[25px] grid grid-cols-2 gap-[18px] xl:grid-cols-4">
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Total Unit</span>
                                    <h5 class="!mb-0">{{ $summary['total'] }}</h5>
                                </div>
                                <div class="grad-icon grad-primary"><i class="material-symbols-outlined !text-[26px]">inventory</i></div>
                            </div>
                        </div>
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Tersedia</span>
                                    <h5 class="!mb-0">{{ $summary['tersedia'] }}</h5>
                                </div>
                                <div class="grad-icon grad-success"><i class="material-symbols-outlined !text-[26px]">check_circle</i></div>
                            </div>
                        </div>
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Di Teknisi</span>
                                    <h5 class="!mb-0">{{ $summary['di_teknisi'] }}</h5>
                                </div>
                                <div class="grad-icon grad-warning"><i class="material-symbols-outlined !text-[26px]">engineering</i></div>
                            </div>
                        </div>
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Perlu Pemeriksaan</span>
                                    <h5 class="!mb-0">{{ $summary['perlu_pemeriksaan'] }}</h5>
                                </div>
                                <div class="grad-icon grad-danger"><i class="material-symbols-outlined !text-[26px]">build</i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel -->
                    <div class="glass-card mb-[25px]">
                        <div class="flex flex-col gap-[14px] border-b border-white/50 p-[20px] dark:border-white/5 xl:flex-row xl:items-center xl:justify-between">
                            <div>
                                <h5 class="!mb-[2px]">Daftar Unit</h5>
                                <p class="!mb-0 text-xs text-gray-400">{{ $units->total() }} unit terdaftar</p>
                            </div>
                            <form method="GET" action="{{ route('barang-units.index') }}" class="flex flex-wrap items-center gap-[10px]">
                                <div class="relative w-full sm:w-[200px]">
                                    <i class="material-symbols-outlined pointer-events-none absolute left-[14px] top-1/2 -translate-y-1/2 !text-[20px] text-gray-400">search</i>
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Asset ID / serial..." class="glass-input !h-[46px] ltr:!pl-[44px]">
                                </div>
                                <select name="barang_id" class="glass-select !h-[46px] !py-0 sm:!w-[180px]">
                                    <option value="">Semua Barang</option>
                                    @foreach($barangs as $barang)
                                        <option value="{{ $barang->id }}" @selected(request('barang_id') == $barang->id)>{{ $barang->kode_barang }} - {{ $barang->nama_barang }}</option>
                                    @endforeach
                                </select>
                                <select name="status" class="glass-select !h-[46px] !py-0 sm:!w-[170px]">
                                    <option value="">Semua Status</option>
                                    @foreach($statusLabels as $value => $label)
                                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn-grad !py-[11px]">Filter</button>
                                @if(request()->hasAny(['search', 'barang_id', 'status']))
                                    <a href="{{ route('barang-units.index') }}" class="btn-glass !py-[11px]">Reset</a>
                                @endif
                            </form>
                        </div>

                        <div class="overflow-x-auto p-[12px]">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Asset ID</th>
                                        <th>Barang</th>
                                        <th>Serial Number</th>
                                        <th>Status</th>
                                        <th>Lokasi</th>
                                        <th class="!text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($units as $index => $unit)
                                    <tr>
                                        <td class="text-gray-400">{{ $units->firstItem() + $index }}</td>
                                        <td><span class="font-semibold">{{ $unit->asset_id }}</span></td>
                                        <td>
                                            <div>{{ $unit->barang->nama_barang ?? '-' }}</div>
                                            <div class="text-xs text-gray-400">{{ $unit->barang->kode_barang ?? '' }}</div>
                                        </td>
                                        <td class="text-gray-400">{{ $unit->serial_number ?: '-' }}</td>
                                        <td>
                                            @php
                                                $statusPill = [
                                                    'tersedia' => 'pill-success',
                                                    'di_teknisi' => 'pill-warning',
                                                    'terpasang' => 'pill-primary',
                                                    'menunggu_verifikasi' => 'pill-info',
                                                    'perlu_pemeriksaan' => 'pill-warning',
                                                    'rusak' => 'pill-danger',
                                                    'hilang' => 'pill-dark',
                                                ][$unit->status] ?? 'pill-dark';
                                            @endphp
                                            <span class="pill {{ $statusPill }}">{{ $unit->statusLabel() }}</span>
                                        </td>
                                        <td class="text-gray-400">{{ $unit->lokasi->nama ?? '-' }}</td>
                                        <td>
                                            <div class="flex items-center justify-end gap-[8px]">
                                                <a href="{{ route('barang-units.labels', ['ids' => $unit->asset_id]) }}" target="_blank" class="icon-btn icon-btn-primary" title="Cetak label">
                                                    <i class="material-symbols-outlined !text-[20px]">print</i>
                                                </a>
                                                @if(auth()->user()?->isAdmin())
                                                    <button type="button" class="icon-btn btn-edit"
                                                        data-id="{{ $unit->id }}"
                                                        data-asset="{{ $unit->asset_id }}"
                                                        data-nama="{{ $unit->barang->nama_barang ?? '' }}"
                                                        data-status="{{ $unit->status }}"
                                                        data-lokasi="{{ $unit->lokasi_id }}"
                                                        data-catatan="{{ $unit->catatan }}"
                                                        data-action="{{ route('barang-units.update', $unit) }}">
                                                        <i class="material-symbols-outlined !text-[20px]">edit</i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="!text-center text-gray-400">Belum ada unit terdaftar.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($units->hasPages())
                            <div class="flex flex-wrap items-center justify-between gap-[12px] border-t border-white/50 p-[20px] dark:border-white/5">
                                <p class="!mb-0 text-sm text-gray-400">Menampilkan {{ $units->firstItem() }}–{{ $units->lastItem() }} dari {{ $units->total() }}</p>
                                <div class="flex items-center gap-[8px]">
                                    @if($units->onFirstPage())
                                        <span class="icon-btn cursor-not-allowed opacity-40"><i class="material-symbols-outlined !text-[20px]">chevron_left</i></span>
                                    @else
                                        <a href="{{ $units->previousPageUrl() }}" class="icon-btn hover:border-primary-500 hover:bg-primary-500 hover:text-white"><i class="material-symbols-outlined !text-[20px]">chevron_left</i></a>
                                    @endif
                                    <span class="pill pill-primary">{{ $units->currentPage() }} / {{ $units->lastPage() }}</span>
                                    @if($units->hasMorePages())
                                        <a href="{{ $units->nextPageUrl() }}" class="icon-btn hover:border-primary-500 hover:bg-primary-500 hover:text-white"><i class="material-symbols-outlined !text-[20px]">chevron_right</i></a>
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

        @if(auth()->user()?->isAdmin())
            <!-- Modal Edit Unit -->
            <div id="unit-modal" class="fixed inset-0 z-[9998] hidden items-center justify-center p-[20px]">
                <div id="unit-backdrop" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
                <div class="modern-modal max-w-[480px]">
                    <div class="mb-[22px] flex items-start justify-between gap-[12px]">
                        <div class="flex items-center gap-[12px]">
                            <div class="grad-icon grad-primary !h-[44px] !w-[44px] !rounded-xl"><i class="material-symbols-outlined !text-[22px]">build</i></div>
                            <div>
                                <h5 class="!mb-0">Update Unit</h5>
                                <p class="!mb-0 text-xs text-gray-400" id="unit-asset"></p>
                            </div>
                        </div>
                        <button type="button" id="unit-close" class="shrink-0 text-gray-400 transition-all hover:text-danger-500">
                            <i class="material-symbols-outlined !text-[22px]">close</i>
                        </button>
                    </div>
                    <form id="unit-form" method="POST" action="">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 gap-[18px]">
                            <div>
                                <label class="mb-[10px] block font-medium text-black dark:text-white">Status</label>
                                <select name="status" id="unit-status" class="glass-select">
                                    @foreach($statusLabels as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-[10px] block font-medium text-black dark:text-white">Lokasi</label>
                                <select name="lokasi_id" id="unit-lokasi" class="glass-select">
                                    <option value="">- Tanpa lokasi -</option>
                                    @foreach($lokasis as $lokasi)
                                        <option value="{{ $lokasi->id }}">{{ $lokasi->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-[10px] block font-medium text-black dark:text-white">Catatan</label>
                                <input type="text" name="catatan" id="unit-catatan" maxlength="255" placeholder="Opsional" class="glass-input">
                            </div>
                        </div>
                        <div class="mt-[24px] flex items-center justify-end gap-[10px]">
                            <button type="button" id="unit-cancel" class="btn-glass">Batal</button>
                            <button type="submit" class="btn-grad">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @include('partials.front.scripts')
        @if(auth()->user()?->isAdmin())
            <script>
                (function () {
                    const modal = document.getElementById('unit-modal');
                    const backdrop = document.getElementById('unit-backdrop');
                    const closeBtn = document.getElementById('unit-close');
                    const cancelBtn = document.getElementById('unit-cancel');
                    const form = document.getElementById('unit-form');
                    const asset = document.getElementById('unit-asset');
                    const status = document.getElementById('unit-status');
                    const lokasi = document.getElementById('unit-lokasi');
                    const catatan = document.getElementById('unit-catatan');

                    function open() {
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                    }

                    function close() {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    }

                    document.querySelectorAll('.btn-edit').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            asset.textContent = btn.getAttribute('data-asset') + ' · ' + btn.getAttribute('data-nama');
                            status.value = btn.getAttribute('data-status');
                            lokasi.value = btn.getAttribute('data-lokasi') || '';
                            catatan.value = btn.getAttribute('data-catatan') || '';
                            form.action = btn.getAttribute('data-action');
                            open();
                        });
                    });

                    closeBtn.addEventListener('click', close);
                    cancelBtn.addEventListener('click', close);
                    backdrop.addEventListener('click', close);
                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape') {
                            close();
                        }
                    });
                })();
            </script>
        @endif
    </body>
</html>
