<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Cetak Label Unit</title>
        @vite('resources/css/app.css')
        <style>
            .label-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 12px;
            }
            .label-card {
                border: 1px dashed #cbd5e1;
                border-radius: 10px;
                padding: 12px;
                background: #fff;
                color: #0f172a;
                break-inside: avoid;
            }
            .label-card .asset {
                font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
                font-size: 15px;
                font-weight: 700;
                letter-spacing: .5px;
            }
            .label-card .name { font-size: 12px; font-weight: 600; }
            .label-card .meta { font-size: 10px; color: #64748b; }
            .label-card svg { max-width: 100%; height: 46px; }
            @media print {
                .no-print { display: none !important; }
                body { background: #fff !important; }
                @page { margin: 10mm; }
            }
        </style>
    </head>
    <body class="bg-slate-100">
        <div class="mx-auto max-w-[1200px] p-[20px]">
            <div class="no-print mb-[20px] flex flex-wrap items-center justify-between gap-[12px] rounded-xl bg-white p-[16px] shadow-sm">
                <div>
                    <h2 class="m-0 text-lg font-bold text-slate-800">Cetak Label Unit</h2>
                    <p class="m-0 text-sm text-slate-500">{{ $units->count() }} label siap dicetak.</p>
                </div>
                <div class="flex items-center gap-[10px]">
                    <a href="{{ route('barang-units.index') }}" class="rounded-lg border border-slate-300 px-[16px] py-[9px] text-sm font-semibold text-slate-600">Kembali</a>
                    <button type="button" onclick="window.print()" class="rounded-lg bg-primary-500 px-[16px] py-[9px] text-sm font-semibold text-white">Cetak</button>
                </div>
            </div>

            @if($units->isEmpty())
                <div class="rounded-xl bg-white p-[40px] text-center text-slate-400">Tidak ada unit untuk dicetak.</div>
            @else
                <div class="label-grid">
                    @foreach($units as $unit)
                        <div class="label-card">
                            <div class="mb-[6px] flex items-start justify-between gap-[8px]">
                                <div class="name">{{ $unit->barang->nama_barang ?? '-' }}</div>
                                <div class="qr-mini shrink-0" data-code="{{ $unit->asset_id }}"></div>
                            </div>
                            <svg class="barcode" data-code="{{ $unit->asset_id }}"></svg>
                            <div class="asset mt-[4px]">{{ $unit->asset_id }}</div>
                            <div class="meta mt-[2px]">
                                {{ $unit->barang->kode_barang ?? '' }}
                                @if($unit->serial_number) · SN: {{ $unit->serial_number }} @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
        <script>
            document.querySelectorAll('.barcode').forEach(function (el) {
                try {
                    JsBarcode(el, el.getAttribute('data-code'), {
                        format: 'CODE128',
                        displayValue: false,
                        height: 46,
                        margin: 0,
                    });
                } catch (e) {}
            });

            document.querySelectorAll('.qr-mini').forEach(function (el) {
                new QRCode(el, {
                    text: el.getAttribute('data-code'),
                    width: 44,
                    height: 44,
                    correctLevel: QRCode.CorrectLevel.M,
                });
            });
        </script>
    </body>
</html>
