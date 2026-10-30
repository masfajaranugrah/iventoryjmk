<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('partials.front.styles')

        <title>Trezo - Stok Barang</title>

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
                                    <i class="material-symbols-outlined !text-[30px]">inventory_2</i>
                                </div>
                                <div>
                                    <h5 class="!mb-[4px] !text-white lg:!text-[24px]">Stok Barang</h5>
                                    <p class="!mb-0 text-sm text-white/80">Kelola data, stok, dan QR code seluruh barang inventori.</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-[10px]">
                                @if(auth()->user()?->isAdmin())
                                    <button type="button" id="btn-add" class="inline-flex items-center justify-center gap-[7px] rounded-xl bg-white px-[18px] py-[11px] text-sm font-semibold text-primary-600 shadow-lg shadow-black/10 transition-all hover:-translate-y-px hover:bg-white/90">
                                        <i class="material-symbols-outlined !text-[20px]">add</i>
                                        Tambah Barang
                                    </button>
                                @endif
                                <a href="{{ route('barang-units.index') }}" class="inline-flex items-center justify-center gap-[7px] rounded-xl border border-white/30 bg-white/15 px-[18px] py-[11px] text-sm font-semibold text-white backdrop-blur-md transition-all hover:bg-white/25">
                                    <i class="material-symbols-outlined !text-[20px]">barcode</i>
                                    Unit & Label
                                </a>
                                <a href="/dashboard/scan-barcode" class="inline-flex items-center justify-center gap-[7px] rounded-xl border border-white/30 bg-white/15 px-[18px] py-[11px] text-sm font-semibold text-white backdrop-blur-md transition-all hover:bg-white/25">
                                    <i class="material-symbols-outlined !text-[20px]">qr_code_scanner</i>
                                    Scan Barcode
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Ringkasan -->
                    <div class="mb-[25px] grid grid-cols-1 gap-[18px] sm:grid-cols-2 xl:grid-cols-4">
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Jenis Barang</span>
                                    <h5 class="!mb-0">{{ $totalItems }}</h5>
                                </div>
                                <div class="grad-icon grad-primary"><i class="material-symbols-outlined !text-[26px]">category</i></div>
                            </div>
                        </div>
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Total Stok</span>
                                    <h5 class="!mb-0">{{ number_format($totalStok, 0, ',', '.') }}</h5>
                                </div>
                                <div class="grad-icon grad-success"><i class="material-symbols-outlined !text-[26px]">warehouse</i></div>
                            </div>
                        </div>
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Stok Menipis</span>
                                    <h5 class="!mb-0">{{ $lowStock }}</h5>
                                </div>
                                <div class="grad-icon grad-warning"><i class="material-symbols-outlined !text-[26px]">warning</i></div>
                            </div>
                        </div>
                        <div class="glass-card p-[20px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="mb-[6px] block text-xs font-medium uppercase tracking-wide text-gray-400">Stok Habis</span>
                                    <h5 class="!mb-0">{{ $outOfStock }}</h5>
                                </div>
                                <div class="grad-icon grad-danger"><i class="material-symbols-outlined !text-[26px]">production_quantity_limits</i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel -->
                    <div class="glass-card mb-[25px]">
                        <div class="flex flex-col gap-[14px] border-b border-white/50 p-[20px] dark:border-white/5 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h5 class="!mb-[2px]">Daftar Stok Barang</h5>
                                <p class="!mb-0 text-xs text-gray-400">{{ $barangs->total() }} barang terdaftar</p>
                            </div>
                            <form method="GET" action="{{ route('stok-barang.index') }}" class="flex flex-wrap items-center gap-[10px]">
                                <div class="relative w-full sm:w-[260px]">
                                    <i class="material-symbols-outlined pointer-events-none absolute left-[14px] top-1/2 -translate-y-1/2 !text-[20px] text-gray-400">search</i>
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari barang / kode..." class="glass-input !h-[46px] ltr:!pl-[44px]">
                                </div>
                                <select name="tipe" class="glass-select !h-[46px] !py-0 sm:!w-[170px]">
                                    <option value="">Semua Tipe</option>
                                    <option value="unit" @selected(request('tipe') === 'unit')>Per Unit</option>
                                    <option value="jumlah" @selected(request('tipe') === 'jumlah')>Per Jumlah</option>
                                </select>
                                <button type="submit" class="btn-grad !px-[16px] !py-[11px]">
                                    <i class="material-symbols-outlined !text-[20px]">search</i>
                                </button>
                                @if(request('search') || request('tipe'))
                                    <a href="{{ route('stok-barang.index') }}" class="btn-glass !py-[11px]">Reset</a>
                                @endif
                            </form>
                        </div>

                        <div class="overflow-x-auto p-[12px]">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th>Tipe</th>
                                        <th>Stok</th>
                                        <th>Satuan</th>
                                        <th>Label</th>
                                        <th>Status</th>
                                        @if(auth()->user()?->isAdmin())
                                            <th class="!text-right">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($barangs as $index => $barang)
                                    <tr>
                                        <td class="text-gray-400">{{ $barangs->firstItem() + $index }}</td>
                                        <td><span class="font-semibold">{{ $barang->kode_barang }}</span></td>
                                        <td>
                                            <div>{{ $barang->nama_barang }}</div>
                                            @if($barang->kategori || $barang->merek)
                                                <div class="text-xs text-gray-400">{{ trim(($barang->kategori ?? '') . ($barang->merek ? ' · ' . $barang->merek : ''), ' ·') }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($barang->isTrackedPerUnit())
                                                <span class="pill pill-primary"><i class="material-symbols-outlined !text-[14px]">barcode</i> Per Unit</span>
                                            @else
                                                <span class="pill pill-dark"><i class="material-symbols-outlined !text-[14px]">tag</i> Per Jumlah</span>
                                            @endif
                                        </td>
                                        <td><span class="font-semibold">{{ $barang->stok }}</span></td>
                                        <td class="text-gray-400">{{ $barang->satuan ?? '-' }}</td>
                                        <td>
                                            @if($barang->isTrackedPerUnit())
                                                <a href="{{ route('barang-units.labels', ['barang_id' => $barang->id]) }}" class="icon-btn icon-btn-primary" title="Cetak label unit">
                                                    <i class="material-symbols-outlined !text-[20px]">print</i>
                                                </a>
                                                <span class="ml-[6px] text-xs text-gray-400">{{ $barang->units_count }} unit</span>
                                            @else
                                                <div class="qr-code inline-block rounded-lg bg-white p-[5px] shadow-sm" data-code="{{ $barang->kode_barang }}"></div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($barang->stok > 10)
                                                <span class="pill pill-success"><span class="h-[6px] w-[6px] rounded-full bg-success-500"></span> Tersedia</span>
                                            @elseif($barang->stok > 0)
                                                <span class="pill pill-warning"><span class="h-[6px] w-[6px] rounded-full bg-warning-500"></span> Menipis</span>
                                            @else
                                                <span class="pill pill-danger"><span class="h-[6px] w-[6px] rounded-full bg-danger-500"></span> Habis</span>
                                            @endif
                                        </td>
                                        @if(auth()->user()?->isAdmin())
                                            <td>
                                                <div class="flex items-center justify-end gap-[8px]">
                                                    <button type="button" class="icon-btn icon-btn-primary btn-edit"
                                                        data-id="{{ $barang->id }}"
                                                        data-kode="{{ $barang->kode_barang }}"
                                                        data-nama="{{ $barang->nama_barang }}"
                                                        data-kategori="{{ $barang->kategori }}"
                                                        data-merek="{{ $barang->merek }}"
                                                        data-model="{{ $barang->model }}"
                                                        data-tipe="{{ $barang->tipe_pelacakan }}"
                                                        data-satuan="{{ $barang->satuan }}">
                                                        <i class="material-symbols-outlined !text-[20px]">edit</i>
                                                    </button>
                                                    <button type="button" class="icon-btn icon-btn-danger btn-delete"
                                                        data-id="{{ $barang->id }}"
                                                        data-nama="{{ $barang->nama_barang }}"
                                                        data-action="{{ route('stok-barang.destroy', $barang) }}">
                                                        <i class="material-symbols-outlined !text-[20px]">delete</i>
                                                    </button>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()?->isAdmin() ? 9 : 8 }}" class="!text-center text-gray-400">
                                            Belum ada data barang.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($barangs->hasPages())
                            <div class="flex flex-wrap items-center justify-between gap-[12px] border-t border-white/50 p-[20px] dark:border-white/5">
                                <p class="!mb-0 text-sm text-gray-400">
                                    Menampilkan {{ $barangs->firstItem() }}–{{ $barangs->lastItem() }} dari {{ $barangs->total() }}
                                </p>
                                <div class="flex items-center gap-[8px]">
                                    @if($barangs->onFirstPage())
                                        <span class="icon-btn cursor-not-allowed opacity-40"><i class="material-symbols-outlined !text-[20px]">chevron_left</i></span>
                                    @else
                                        <a href="{{ $barangs->previousPageUrl() }}" class="icon-btn hover:border-primary-500 hover:bg-primary-500 hover:text-white"><i class="material-symbols-outlined !text-[20px]">chevron_left</i></a>
                                    @endif
                                    <span class="pill pill-primary">{{ $barangs->currentPage() }} / {{ $barangs->lastPage() }}</span>
                                    @if($barangs->hasMorePages())
                                        <a href="{{ $barangs->nextPageUrl() }}" class="icon-btn hover:border-primary-500 hover:bg-primary-500 hover:text-white"><i class="material-symbols-outlined !text-[20px]">chevron_right</i></a>
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
            <!-- Modal Form Barang -->
            <div id="barang-modal" class="fixed inset-0 z-[9998] hidden items-center justify-center p-[20px]">
                <div id="barang-backdrop" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
                <div class="modern-modal max-w-[520px]">
                    <div class="mb-[22px] flex items-start justify-between gap-[12px]">
                        <div class="flex items-center gap-[12px]">
                            <div class="grad-icon grad-primary !h-[44px] !w-[44px] !rounded-xl"><i class="material-symbols-outlined !text-[22px]">edit_note</i></div>
                            <h5 class="!mb-0" id="barang-modal-title">Tambah Barang</h5>
                        </div>
                        <button type="button" id="barang-close" class="shrink-0 text-gray-400 transition-all hover:text-danger-500">
                            <i class="material-symbols-outlined !text-[22px]">close</i>
                        </button>
                    </div>
                    <form id="barang-form" method="POST" action="{{ route('stok-barang.store') }}">
                        @csrf
                        <input type="hidden" name="_method" id="barang-method" value="POST">
                        <div class="grid grid-cols-1 gap-[18px] sm:grid-cols-2">
                            <div>
                                <label class="mb-[10px] block font-medium text-black dark:text-white">Kode Barang</label>
                                <input type="text" name="kode_barang" id="field-kode" maxlength="100" placeholder="Kosongkan untuk otomatis" class="glass-input">
                            </div>
                            <div>
                                <label class="mb-[10px] block font-medium text-black dark:text-white">Satuan</label>
                                <input type="text" name="satuan" id="field-satuan" maxlength="50" placeholder="E.g. Unit" class="glass-input">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-[10px] block font-medium text-black dark:text-white">Nama Barang</label>
                                <input type="text" name="nama_barang" id="field-nama" required maxlength="150" placeholder="E.g. Modem Fiber GPON" class="glass-input">
                            </div>
                            <div>
                                <label class="mb-[10px] block font-medium text-black dark:text-white">Kategori</label>
                                <input type="text" name="kategori" id="field-kategori" maxlength="100" placeholder="E.g. Perangkat Jaringan" class="glass-input">
                            </div>
                            <div>
                                <label class="mb-[10px] block font-medium text-black dark:text-white">Merek</label>
                                <input type="text" name="merek" id="field-merek" maxlength="100" placeholder="E.g. Huawei" class="glass-input">
                            </div>
                            <div>
                                <label class="mb-[10px] block font-medium text-black dark:text-white">Model</label>
                                <input type="text" name="model" id="field-model" maxlength="100" placeholder="E.g. HG8145V5" class="glass-input">
                            </div>
                            <div>
                                <label class="mb-[10px] block font-medium text-black dark:text-white">Tipe Pelacakan</label>
                                <select name="tipe_pelacakan" id="field-tipe" class="glass-select">
                                    <option value="jumlah">Per Jumlah</option>
                                    <option value="unit">Per Unit (Asset ID)</option>
                                </select>
                            </div>
                        </div>
                        <div id="initial-fields" class="mt-[18px] grid grid-cols-1 gap-[18px] border-t border-white/50 pt-[18px] sm:grid-cols-2 dark:border-white/5">
                            <div>
                                <label class="mb-[10px] block font-medium text-black dark:text-white">Jumlah Awal</label>
                                <input type="number" name="jumlah_awal" id="field-jumlah" min="0" value="0" class="glass-input">
                            </div>
                            <div>
                                <label class="mb-[10px] block font-medium text-black dark:text-white">Lokasi</label>
                                <select name="lokasi_id" id="field-lokasi" class="glass-select">
                                    <option value="">- Tanpa lokasi -</option>
                                    @foreach($lokasis as $lokasi)
                                        <option value="{{ $lokasi->id }}">{{ $lokasi->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2" id="serial-wrap">
                                <label class="mb-[10px] block font-medium text-black dark:text-white">Serial Numbers <span class="text-xs text-gray-400">(opsional, satu per baris)</span></label>
                                <textarea name="serial_numbers" id="field-serials" rows="3" class="glass-input" placeholder="SN-123&#10;SN-124"></textarea>
                            </div>
                        </div>
                        @error('kode_barang')
                            <p class="mt-[10px] text-sm text-danger-500">{{ $message }}</p>
                        @enderror
                        <div class="mt-[24px] flex items-center justify-end gap-[10px]">
                            <button type="button" id="barang-cancel" class="btn-glass">Batal</button>
                            <button type="submit" class="btn-grad">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Hapus -->
            <div id="delete-modal" class="fixed inset-0 z-[9998] hidden items-center justify-center p-[20px]">
                <div id="delete-backdrop" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
                <div class="modern-modal max-w-[420px]">
                    <div class="flex flex-col items-center text-center">
                        <div class="grad-icon grad-danger mb-[16px]"><i class="material-symbols-outlined !text-[28px]">delete_forever</i></div>
                        <h5 class="!mb-[8px]">Hapus Barang?</h5>
                        <p class="mb-[24px] text-sm text-gray-500 dark:text-gray-400">
                            Barang <strong id="delete-name" class="text-black dark:text-white"></strong> akan dihapus permanen beserta riwayat stoknya.
                        </p>
                    </div>
                    <form id="delete-form" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <div class="flex items-center justify-center gap-[10px]">
                            <button type="button" id="delete-cancel" class="btn-glass">Batal</button>
                            <button type="submit" class="btn-grad-danger">Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @include('partials.front.scripts')
        <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
        <script>
            document.querySelectorAll('.qr-code').forEach(function (el) {
                new QRCode(el, {
                    text: el.getAttribute('data-code'),
                    width: 56,
                    height: 56,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M,
                });
            });

            @if(auth()->user()?->isAdmin())
                (function () {
                    const storeUrl = @json(route('stok-barang.store'));
                    const updateUrl = @json(route('stok-barang.update', ['barang' => '__ID__']));
                    const modal = document.getElementById('barang-modal');
                    const backdrop = document.getElementById('barang-backdrop');
                    const closeBtn = document.getElementById('barang-close');
                    const cancelBtn = document.getElementById('barang-cancel');
                    const form = document.getElementById('barang-form');
                    const method = document.getElementById('barang-method');
                    const title = document.getElementById('barang-modal-title');
                    const fieldKode = document.getElementById('field-kode');
                    const fieldNama = document.getElementById('field-nama');
                    const fieldKategori = document.getElementById('field-kategori');
                    const fieldMerek = document.getElementById('field-merek');
                    const fieldModel = document.getElementById('field-model');
                    const fieldTipe = document.getElementById('field-tipe');
                    const fieldSatuan = document.getElementById('field-satuan');
                    const initialFields = document.getElementById('initial-fields');
                    const serialWrap = document.getElementById('serial-wrap');

                    function toggleSerial() {
                        serialWrap.style.display = fieldTipe.value === 'unit' ? '' : 'none';
                    }
                    fieldTipe.addEventListener('change', toggleSerial);

                    const deleteModal = document.getElementById('delete-modal');
                    const deleteBackdrop = document.getElementById('delete-backdrop');
                    const deleteCancel = document.getElementById('delete-cancel');
                    const deleteForm = document.getElementById('delete-form');
                    const deleteName = document.getElementById('delete-name');

                    function openModal() {
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                    }

                    function closeModal() {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    }

                    function resetForm() {
                        form.reset();
                        form.action = storeUrl;
                        method.value = 'POST';
                        title.textContent = 'Tambah Barang';
                        initialFields.style.display = '';
                        fieldTipe.value = 'jumlah';
                        toggleSerial();
                    }

                    document.getElementById('btn-add').addEventListener('click', function () {
                        resetForm();
                        openModal();
                        setTimeout(function () { fieldKode.focus(); }, 50);
                    });

                    document.querySelectorAll('.btn-edit').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            resetForm();
                            title.textContent = 'Edit Barang';
                            initialFields.style.display = 'none';
                            fieldKode.value = btn.getAttribute('data-kode');
                            fieldNama.value = btn.getAttribute('data-nama');
                            fieldKategori.value = btn.getAttribute('data-kategori') || '';
                            fieldMerek.value = btn.getAttribute('data-merek') || '';
                            fieldModel.value = btn.getAttribute('data-model') || '';
                            fieldTipe.value = btn.getAttribute('data-tipe') || 'jumlah';
                            fieldSatuan.value = btn.getAttribute('data-satuan') || '';
                            method.value = 'PUT';
                            form.action = updateUrl.replace('__ID__', btn.getAttribute('data-id'));
                            openModal();
                            setTimeout(function () { fieldKode.focus(); }, 50);
                        });
                    });

                    function closeDelete() {
                        deleteModal.classList.add('hidden');
                        deleteModal.classList.remove('flex');
                    }

                    document.querySelectorAll('.btn-delete').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            deleteName.textContent = btn.getAttribute('data-nama');
                            deleteForm.action = btn.getAttribute('data-action');
                            deleteModal.classList.remove('hidden');
                            deleteModal.classList.add('flex');
                        });
                    });

                    closeBtn.addEventListener('click', closeModal);
                    cancelBtn.addEventListener('click', closeModal);
                    backdrop.addEventListener('click', closeModal);
                    deleteCancel.addEventListener('click', closeDelete);
                    deleteBackdrop.addEventListener('click', closeDelete);

                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape') {
                            closeModal();
                            closeDelete();
                        }
                    });
                })();
            @endif
        </script>
    </body>
</html>
