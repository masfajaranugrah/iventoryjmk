<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @include('partials.front.styles')

        <title>Trezo - {{ $title }}</title>

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
                                    <i class="material-symbols-outlined !text-[30px]">summarize</i>
                                </div>
                                <div>
                                    <h5 class="!mb-[4px] !text-white lg:!text-[24px]">Laporan</h5>
                                    <p class="!mb-0 text-sm text-white/80">Laporan mutasi, stok, lokasi, hilang/rusak, dan selisih opname.</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-[10px]">
                                <a href="{{ route('laporan.export', request()->query()) }}" class="inline-flex items-center justify-center gap-[7px] rounded-xl border border-white/30 bg-white/15 px-[18px] py-[11px] text-sm font-semibold text-white backdrop-blur-md transition-all hover:bg-white/25">
                                    <i class="material-symbols-outlined !text-[20px]">grid_on</i>
                                    Excel
                                </a>
                                <a href="{{ route('laporan.print', request()->query()) }}" target="_blank" class="inline-flex items-center justify-center gap-[7px] rounded-xl bg-white px-[18px] py-[11px] text-sm font-semibold text-primary-600 shadow-lg shadow-black/10 transition-all hover:-translate-y-px hover:bg-white/90">
                                    <i class="material-symbols-outlined !text-[20px]">print</i>
                                    Cetak / PDF
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Jenis Tab -->
                    <div class="mb-[25px] flex flex-wrap gap-[10px]">
                        @php
                            $jenisList = [
                                'ringkasan' => ['Ringkasan Stok', 'table_chart'],
                                'mutasi' => ['Mutasi Stok', 'swap_horiz'],
                                'stok' => ['Stok Barang', 'inventory_2'],
                                'lokasi' => ['Stok per Lokasi', 'location_on'],
                                'hilang_rusak' => ['Hilang / Rusak', 'report'],
                                'selisih' => ['Selisih Opname', 'difference'],
                            ];
                        @endphp
                        @foreach($jenisList as $key => $meta)
                            <a href="{{ route('laporan.index', array_merge(request()->except('jenis'), ['jenis' => $key])) }}"
                               class="inline-flex items-center gap-[7px] rounded-xl px-[16px] py-[10px] text-sm font-semibold transition-all {{ $jenis === $key ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/30' : 'glass-card !rounded-xl text-gray-500 hover:text-primary-500' }}">
                                <i class="material-symbols-outlined !text-[18px]">{{ $meta[1] }}</i>
                                {{ $meta[0] }}
                            </a>
                        @endforeach
                    </div>

                    <!-- Filter + Tabel -->
                    <div class="glass-card mb-[25px]">
                        <div class="flex flex-col gap-[14px] border-b border-white/50 p-[20px] dark:border-white/5 xl:flex-row xl:items-center xl:justify-between">
                            <div>
                                <h5 class="!mb-[2px]">{{ $title }}</h5>
                                <p class="!mb-0 text-xs text-gray-400">{{ count($rows) }} baris &middot; {{ $dari->format('d M Y') }} – {{ $sampai->format('d M Y') }}</p>
                            </div>
                            <form method="GET" action="{{ route('laporan.index') }}" class="flex flex-wrap items-center gap-[10px]">
                                <input type="hidden" name="jenis" value="{{ $jenis }}">
                                @if(in_array($jenis, ['ringkasan', 'mutasi'], true))
                                    <select name="barang_id" class="glass-select !h-[46px] !py-0 sm:!w-[200px]">
                                        <option value="">Semua Barang</option>
                                        @foreach($barangs as $barang)
                                            <option value="{{ $barang->id }}" @selected(request('barang_id') == $barang->id)>{{ $barang->kode_barang }} - {{ $barang->nama_barang }}</option>
                                        @endforeach
                                    </select>
                                    <input type="date" name="dari" value="{{ $dari->format('Y-m-d') }}" class="glass-input !h-[46px] sm:!w-[160px]">
                                    <input type="date" name="sampai" value="{{ $sampai->format('Y-m-d') }}" class="glass-input !h-[46px] sm:!w-[160px]">
                                @endif
                                <button type="submit" class="btn-grad !py-[11px]">Terapkan</button>
                                @if(request()->hasAny(['barang_id', 'dari', 'sampai']))
                                    <a href="{{ route('laporan.index', ['jenis' => $jenis]) }}" class="btn-glass !py-[11px]">Reset</a>
                                @endif
                            </form>
                        </div>

                        <div class="overflow-x-auto p-[12px]">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        @foreach(array_keys($rows[0] ?? []) as $col)
                                            <th>{{ $col }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rows as $index => $row)
                                        <tr>
                                            <td class="text-gray-400">{{ $index + 1 }}</td>
                                            @foreach($row as $value)
                                                <td>{{ $value }}</td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="12" class="!text-center text-gray-400">Tidak ada data untuk laporan ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="grow"></div>
                    @include('partials.dashboard.footer')
                </div>
            </div>
        </div>

        @include('partials.front.scripts')
    </body>
</html>
