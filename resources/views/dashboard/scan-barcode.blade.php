<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('partials.front.styles')

        <title>Trezo - Scan Barcode</title>

        @vite('resources/css/app.css')

        <style>
            #reader video {
                width: 100% !important;
                height: 100% !important;
                object-fit: cover;
                border-radius: 16px;
            }

            #reader img[alt="Info icon"] {
                display: none !important;
            }

            .scan-frame::before,
            .scan-frame::after,
            .scan-frame span::before,
            .scan-frame span::after {
                content: '';
                position: absolute;
                width: 30px;
                height: 30px;
                border: 3px solid #ffffff;
                filter: drop-shadow(0 0 6px rgba(96, 93, 255, .9));
            }

            .scan-frame::before { top: 0; left: 0; border-right: 0; border-bottom: 0; border-top-left-radius: 14px; }
            .scan-frame::after { top: 0; right: 0; border-left: 0; border-bottom: 0; border-top-right-radius: 14px; }
            .scan-frame span::before { bottom: 0; left: 0; border-right: 0; border-top: 0; border-bottom-left-radius: 14px; }
            .scan-frame span::after { bottom: 0; right: 0; border-left: 0; border-top: 0; border-bottom-right-radius: 14px; }

            .scan-laser {
                position: absolute;
                left: 10%;
                right: 10%;
                height: 2px;
                background: linear-gradient(90deg, transparent, #9caaff, #605dff, #9caaff, transparent);
                box-shadow: 0 0 14px rgba(96, 93, 255, 1);
                animation: scanMove 2.2s ease-in-out infinite;
            }

            @keyframes scanMove {
                0%, 100% { top: 10%; opacity: .3; }
                50% { top: 88%; opacity: 1; }
            }

            @keyframes pulseDot {
                0%, 100% { opacity: 1; transform: scale(1); }
                50% { opacity: .35; transform: scale(.7); }
            }

            .status-dot-live { animation: pulseDot 1.4s ease-in-out infinite; }

            .history-scroll { max-height: 340px; overflow-y: auto; }
            .history-scroll::-webkit-scrollbar { width: 6px; }
            .history-scroll::-webkit-scrollbar-thumb { background: rgba(96, 93, 255, .35); border-radius: 999px; }
        </style>
    </head>
    <body>
        @include('partials.dashboard.sidebar')
        @include('partials.dashboard.header')

        <div id="toast" class="hidden fixed top-[20px] ltr:right-[20px] rtl:left-[20px] z-[9999] max-w-[360px] px-[16px] py-[12px] rounded-md shadow-lg text-sm font-medium"></div>

        <div class="main-content transition-all flex flex-col overflow-hidden min-h-screen" id="main-content">
            <div class="inv-scene">
                @include('partials.dashboard.inv-glow')

                <div class="relative z-10">
                    <!-- Hero -->
                    <div class="hero-panel hero-panel-slate mb-[25px]">
                        <div class="relative z-[1] flex flex-col gap-[18px] p-[22px] md:p-[28px] lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-start gap-[16px]">
                                <div class="flex h-[56px] w-[56px] shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-md">
                                    <i class="material-symbols-outlined !text-[30px]">qr_code_scanner</i>
                                </div>
                                <div>
                                    <h5 class="!mb-[4px] !text-white lg:!text-[24px]">Scan Barcode</h5>
                                    <p class="!mb-0 text-sm text-white/80">Kamera aktif otomatis. Arahkan ke Barcode / QR Code <strong>Asset ID</strong> unit.</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-[10px]">
                                <span id="camera-status" class="pill bg-white/15 text-white backdrop-blur-md">
                                    <span id="camera-dot" class="h-[7px] w-[7px] rounded-full bg-gray-300"></span>
                                    <span id="camera-status-text">Menyiapkan</span>
                                </span>
                                <a href="{{ route('stok-barang.index') }}" class="inline-flex items-center justify-center gap-[7px] rounded-xl border border-white/30 bg-white/15 px-[18px] py-[11px] text-sm font-semibold text-white backdrop-blur-md transition-all hover:bg-white/25">
                                    <i class="material-symbols-outlined !text-[20px]">inventory_2</i>
                                    Stok Barang
                                </a>
                            </div>
                        </div>
                    </div>

                    <div id="secure-context-alert" class="glass-card mb-[25px] hidden border-danger-200/70 p-[16px] text-sm text-danger-500 md:p-[20px]">
                        <p class="mb-[8px] font-semibold">Kamera tidak bisa diakses dari alamat ini.</p>
                        <p class="mb-[8px]">
                            Browser hanya mengizinkan kamera pada koneksi aman. Akses lewat
                            <strong>https://{{ request()->getHost() }}:8443</strong> (instal sertifikat CA di bawah),
                            atau lewat <strong>http://localhost</strong> / <strong>http://127.0.0.1</strong> pada komputer ini.
                        </p>
                        <a href="https://{{ request()->getHost() }}:8443/mkcert-rootCA.crt" class="inline-block font-medium text-primary-500 underline">
                            Unduh sertifikat CA (mkcert-rootCA.crt)
                        </a>
                    </div>

                    <div class="mb-[25px] grid grid-cols-1 gap-[25px] xl:grid-cols-3">
                        <!-- Camera -->
                        <div class="xl:col-span-2">
                            <div class="glass-card h-full p-[20px] md:p-[25px]">
                                <div class="mb-[18px] flex items-center gap-[12px]">
                                    <div class="grad-icon grad-primary !h-[42px] !w-[42px] !rounded-xl"><i class="material-symbols-outlined !text-[22px]">videocam</i></div>
                                    <div>
                                        <h5 class="!mb-0">Kamera Scanner</h5>
                                        <p class="!mb-0 text-xs text-gray-400">Setiap scan mencatat stok keluar setelah nama pengambil diisi.</p>
                                    </div>
                                </div>

                                <div id="camera-wrap" class="relative h-[300px] w-full overflow-hidden rounded-2xl bg-[#0b1020] sm:h-[380px] lg:h-[460px]">
                                    <div id="reader" class="h-full w-full"></div>

                                    <div id="camera-placeholder" class="absolute inset-0 flex flex-col items-center justify-center px-[20px] text-center transition-opacity">
                                        <div class="mb-[16px] flex h-[64px] w-[64px] items-center justify-center rounded-full bg-white/10 text-white">
                                            <i class="material-symbols-outlined !text-[32px]">videocam</i>
                                        </div>
                                        <p class="!mb-[4px] font-medium text-white">Mengaktifkan kamera...</p>
                                        <p class="!mb-0 max-w-[320px] text-sm text-white/60">Izinkan akses kamera pada browser untuk mulai memindai.</p>
                                    </div>

                                    <div id="scan-overlay" class="scan-frame pointer-events-none absolute inset-[12%] hidden transition-opacity">
                                        <span></span>
                                        <div class="scan-laser"></div>
                                    </div>
                                </div>

                                <div class="mt-[18px] flex flex-col items-stretch justify-between gap-[12px] sm:flex-row sm:items-center">
                                    <p class="!mb-0 text-sm text-gray-400">Sistem berbunyi sebagai tanda scan berhasil / gagal.</p>
                                    <div class="flex shrink-0 items-center gap-[10px]">
                                        <button type="button" id="btn-start" class="btn-grad">
                                            <i class="material-symbols-outlined !text-[20px]">play_arrow</i>
                                            Nyalakan
                                        </button>
                                        <button type="button" id="btn-stop" class="btn-grad-danger hidden">
                                            <i class="material-symbols-outlined !text-[20px]">stop</i>
                                            Matikan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Side panels -->
                        <div class="flex flex-col gap-[25px] xl:col-span-1">
                            <div class="glass-card p-[20px] md:p-[25px]">
                                <div class="mb-[18px] flex items-center justify-between">
                                    <h5 class="!mb-0">Hasil Scan Terakhir</h5>
                                    <div class="flex h-[34px] w-[34px] items-center justify-center rounded-lg bg-primary-50 text-primary-500 dark:bg-primary-500/10">
                                        <i class="material-symbols-outlined !text-[20px]">history</i>
                                    </div>
                                </div>
                                <div id="last-scan">
                                    <div class="flex flex-col items-center justify-center py-[22px] text-center">
                                        <div class="mb-[12px] flex h-[56px] w-[56px] items-center justify-center rounded-full bg-gray-50 text-gray-400 dark:bg-white/[0.05]">
                                            <i class="material-symbols-outlined !text-[26px]">scan</i>
                                        </div>
                                        <p class="mb-0 text-sm text-gray-400">Belum ada barang yang di-scan.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="glass-card p-[20px] md:p-[25px]">
                                <div class="mb-[18px] flex items-center justify-between">
                                    <h5 class="!mb-0">Riwayat Scan</h5>
                                    <span class="pill pill-primary">Terbaru</span>
                                </div>
                                <div class="history-scroll">
                                    <ul class="mb-0" id="scan-history">
                                        @forelse($recent as $item)
                                            <li class="mb-[12px] flex items-start justify-between gap-[10px] border-b border-white/50 pb-[12px] last:mb-0 last:border-0 last:pb-0 dark:border-white/5">
                                                <div class="min-w-0">
                                                    <span class="block truncate font-medium text-black dark:text-white">{{ $item->barang->nama_barang ?? '-' }}</span>
                                                    <span class="block text-xs text-gray-400">{{ $item->unit->asset_id ?? $item->kode_transaksi }} &middot; {{ $item->tanggal->format('d M Y H:i') }}</span>
                                                    @if($item->nama_pengambil)
                                                        <span class="block text-xs text-gray-400">Pengambil: {{ $item->nama_pengambil }}</span>
                                                    @endif
                                                </div>
                                                @if($item->tipe === \App\Models\Transaksi::TIPE_KEMBALI)
                                                    <span class="pill pill-success shrink-0">Kembali</span>
                                                @else
                                                    <span class="pill pill-danger shrink-0">Keluar</span>
                                                @endif
                                            </li>
                                        @empty
                                            <li class="text-sm text-gray-400">Belum ada riwayat scan.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grow"></div>
                    @include('partials.dashboard.footer')
                </div>
            </div>
        </div>

        <!-- Picker Modal -->
        <div id="picker-modal" class="fixed inset-0 z-[9998] hidden items-center justify-center p-[20px]">
            <div id="picker-backdrop" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="modern-modal max-w-[440px]">
                <div class="mb-[6px] flex items-start justify-between gap-[12px]">
                    <div class="flex items-center gap-[12px]">
                        <div class="grad-icon grad-primary !h-[44px] !w-[44px] !rounded-xl"><i class="material-symbols-outlined !text-[22px]">qr_code_2</i></div>
                        <h5 class="!mb-0" id="picker-title">Transaksi Unit</h5>
                    </div>
                    <button type="button" id="picker-close" class="shrink-0 text-gray-400 transition-all hover:text-danger-500">
                        <i class="material-symbols-outlined !text-[22px]">close</i>
                    </button>
                </div>
                <p class="mb-[16px] text-sm text-gray-500 dark:text-gray-400">
                    Unit: <strong id="picker-item" class="text-black dark:text-white"></strong>
                </p>
                <form id="picker-form">
                    <div class="mb-[16px] grid grid-cols-2 gap-[10px]">
                        <label class="cursor-pointer">
                            <input type="radio" name="picker-mode" value="keluar" class="peer sr-only" checked>
                            <span class="flex items-center justify-center gap-[6px] rounded-xl border border-white/60 px-[12px] py-[10px] text-sm font-medium text-gray-500 transition-all peer-checked:border-danger-400 peer-checked:bg-danger-50 peer-checked:text-danger-600 dark:border-white/10 dark:peer-checked:bg-danger-500/10">
                                <i class="material-symbols-outlined !text-[18px]">outbox</i> Keluar
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="picker-mode" value="kembali" class="peer sr-only">
                            <span class="flex items-center justify-center gap-[6px] rounded-xl border border-white/60 px-[12px] py-[10px] text-sm font-medium text-gray-500 transition-all peer-checked:border-success-400 peer-checked:bg-success-50 peer-checked:text-success-600 dark:border-white/10 dark:peer-checked:bg-success-500/10">
                                <i class="material-symbols-outlined !text-[18px]">move_to_inbox</i> Kembali
                            </span>
                        </label>
                    </div>
                    <label class="mb-[10px] block font-medium text-black dark:text-white">Nama Pengambil <span class="text-xs text-gray-400">(opsional saat kembali)</span></label>
                    <input type="text" id="picker-name" maxlength="100" autocomplete="off" placeholder="Masukkan nama pengambil" class="glass-input mb-[16px]">
                    <div class="mb-[16px]">
                        <label class="mb-[10px] block font-medium text-black dark:text-white">Lokasi</label>
                        <select id="picker-lokasi" class="glass-select">
                            <option value="">- Tanpa lokasi -</option>
                            @foreach(\App\Models\Lokasi::orderBy('nama')->get(['id', 'nama']) as $lokasi)
                                <option value="{{ $lokasi->id }}">{{ $lokasi->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center justify-end gap-[10px]">
                        <button type="button" id="picker-cancel" class="btn-glass">Batal</button>
                        <button type="submit" id="picker-save" class="btn-grad">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        @include('partials.front.scripts')
        <script src="/assets/js/vendor/html5-qrcode.min.js"></script>
        <script>
            (function () {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const reader = document.getElementById('reader');
                const btnStart = document.getElementById('btn-start');
                const btnStop = document.getElementById('btn-stop');
                const cameraStatus = document.getElementById('camera-status');
                const cameraDot = document.getElementById('camera-dot');
                const cameraStatusText = document.getElementById('camera-status-text');
                const cameraPlaceholder = document.getElementById('camera-placeholder');
                const scanOverlay = document.getElementById('scan-overlay');
                const lastScan = document.getElementById('last-scan');
                const scanHistory = document.getElementById('scan-history');
                const secureContextAlert = document.getElementById('secure-context-alert');
                const toast = document.getElementById('toast');
                const pickerModal = document.getElementById('picker-modal');
                const pickerBackdrop = document.getElementById('picker-backdrop');
                const pickerForm = document.getElementById('picker-form');
                const pickerName = document.getElementById('picker-name');
                const pickerItem = document.getElementById('picker-item');
                const pickerTitle = document.getElementById('picker-title');
                const pickerLokasi = document.getElementById('picker-lokasi');
                const pickerCancel = document.getElementById('picker-cancel');
                const pickerClose = document.getElementById('picker-close');
                const pickerSave = document.getElementById('picker-save');

                const isSecureEnough = window.isSecureContext && !!navigator.mediaDevices?.getUserMedia;

                let html5QrCode = null;
                let pendingScan = null;
                let isModalOpen = false;
                let isProcessing = false;
                let isSaving = false;
                let isStarting = false;
                let lastCode = null;
                let lastCodeTime = 0;
                let toastTimer = null;

                function showToast(message, type) {
                    const colors = type === 'error'
                        ? 'bg-danger-500 text-white'
                        : type === 'success'
                            ? 'bg-success-500 text-white'
                            : 'bg-primary-500 text-white';

                    toast.textContent = message;
                    toast.className = 'fixed top-[20px] ltr:right-[20px] rtl:left-[20px] z-[9999] max-w-[360px] px-[16px] py-[12px] rounded-md shadow-lg text-sm font-medium ' + colors;
                    toast.classList.remove('hidden');

                    clearTimeout(toastTimer);
                    toastTimer = setTimeout(function () {
                        toast.classList.add('hidden');
                    }, 4500);
                }

                function setStatus(text, type) {
                    cameraStatusText.textContent = text;

                    const badge = {
                        active: 'bg-success-500/15 text-success-600 dark:text-success-400',
                        error: 'bg-danger-500/15 text-danger-500',
                        idle: 'bg-white/15 text-white',
                        loading: 'bg-white/15 text-white',
                    }[type] || 'bg-white/15 text-white';

                    const dot = {
                        active: 'bg-success-500 status-dot-live',
                        error: 'bg-danger-500',
                        idle: 'bg-gray-300',
                        loading: 'bg-primary-300 status-dot-live',
                    }[type] || 'bg-gray-300';

                    cameraStatus.className = 'pill backdrop-blur-md ' + badge;
                    cameraDot.className = 'h-[7px] w-[7px] rounded-full ' + dot;
                }

                function showPlaceholder(show, title, subtitle, icon) {
                    if (!cameraPlaceholder) {
                        return;
                    }

                    if (show) {
                        cameraPlaceholder.classList.remove('hidden', 'opacity-0');
                        if (title) {
                            cameraPlaceholder.querySelector('p').textContent = title;
                        }
                        if (subtitle) {
                            cameraPlaceholder.querySelectorAll('p')[1].textContent = subtitle;
                        }
                        if (icon) {
                            cameraPlaceholder.querySelector('.material-symbols-outlined').textContent = icon;
                        }
                    } else {
                        cameraPlaceholder.classList.add('opacity-0');
                        setTimeout(function () {
                            cameraPlaceholder.classList.add('hidden');
                        }, 250);
                    }
                }

                function renderLastScan(data, isError) {
                    if (isError) {
                        lastScan.innerHTML =
                            '<div class="rounded-xl bg-danger-50 p-[14px] text-sm text-danger-500 dark:bg-danger-500/10">' +
                                '<div class="flex items-start gap-[8px]">' +
                                    '<i class="material-symbols-outlined !text-[20px] leading-none">error</i>' +
                                    '<span>' + data + '</span>' +
                                '</div>' +
                            '</div>';
                        return;
                    }

                    const isReturn = data.mode === 'kembali';
                    lastScan.innerHTML =
                        '<div class="mb-[14px] rounded-xl ' + (isReturn ? 'bg-success-50 dark:bg-success-500/10' : 'bg-primary-50 dark:bg-primary-500/10') + ' p-[14px]">' +
                            '<span class="block font-semibold text-black dark:text-white">' + data.nama_barang + '</span>' +
                            '<span class="block text-xs text-gray-500 dark:text-gray-400 mt-[4px]">Asset ID: ' + data.asset_id + '</span>' +
                        '</div>' +
                        '<div class="flex items-center justify-between text-sm">' +
                            '<span class="text-gray-400">' + (isReturn ? 'Kembali' : 'Keluar') + '</span>' +
                            '<span class="font-semibold ' + (isReturn ? 'text-success-600' : 'text-danger-500') + '">' + (isReturn ? '+' : '-') + '1 unit</span>' +
                        '</div>' +
                        '<div class="mt-[8px] flex items-center justify-between text-sm">' +
                            '<span class="text-gray-400">Status</span>' +
                            '<span class="font-medium text-black dark:text-white">' + data.status_label + '</span>' +
                        '</div>' +
                        '<div class="mt-[8px] flex items-center justify-between text-sm">' +
                            '<span class="text-gray-400">Pengambil</span>' +
                            '<span class="font-medium text-black dark:text-white">' + (data.nama_pengambil || '-') + '</span>' +
                        '</div>' +
                        '<div class="mt-[8px] flex items-center justify-between text-sm">' +
                            '<span class="text-gray-400">Lokasi</span>' +
                            '<span class="font-medium text-black dark:text-white">' + (data.lokasi || '-') + '</span>' +
                        '</div>' +
                        '<div class="mt-[8px] flex items-center justify-between text-sm">' +
                            '<span class="text-gray-400">Stok tersedia</span>' +
                            '<span class="font-medium text-black dark:text-white">' + data.stok_barang + '</span>' +
                        '</div>' +
                        '<div class="mt-[8px] flex items-center justify-between text-sm">' +
                            '<span class="text-gray-400">Waktu</span>' +
                            '<span class="text-black dark:text-white">' + data.tanggal + '</span>' +
                        '</div>';
                }

                function addHistory(data) {
                    const emptyItem = scanHistory.querySelector('li.text-sm');
                    if (emptyItem && scanHistory.children.length === 1) {
                        scanHistory.innerHTML = '';
                    }

                    const isReturn = data.mode === 'kembali';
                    const li = document.createElement('li');
                    li.className = 'mb-[12px] flex items-start justify-between gap-[10px] border-b border-white/50 pb-[12px] last:mb-0 last:border-0 last:pb-0 dark:border-white/5';
                    li.innerHTML =
                        '<div class="min-w-0">' +
                            '<span class="block truncate font-medium text-black dark:text-white">' + data.nama_barang + '</span>' +
                            '<span class="block text-xs text-gray-400">' + data.asset_id + ' &middot; ' + data.tanggal + '</span>' +
                            '<span class="block text-xs text-gray-400">Pengambil: ' + (data.nama_pengambil || '-') + '</span>' +
                        '</div>' +
                        '<span class="pill ' + (isReturn ? 'pill-success' : 'pill-danger') + ' shrink-0">' + (isReturn ? 'Kembali' : 'Keluar') + '</span>';

                    scanHistory.prepend(li);

                    while (scanHistory.children.length > 10) {
                        scanHistory.removeChild(scanHistory.lastElementChild);
                    }
                }

                function openPicker(data) {
                    pendingScan = data;
                    isModalOpen = true;
                    pickerItem.textContent = data.asset_id + ' — ' + data.nama_barang;
                    pickerName.value = '';
                    pickerName.required = false;
                    document.querySelector('input[name="picker-mode"][value="keluar"]').checked = true;

                    if (!data.is_available) {
                        document.querySelector('input[name="picker-mode"][value="kembali"]').checked = true;
                    }

                    pickerModal.classList.remove('hidden');
                    pickerModal.classList.add('flex');

                    setTimeout(function () {
                        pickerName.focus();
                    }, 50);
                }

                function closePicker() {
                    isModalOpen = false;
                    pendingScan = null;
                    pickerModal.classList.add('hidden');
                    pickerModal.classList.remove('flex');
                }

                async function processScan(code) {
                    const now = Date.now();
                    if (isModalOpen || isProcessing || (code === lastCode && now - lastCodeTime < 1500)) {
                        return;
                    }

                    isProcessing = true;
                    lastCode = code;
                    lastCodeTime = now;
                    let failed = false;

                    try {
                        const response = await fetch('/dashboard/scan-barcode/process', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({ asset_id: code }),
                        });

                        const result = await response.json();

                        if (!response.ok || result.status !== 'success') {
                            failed = true;
                            renderLastScan(result.message || 'Terjadi kesalahan.', true);
                            showToast(result.message || 'Terjadi kesalahan.', 'error');
                            beep(false);
                        } else {
                            openPicker(result.data);
                            showToast('Unit ditemukan. Pilih Keluar atau Kembali.', 'success');
                            beep(true);
                        }
                    } catch (error) {
                        failed = true;
                        renderLastScan('Gagal menghubungi server.', true);
                        showToast('Gagal menghubungi server.', 'error');
                        beep(false);
                    } finally {
                        if (failed) {
                            setTimeout(function () {
                                isProcessing = false;
                            }, 600);
                        } else {
                            isProcessing = false;
                        }
                    }
                }

                function beep(success) {
                    try {
                        const AudioCtx = window.AudioContext || window.webkitAudioContext;
                        const ctx = new AudioCtx();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();

                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.frequency.value = success ? 900 : 300;
                        osc.type = 'sine';
                        gain.gain.value = 0.08;
                        osc.start();
                        osc.stop(ctx.currentTime + 0.12);
                    } catch (e) {
                        // Audio not supported, ignore.
                    }
                }

                function config() {
                    return {
                        fps: 30,
                        qrbox: function (viewWidth, viewHeight) {
                            const width = Math.floor(viewWidth * 0.85);
                            const height = Math.floor(viewHeight * 0.45);
                            return { width: Math.max(width, 120), height: Math.max(height, 90) };
                        },
                        aspectRatio: 1.0,
                        disableFlip: true,
                        experimentalFeatures: {
                            useBarCodeDetectorIfSupported: true,
                        },
                        formatsToSupport: [
                            Html5QrcodeSupportedFormats.QR_CODE,
                            Html5QrcodeSupportedFormats.CODE_128,
                            Html5QrcodeSupportedFormats.CODE_39,
                            Html5QrcodeSupportedFormats.EAN_13,
                            Html5QrcodeSupportedFormats.EAN_8,
                            Html5QrcodeSupportedFormats.UPC_A,
                            Html5QrcodeSupportedFormats.UPC_E,
                            Html5QrcodeSupportedFormats.ITF,
                        ],
                    };
                }

                function onScanSuccess(decodedText) {
                    processScan(decodedText.trim());
                }

                function cameraErrorMessage(error) {
                    const name = error && error.name ? error.name : '';

                    if (name === 'NotAllowedError' || name === 'PermissionDeniedError' || name === 'SecurityError') {
                        return 'Izin kamera diblokir. Klik ikon kunci (gembok) di address bar, pilih "Kamera: Izinkan", lalu muat ulang halaman.';
                    }

                    if (name === 'NotFoundError' || name === 'DevicesNotFoundError') {
                        return 'Tidak ada kamera terdeteksi pada perangkat ini.';
                    }

                    if (name === 'OverconstrainedError') {
                        return 'Kamera tidak mendukung pengaturan yang diminta. Sistem sudah mencoba mode lain — tekan Nyalakan lagi.';
                    }

                    if (name === 'NotReadableError' || name === 'TrackStartError') {
                        return 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi tersebut (Zoom/Meet/WhatsApp) lalu tekan Nyalakan lagi.';
                    }

                    if (name === 'AbortError') {
                        return 'Akses kamera dibatalkan. Tekan Nyalakan lagi untuk mencoba ulang.';
                    }

                    return 'Tidak bisa mengakses kamera: ' + (error?.message || error || 'kesalahan tidak diketahui');
                }

                async function startWithFallback() {
                    const candidates = [
                        { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } },
                        { facingMode: 'environment' },
                        { facingMode: 'user' },
                    ];

                    try {
                        const cameras = await Html5Qrcode.getCameras();

                        if (cameras && cameras.length) {
                            const back = cameras.find(function (c) {
                                return /back|rear|environment|belakang/i.test(c.label || '');
                            });
                            candidates.push(back ? back.id : cameras[cameras.length - 1].id);
                        }
                    } catch (e) {
                        // getCameras bisa gagal sebelum izin diberikan; lanjut ke kandidat facingMode.
                    }

                    let lastError = null;

                    for (let i = 0; i < candidates.length; i++) {
                        try {
                            if (!html5QrCode) {
                                html5QrCode = new Html5Qrcode('reader', { verbose: false });
                            }

                            await html5QrCode.start(candidates[i], config(), onScanSuccess, function () {});
                            return;
                        } catch (error) {
                            lastError = error;

                            if (html5QrCode) {
                                try {
                                    await html5QrCode.stop();
                                } catch (e) {
                                    // belum berjalan, abaikan.
                                }
                                try {
                                    html5QrCode.clear();
                                } catch (e) {
                                    // abaikan.
                                }
                                html5QrCode = null;
                            }

                            reader.innerHTML = '';

                            if (error && (error.name === 'NotAllowedError' || error.name === 'SecurityError')) {
                                throw error;
                            }
                        }
                    }

                    throw lastError || new Error('Tidak ada kamera yang dapat dibuka pada perangkat ini.');
                }

                async function startCamera() {
                    if (isStarting) {
                        return;
                    }

                    if (!isSecureEnough) {
                        secureContextAlert.classList.remove('hidden');
                        setStatus('HTTPS diperlukan', 'error');
                        showPlaceholder(true, 'HTTPS diperlukan', 'Buka halaman ini lewat HTTPS tepercaya untuk mengakses kamera.', 'lock');
                        renderLastScan('Kamera hanya bisa dibuka lewat HTTPS yang tepercaya. Instal sertifikat CA lalu muat ulang halaman.', true);
                        return;
                    }

                    if (typeof Html5Qrcode === 'undefined' || typeof Html5QrcodeSupportedFormats === 'undefined') {
                        setStatus('Gagal akses kamera', 'error');
                        showPlaceholder(true, 'Komponen scanner gagal dimuat', 'Periksa koneksi internet lalu muat ulang halaman.', 'cloud_off');
                        renderLastScan('Komponen scanner (html5-qrcode) gagal dimuat. Periksa koneksi internet lalu muat ulang halaman.', true);
                        return;
                    }

                    isStarting = true;
                    setStatus('Menyiapkan', 'loading');
                    showPlaceholder(true, 'Mengaktifkan kamera...', 'Izinkan akses kamera pada browser untuk mulai memindai.', 'videocam');
                    btnStart.disabled = true;
                    btnStart.classList.add('opacity-60', 'cursor-not-allowed');

                    try {
                        await startWithFallback();

                        setStatus('Kamera aktif', 'active');
                        showPlaceholder(false);
                        scanOverlay.classList.remove('hidden');
                        btnStart.classList.add('hidden');
                        btnStop.classList.remove('hidden');
                    } catch (error) {
                        setStatus('Gagal akses kamera', 'error');
                        showPlaceholder(true, 'Kamera tidak dapat diakses', cameraErrorMessage(error), 'videocam_off');
                        renderLastScan(cameraErrorMessage(error), true);
                        secureContextAlert.classList.toggle('hidden', window.isSecureContext);
                        btnStart.classList.remove('hidden');
                        btnStop.classList.add('hidden');

                        if (html5QrCode) {
                            try {
                                html5QrCode.clear();
                            } catch (e) {
                                // Ignore.
                            }
                            html5QrCode = null;
                        }
                    } finally {
                        isStarting = false;
                        btnStart.disabled = false;
                        btnStart.classList.remove('opacity-60', 'cursor-not-allowed');
                    }
                }

                async function stopCamera() {
                    if (html5QrCode) {
                        try {
                            await html5QrCode.stop();
                            await html5QrCode.clear();
                        } catch (e) {
                            // Ignore stop errors.
                        }
                        html5QrCode = null;
                    }

                    setStatus('Kamera mati', 'idle');
                    scanOverlay.classList.add('hidden');
                    showPlaceholder(true, 'Kamera dimatikan', 'Tekan Nyalakan untuk mengaktifkan kamera kembali.', 'videocam_off');
                    btnStart.classList.remove('hidden');
                    btnStop.classList.add('hidden');
                }

                pickerForm.addEventListener('submit', async function (event) {
                    event.preventDefault();

                    if (!pendingScan || isSaving) {
                        return;
                    }

                    const namaPengambil = pickerName.value.trim();
                    const mode = document.querySelector('input[name="picker-mode"]:checked').value;

                    if (mode === 'keluar' && !namaPengambil) {
                        showToast('Nama pengambil wajib diisi untuk transaksi keluar.', 'error');
                        pickerName.focus();
                        return;
                    }

                    isSaving = true;
                    pickerSave.disabled = true;
                    pickerSave.classList.add('opacity-50');

                    try {
                        const response = await fetch('/dashboard/scan-barcode/store', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                asset_id: pendingScan.asset_id,
                                mode: mode,
                                nama_pengambil: namaPengambil || null,
                                lokasi_id: pickerLokasi.value || null,
                            }),
                        });

                        const result = await response.json();

                        if (!response.ok || result.status !== 'success') {
                            showToast(result.message || 'Gagal menyimpan scan.', 'error');
                            beep(false);
                            return;
                        }

                        renderLastScan(result.data, false);
                        addHistory(result.data);
                        closePicker();
                        showToast(result.message || 'Stok keluar berhasil dicatat.', 'success');
                        beep(true);
                    } catch (error) {
                        showToast('Gagal menghubungi server.', 'error');
                        beep(false);
                    } finally {
                        isSaving = false;
                        pickerSave.disabled = false;
                        pickerSave.classList.remove('opacity-50');
                    }
                });

                pickerCancel.addEventListener('click', closePicker);
                pickerClose.addEventListener('click', closePicker);
                pickerBackdrop.addEventListener('click', closePicker);

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && isModalOpen) {
                        closePicker();
                    }
                });

                btnStart.addEventListener('click', startCamera);
                btnStop.addEventListener('click', stopCamera);

                if (!isSecureEnough) {
                    secureContextAlert.classList.remove('hidden');
                    setStatus('HTTPS diperlukan', 'error');
                    showPlaceholder(true, 'HTTPS diperlukan', 'Buka halaman ini lewat HTTPS tepercaya untuk mengakses kamera.', 'lock');
                } else {
                    window.addEventListener('load', function () {
                        startCamera();
                    });
                }
            })();
        </script>
    </body>
</html>
