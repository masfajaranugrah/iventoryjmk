<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @include('partials.front.styles')

        <title>Trezo - Pencarian</title>

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
                    <div class="hero-panel mb-[25px]">
                        <div class="relative z-[1] flex items-center gap-[16px] p-[22px] md:p-[28px]">
                            <div class="flex h-[56px] w-[56px] shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-md">
                                <i class="material-symbols-outlined !text-[30px]">search</i>
                            </div>
                            <div>
                                <h5 class="!mb-[4px] !text-white lg:!text-[24px]">Hasil Pencarian</h5>
                                <p class="!mb-0 text-sm text-white/80">
                                    @if($q === '')
                                        Masukkan kata kunci pada kolom pencarian.
                                    @else
                                        Menampilkan hasil untuk "<strong>{{ $q }}</strong>"
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($q === '')
                        <div class="glass-card p-[40px] text-center">
                            <i class="material-symbols-outlined !text-[46px] text-gray-300">manage_search</i>
                            <p class="!mb-0 mt-[10px] text-gray-400">Cari menu, barang, unit/asset, transaksi, lokasi, opname, atau pengguna.</p>
                        </div>
                    @elseif(empty($groups))
                        <div class="glass-card p-[40px] text-center">
                            <i class="material-symbols-outlined !text-[46px] text-gray-300">search_off</i>
                            <p class="!mb-0 mt-[10px] text-gray-400">Tidak ada hasil untuk "{{ $q }}".</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-[18px] lg:grid-cols-2">
                            @foreach($groups as $group)
                                <div class="glass-card p-[20px]">
                                    <div class="mb-[14px] flex items-center gap-[8px]">
                                        <span class="grad-icon grad-primary !h-[36px] !w-[36px] !rounded-lg">
                                            <i class="material-symbols-outlined !text-[20px]">{{ $group['icon'] }}</i>
                                        </span>
                                        <h5 class="!mb-0">{{ $group['label'] }}</h5>
                                        <span class="pill pill-dark ltr:ml-auto rtl:mr-auto">{{ count($group['items']) }}</span>
                                    </div>
                                    <div class="flex flex-col gap-[6px]">
                                        @foreach($group['items'] as $item)
                                            <a href="{{ $item['url'] }}" class="flex flex-col rounded-xl border border-white/60 bg-white/40 px-[14px] py-[10px] transition-all hover:border-primary-300 hover:bg-white/70 dark:border-white/5 dark:bg-white/5">
                                                <span class="text-sm font-medium text-black dark:text-white">{{ $item['title'] }}</span>
                                                @if($item['subtitle'])
                                                    <span class="text-xs text-gray-400">{{ $item['subtitle'] }}</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="grow"></div>
                    @include('partials.dashboard.footer')
                </div>
            </div>
        </div>

        @include('partials.front.scripts')
    </body>
</html>
