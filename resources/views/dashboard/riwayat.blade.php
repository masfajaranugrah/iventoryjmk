<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @include('partials.front.styles')

        <title>Trezo - Riwayat Transaksi</title>

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
                    <div class="hero-panel hero-panel-slate mb-[25px]">
                        <div class="relative z-[1] flex flex-col gap-[18px] p-[22px] md:p-[28px] lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-start gap-[16px]">
                                <div class="flex h-[56px] w-[56px] shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-md">
                                    <i class="material-symbols-outlined !text-[30px]">history</i>
                                </div>
                                <div>
                                    <h5 class="!mb-[4px] !text-white lg:!text-[24px]">Riwayat Transaksi</h5>
                                    <p class="!mb-0 text-sm text-white/80">Seluruh riwayat barang masuk, keluar, dan kembali dalam satu tempat.</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-[10px]">
                                <a href="{{ route('stok-masuk.index') }}" class="inline-flex items-center justify-center gap-[7px] rounded-xl border border-white/30 bg-white/15 px-[18px] py-[11px] text-sm font-semibold text-white backdrop-blur-md transition-all hover:bg-white/25">
                                    <i class="material-symbols-outlined !text-[20px]">move_to_inbox</i>
                                    Stok Masuk
                                </a>
                                <a href="{{ route('stok-keluar.index') }}" class="inline-flex items-center justify-center gap-[7px] rounded-xl border border-white/30 bg-white/15 px-[18px] py-[11px] text-sm font-semibold text-white backdrop-blur-md transition-all hover:bg-white/25">
                                    <i class="material-symbols-outlined !text-[20px]">outbox</i>
                                    Stok Keluar
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Ringkasan -->
                    <div class="mb-[25px] grid grid-cols-2 gap-[18px] xl:grid-cols-4">
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Total Transaksi</span>
                                    <h5 class="!mb-0">{{ number_format($summary['total'], 0, ',', '.') }}</h5>
                                </div>
                                <div class="grad-icon grad-primary"><i class="material-symbols-outlined !text-[26px]">receipt_long</i></div>
                            </div>
                        </div>
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Masuk</span>
                                    <h5 class="!mb-0">{{ number_format($summary['masuk'], 0, ',', '.') }}</h5>
                                </div>
                                <div class="grad-icon grad-success"><i class="material-symbols-outlined !text-[26px]">move_to_inbox</i></div>
                            </div>
                        </div>
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Keluar</span>
                                    <h5 class="!mb-0">{{ number_format($summary['keluar'], 0, ',', '.') }}</h5>
                                </div>
                                <div class="grad-icon grad-danger"><i class="material-symbols-outlined !text-[26px]">outbox</i></div>
                            </div>
                        </div>
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Kembali</span>
                                    <h5 class="!mb-0">{{ number_format($summary['kembali'], 0, ',', '.') }}</h5>
                                </div>
                                <div class="grad-icon grad-info"><i class="material-symbols-outlined !text-[26px]">assignment_return</i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel -->
                    <div class="glass-card mb-[25px]">
                        <div class="flex flex-col gap-[14px] border-b border-white/50 p-[20px] dark:border-white/5 xl:flex-row xl:items-center xl:justify-between">
                            <div>
                                <h5 class="!mb-[2px]">Riwayat Transaksi</h5>
                                <p class="!mb-0 text-xs text-gray-400">{{ $transaksis->total() }} transaksi</p>
                            </div>
                            <form method="GET" action="{{ route('riwayat.index') }}" class="flex flex-wrap items-center gap-[10px]">
                                <div class="relative w-full sm:w-[220px]">
                                    <i class="material-symbols-outlined pointer-events-none absolute left-[14px] top-1/2 -translate-y-1/2 !text-[20px] text-gray-400">search</i>
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari barang / pengambil / asset..." class="glass-input !h-[46px] ltr:!pl-[44px]">
                                </div>
                                <select name="tipe" class="glass-select !h-[46px] !py-0 sm:!w-[160px]">
                                    <option value="">Semua Tipe</option>
                                    @foreach($tipeLabels as $value => $label)
                                        <option value="{{ $value }}" @selected(request('tipe') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input type="date" name="date" value="{{ request('date') }}" class="glass-input !h-[46px] sm:!w-[170px]">
                                <button type="submit" class="btn-grad !py-[11px]">Filter</button>
                                @if(request()->hasAny(['search', 'tipe', 'date']))
                                    <a href="{{ route('riwayat.index') }}" class="btn-glass !py-[11px]">Reset</a>
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
                                        <th>Barang</th>
                                        <th>Asset ID</th>
                                        <th>Jumlah</th>
                                        <th>Pengambil / Pemasok</th>
                                        <th>Lokasi</th>
                                        <th>Petugas</th>
                                        <th>Catatan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transaksis as $index => $item)
                                    @php
                                        $tipePill = [
                                            'masuk' => 'pill-success',
                                            'keluar' => 'pill-danger',
                                            'kembali' => 'pill-info',
                                            'pindah' => 'pill-warning',
                                            'pemeriksaan' => 'pill-primary',
                                            'penyesuaian' => 'pill-dark',
                                            'pembatalan' => 'pill-warning',
                                        ][$item->tipe] ?? 'pill-dark';
                                    @endphp
                                    <tr class="{{ $item->isVoided() ? 'opacity-50' : '' }}">
                                        <td class="text-gray-400">{{ $transaksis->firstItem() + $index }}</td>
                                        <td>{{ $item->tanggal->format('d M Y H:i') }}</td>
                                        <td class="text-gray-400 {{ $item->isVoided() ? 'line-through' : '' }}">{{ $item->kode_transaksi }}</td>
                                        <td>
                                            <span class="pill {{ $tipePill }}">{{ $item->tipeLabel() }}</span>
                                            @if($item->isVoided())
                                                <span class="pill pill-danger mt-[4px]">Dibatalkan</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $item->barang->nama_barang ?? '-' }}</div>
                                            <div class="text-xs text-gray-400">{{ $item->barang->kode_barang ?? '' }}</div>
                                        </td>
                                        <td>
                                            @if($item->unit)
                                                <span class="pill pill-primary">{{ $item->unit->asset_id }}</span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td><span class="font-semibold">{{ $item->jumlah }}</span></td>
                                        <td class="text-gray-400">{{ $item->nama_pengambil ?: ($item->nama_pemasok ?: '-') }}</td>
                                        <td class="text-gray-400">{{ $item->lokasi->nama ?? '-' }}</td>
                                        <td class="text-gray-400">{{ $item->user ?: '-' }}</td>
                                        <td class="text-gray-400">{{ $item->catatan ?: '-' }}</td>
                                        <td>
                                            @if(auth()->user()?->role === 'admin' && ! $item->isVoided() && ! $item->isReversal())
                                                <button type="button" class="js-void-btn btn-glass !px-[12px] !py-[7px] !text-xs text-danger-500"
                                                    data-url="{{ route('riwayat.void', $item) }}"
                                                    data-code="{{ $item->kode_transaksi }}">
                                                    Batalkan
                                                </button>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="12" class="!text-center text-gray-400">Belum ada riwayat transaksi.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($transaksis->hasPages())
                            <div class="flex flex-wrap items-center justify-between gap-[12px] border-t border-white/50 p-[20px] dark:border-white/5">
                                <p class="!mb-0 text-sm text-gray-400">Menampilkan {{ $transaksis->firstItem() }}–{{ $transaksis->lastItem() }} dari {{ $transaksis->total() }}</p>
                                <div class="flex items-center gap-[8px]">
                                    @if($transaksis->onFirstPage())
                                        <span class="icon-btn cursor-not-allowed opacity-40"><i class="material-symbols-outlined !text-[20px]">chevron_left</i></span>
                                    @else
                                        <a href="{{ $transaksis->previousPageUrl() }}" class="icon-btn hover:border-primary-500 hover:bg-primary-500 hover:text-white"><i class="material-symbols-outlined !text-[20px]">chevron_left</i></a>
                                    @endif
                                    <span class="pill pill-primary">{{ $transaksis->currentPage() }} / {{ $transaksis->lastPage() }}</span>
                                    @if($transaksis->hasMorePages())
                                        <a href="{{ $transaksis->nextPageUrl() }}" class="icon-btn hover:border-primary-500 hover:bg-primary-500 hover:text-white"><i class="material-symbols-outlined !text-[20px]">chevron_right</i></a>
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

        <div id="void-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/50 p-[16px]">
            <div class="glass-card w-full max-w-[440px] p-[24px]">
                <h5 class="!mb-[4px]">Batalkan Transaksi</h5>
                <p class="!mb-[18px] text-sm text-gray-400">Transaksi <strong id="void-code">-</strong> akan dibatalkan dengan transaksi pembatalan. Riwayat tetap tersimpan.</p>
                <form method="POST" id="void-form">
                    @csrf
                    <label class="mb-[10px] block font-medium text-black dark:text-white">Alasan pembatalan</label>
                    <textarea name="reason" rows="3" required maxlength="255" class="glass-input" placeholder="Contoh: salah input jumlah"></textarea>
                    <div class="mt-[20px] flex justify-end gap-[10px]">
                        <button type="button" id="void-cancel" class="btn-glass">Kembali</button>
                        <button type="submit" class="btn-grad-danger">Batalkan Transaksi</button>
                    </div>
                </form>
            </div>
        </div>

        @include('partials.front.scripts')

        <script>
            (function () {
                const modal = document.getElementById('void-modal');
                const form = document.getElementById('void-form');
                const code = document.getElementById('void-code');
                const cancel = document.getElementById('void-cancel');

                function openModal(button) {
                    form.action = button.dataset.url;
                    code.textContent = button.dataset.code;
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }

                function closeModal() {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    form.reset();
                }

                document.querySelectorAll('.js-void-btn').forEach(function (button) {
                    button.addEventListener('click', function () {
                        openModal(button);
                    });
                });

                cancel.addEventListener('click', closeModal);
                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        closeModal();
                    }
                });
            })();
        </script>
    </body>
</html>
