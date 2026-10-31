<!-- Header -->
<div class="header-area bg-white dark:bg-[#0c1427] py-[13px] px-[20px] md:px-[25px] fixed top-0 z-[6] rounded-b-md transition-all" id="header-area">
    <div class="flex flex-col gap-[10px] md:flex-row md:items-center md:justify-between md:gap-0">
        <div class="flex w-full min-w-0 items-center md:w-auto">
            <div class="relative shrink-0 leading-none top-px ltr:mr-[13px] ltr:md:mr-[18px] ltr:lg:mr-[23px] rtl:ml-[13px] rtl:md:ml-[18px] rtl:lg:ml-[23px]">
                <button type="button" class="hide-sidebar-toggle transition-all inline-block hover:text-primary-500" id="hide-sidebar-toggle">
                    <i class="material-symbols-outlined !text-[20px]">
                        menu
                    </i>
                </button>
            </div>
            <div class="relative min-w-0 flex-1 md:w-[260px] md:flex-none lg:w-[300px]">
                <form action="{{ route('search.index') }}" method="GET" autocomplete="off">
                    <input type="text" name="q" id="global-search-input" value="{{ request()->routeIs('search.index') ? request('q') : '' }}" placeholder="Cari menu, barang, unit, transaksi..." class="bg-gray-50 border border-gray-50 h-[44px] rounded-md w-full block text-black pt-[11px] pb-[12px] px-[13px] md:px-[16px] placeholder:text-gray-500 outline-0 dark:bg-[#15203c] dark:text-white dark:border-[#15203c] dark:placeholder:text-gray-400">
                    <button type="submit" class="absolute text-primary-500 mt-[2px] ltr:right-[13px] ltr:md:right-[15px] rtl:left-[13px] rtl:md:left-[15px] top-1/2 -translate-y-1/2">
                        <i class="material-symbols-outlined !text-[20px]">
                            search
                        </i>
                    </button>
                </form>
                <div class="global-search-results hidden bg-white dark:bg-[#0c1427] shadow-3xl dark:shadow-none absolute mt-[9px] w-[340px] md:w-[420px] max-h-[440px] overflow-y-auto z-[5] top-full ltr:left-0 rtl:right-0 rounded-md">
                    <div class="p-[10px]" id="global-search-list"></div>
                </div>
            </div>
        </div>
        <ul class="flex shrink-0 items-center justify-center md:justify-normal">
            <li class="relative mx-[8px] md:mx-[10px] lg:mx-[12px] ltr:first:ml-0 ltr:last:mr-0 rtl:first:mr-0 rtl:last:ml-0">
                <button type="button" class="light-dark-toggle leading-none inline-block transition-all relative top-[2px] text-[#fe7a36]" id="light-dark-toggle">
                    <i class="material-symbols-outlined !text-[20px] md:!text-[22px]">
                        light_mode
                    </i>
                </button>
            </li>
            <li class="relative mx-[8px] md:mx-[10px] lg:mx-[12px] ltr:first:ml-0 ltr:last:mr-0 rtl:first:mr-0 rtl:last:ml-0">
                <button type="button" class="leading-none inline-block transition-all relative top-[2px] hover:text-primary-500" id="fullscreenBtn">
                    <i class="material-symbols-outlined !text-[22px] md:!text-[24px]" id="fullscreenIcon">
                        fullscreen
                    </i>
                </button>
            </li>
            <li class="relative profile-menu mx-[8px] md:mx-[10px] lg:mx-[12px] ltr:first:ml-0 ltr:last:mr-0 rtl:first:mr-0 rtl:last:ml-0">
                <button type="button" class="flex items-center -mx-[5px] relative ltr:pr-[14px] rtl:pl-[14px] text-black dark:text-white" id="dropdownToggleBtn">
                    <img src="/assets/images/admin.png" class="w-[35px] h-[35px] md:w-[42px] md:h-[42px] rounded-full ltr:md:mr-[2px] ltr:lg:mr-[8px] rtl:md:ml-[2px] rtl:lg:ml-[8px] border-[2px] border-primary-200 inline-block" alt="admin-image">
                    <span class="block font-semibold text-[0] lg:text-base">
                        {{ auth()->user()?->name ?? 'Guest' }}
                    </span>
                    <i class="ri-arrow-down-s-line text-[15px] absolute ltr:-right-[3px] rtl:-left-[3px] top-1/2 -translate-y-1/2 mt-px"></i>
                </button>
                <div class="profile-menu-dropdown bg-white dark:bg-[#0c1427] transition-all shadow-3xl dark:shadow-none py-[22px] absolute mt-[13px] md:mt-[14px] w-[195px] z-[1] top-full ltr:right-0 rtl:left-0 rounded-md">
                    <div class="flex items-center border-b border-gray-100 dark:border-[#172036] pb-[12px] mx-[20px] mb-[10px]">
                        <img src="/assets/images/admin.png" class="rounded-full w-[31px] h-[31px] ltr:mr-[9px] rtl:ml-[9px] border-2 border-primary-200 inline-block" alt="admin-image">
                        <div>
                            <span class="block text-black dark:text-white font-medium">
                                {{ auth()->user()?->name ?? 'Guest' }}
                            </span>
                            <span class="block text-xs capitalize">
                                {{ auth()->user()?->role ?? '-' }}
                            </span>
                        </div>
                    </div>
                    <ul>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left relative py-[7px] ltr:pl-[50px] ltr:pr-[20px] rtl:pr-[50px] rtl:pl-[20px] text-black dark:text-white transition-all hover:text-primary-500">
                                    <i class="material-symbols-outlined top-1/2 -translate-y-1/2 !text-[22px] absolute ltr:left-[20px] rtl:right-[20px]">
                                        logout
                                    </i>
                                    Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
