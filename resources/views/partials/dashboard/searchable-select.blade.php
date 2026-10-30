<style>
    .ss-wrap { position: relative; }
    .ss-trigger { display: flex; align-items: center; justify-content: space-between; gap: 10px; text-align: left; }
    .ss-trigger .ss-label { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ss-trigger.ss-placeholder .ss-label { color: #9ca3af; }
    .ss-trigger .ss-arrow { transition: transform .2s ease; }
    .ss-wrap.ss-open .ss-trigger .ss-arrow { transform: rotate(180deg); }

    .ss-panel {
        position: absolute;
        z-index: 50;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        display: none;
        border-radius: 14px;
        border: 1px solid rgba(255, 255, 255, .7);
        background: rgba(255, 255, 255, .92);
        backdrop-filter: blur(16px);
        box-shadow: 0 24px 50px -20px rgba(31, 38, 135, .45);
        padding: 8px;
    }

    .ss-wrap.ss-open .ss-panel { display: block; }

    [class=dark] .ss-panel {
        border-color: rgba(255, 255, 255, .1);
        background: rgba(12, 20, 39, .96);
    }

    .ss-search { position: relative; margin-bottom: 6px; }

    .ss-search input {
        width: 100%;
        height: 42px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, .7);
        background: rgba(255, 255, 255, .7);
        padding: 0 12px 0 38px;
        outline: 0;
        font-size: 13px;
        color: #3a4252;
    }

    .ss-search input:focus { border-color: #757dff; }

    [class=dark] .ss-search input {
        border-color: rgba(255, 255, 255, .1);
        background: rgba(255, 255, 255, .05);
        color: #fff;
    }

    .ss-search i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 20px;
        color: #9ca3af;
        pointer-events: none;
    }

    .ss-list { list-style: none; margin: 0; padding: 0; max-height: 260px; overflow-y: auto; }
    .ss-list::-webkit-scrollbar { width: 6px; }
    .ss-list::-webkit-scrollbar-thumb { background: rgba(96, 93, 255, .35); border-radius: 999px; }

    .ss-option {
        padding: 10px 12px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 13px;
        color: #3a4252;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    [class=dark] .ss-option { color: #e5e7eb; }
    .ss-option:hover, .ss-option.ss-active { background: rgba(96, 93, 255, .1); }

    .ss-option.ss-selected {
        background: linear-gradient(135deg, rgba(96, 93, 255, .18), rgba(173, 99, 246, .18));
        font-weight: 600;
    }

    .ss-option .ss-stock { font-size: 11px; color: #9ca3af; white-space: nowrap; }
    .ss-empty { padding: 16px; text-align: center; font-size: 13px; color: #9ca3af; }
</style>

<script>
    (function () {
        function enhance(select) {
            if (select.dataset.enhanced === '1') {
                return;
            }
            select.dataset.enhanced = '1';

            const placeholder = select.dataset.placeholder || 'Pilih';
            const nativeOptions = Array.from(select.options).map(function (opt) {
                return {
                    value: opt.value,
                    text: opt.textContent.trim(),
                    disabled: opt.disabled,
                };
            }).filter(function (opt) {
                return opt.value !== '';
            });

            const wrap = document.createElement('div');
            wrap.className = 'ss-wrap';
            select.parentNode.insertBefore(wrap, select);
            wrap.appendChild(select);
            select.classList.add('hidden');

            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'glass-select ss-trigger';
            trigger.innerHTML = '<span class="ss-label"></span><i class="material-symbols-outlined ss-arrow !text-[22px]">expand_more</i>';

            const panel = document.createElement('div');
            panel.className = 'ss-panel';
            panel.innerHTML =
                '<div class="ss-search">' +
                    '<i class="material-symbols-outlined">search</i>' +
                    '<input type="text" placeholder="Cari barang / kode..." autocomplete="off">' +
                '</div>' +
                '<ul class="ss-list"></ul>';

            wrap.appendChild(trigger);
            wrap.appendChild(panel);

            const label = trigger.querySelector('.ss-label');
            const searchInput = panel.querySelector('.ss-search input');
            const list = panel.querySelector('.ss-list');

            let activeIndex = -1;
            let visibleOptions = [];

            function updateLabel() {
                const selected = select.value;
                const match = nativeOptions.find(function (opt) { return opt.value === selected; });

                if (match) {
                    label.textContent = match.text;
                    trigger.classList.remove('ss-placeholder');
                } else {
                    label.textContent = placeholder;
                    trigger.classList.add('ss-placeholder');
                }
            }

            function renderOptions(filter) {
                const term = (filter || '').toLowerCase();
                list.innerHTML = '';
                visibleOptions = [];

                nativeOptions.forEach(function (opt) {
                    if (term && opt.text.toLowerCase().indexOf(term) === -1) {
                        return;
                    }

                    const li = document.createElement('li');
                    li.className = 'ss-option';
                    li.dataset.value = opt.value;

                    if (opt.value === select.value) {
                        li.classList.add('ss-selected');
                    }

                    const stockMatch = opt.text.match(/\(stok:\s*([^)]+)\)/i);
                    const mainText = stockMatch ? opt.text.replace(/\(stok:[^)]+\)/i, '').trim() : opt.text;

                    li.innerHTML =
                        '<span class="ss-label-text">' + mainText + '</span>' +
                        (stockMatch ? '<span class="ss-stock">stok ' + stockMatch[1].trim() + '</span>' : '');

                    li.addEventListener('click', function () {
                        choose(opt.value);
                    });

                    list.appendChild(li);
                    visibleOptions.push(li);
                });

                if (visibleOptions.length === 0) {
                    const empty = document.createElement('li');
                    empty.className = 'ss-empty';
                    empty.textContent = 'Barang tidak ditemukan.';
                    list.appendChild(empty);
                }

                activeIndex = -1;
            }

            function choose(value) {
                select.value = value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                updateLabel();
                close();
            }

            function open() {
                wrap.classList.add('ss-open');
                renderOptions('');
                searchInput.value = '';
                setTimeout(function () { searchInput.focus(); }, 30);
            }

            function close() {
                wrap.classList.remove('ss-open');
                searchInput.value = '';
                activeIndex = -1;
            }

            function setActive(index) {
                const options = visibleOptions;
                if (options.length === 0) {
                    return;
                }
                if (index < 0) {
                    index = options.length - 1;
                }
                if (index >= options.length) {
                    index = 0;
                }

                options.forEach(function (li) { li.classList.remove('ss-active'); });
                activeIndex = index;
                options[activeIndex].classList.add('ss-active');
                options[activeIndex].scrollIntoView({ block: 'nearest' });
            }

            trigger.addEventListener('click', function () {
                wrap.classList.contains('ss-open') ? close() : open();
            });

            searchInput.addEventListener('input', function () {
                renderOptions(searchInput.value);
            });

            searchInput.addEventListener('keydown', function (event) {
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    setActive(activeIndex + 1);
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    setActive(activeIndex - 1);
                } else if (event.key === 'Enter') {
                    event.preventDefault();
                    if (activeIndex >= 0 && visibleOptions[activeIndex]) {
                        choose(visibleOptions[activeIndex].dataset.value);
                    }
                } else if (event.key === 'Escape') {
                    close();
                    trigger.focus();
                }
            });

            document.addEventListener('click', function (event) {
                if (!wrap.contains(event.target)) {
                    close();
                }
            });

            select.addEventListener('change', updateLabel);

            updateLabel();
        }

        document.querySelectorAll('select[data-searchable]').forEach(enhance);
    })();
</script>