</div>
<!-- End Header -->

<script>
    (function () {
        const input = document.getElementById('global-search-input');
        const box = document.querySelector('.global-search-results');
        const list = document.getElementById('global-search-list');
        if (!input || !box || !list) return;

        let timer = null;
        let controller = null;

        function hide() {
            box.classList.add('hidden');
            list.innerHTML = '';
        }

        function escapeHtml(str) {
            return String(str).replace(/[&<>"']/g, function (s) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[s];
            });
        }

        function render(groups, query) {
            if (!groups || groups.length === 0) {
                list.innerHTML = '<div class="px-[14px] py-[16px] text-sm text-gray-400">Tidak ada hasil untuk "' + escapeHtml(query) + '".</div>';
                box.classList.remove('hidden');
                return;
            }

            let html = '';
            groups.forEach(function (group) {
                html += '<div class="mb-[4px]">';
                html += '<div class="flex items-center gap-[6px] px-[14px] pt-[10px] pb-[6px] text-[11px] font-semibold uppercase tracking-wide text-gray-400">';
                html += '<i class="material-symbols-outlined !text-[16px]">' + escapeHtml(group.icon || 'label') + '</i>' + escapeHtml(group.label) + '</div>';
                group.items.forEach(function (item) {
                    html += '<a href="' + escapeHtml(item.url) + '" class="flex flex-col rounded-md px-[14px] py-[8px] transition-all hover:bg-gray-50 dark:hover:bg-[#15203c]">';
                    html += '<span class="text-sm font-medium text-black dark:text-white">' + escapeHtml(item.title) + '</span>';
                    if (item.subtitle) {
                        html += '<span class="text-xs text-gray-400">' + escapeHtml(item.subtitle) + '</span>';
                    }
                    html += '</a>';
                });
                html += '</div>';
            });

            list.innerHTML = html;
            box.classList.remove('hidden');
        }

        input.addEventListener('input', function () {
            const q = input.value.trim();
            clearTimeout(timer);

            if (q.length < 2) {
                hide();
                return;
            }

            timer = setTimeout(function () {
                if (controller) controller.abort();
                controller = new AbortController();
                const url = '{{ route('search.suggest') }}?q=' + encodeURIComponent(q);

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: controller.signal })
                    .then(function (r) { return r.json(); })
                    .then(function (data) { render(data.groups, q); })
                    .catch(function () {});
            }, 250);
        });

        document.addEventListener('click', function (e) {
            if (!box.contains(e.target) && e.target !== input) {
                hide();
            }
        });

        input.addEventListener('focus', function () {
            if (input.value.trim().length >= 2 && list.innerHTML !== '') {
                box.classList.remove('hidden');
            }
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') hide();
        });
    })();
</script>

