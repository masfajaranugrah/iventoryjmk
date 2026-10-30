<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Opname {{ $opname->kode_opname }}</title>
        <style>
            body { font-family: Arial, Helvetica, sans-serif; color: #0f172a; font-size: 12px; padding: 16px; }
            h1 { font-size: 18px; margin: 0 0 4px; }
            .meta { color: #64748b; margin-bottom: 12px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
            th { background: #f1f5f9; }
            .text-center { text-align: center; }
            .danger { color: #dc2626; font-weight: 700; }
            .success { color: #16a34a; font-weight: 700; }
            .no-print { margin-bottom: 12px; }
            @media print { .no-print { display: none; } @page { margin: 10mm; } }
        </style>
    </head>
    <body>
        <div class="no-print">
            <button onclick="window.print()">Cetak / Simpan PDF</button>
        </div>

        <h1>Berita Acara Stock Opname</h1>
        <div class="meta">
            Kode: <strong>{{ $opname->kode_opname }}</strong> &middot;
            Tanggal: {{ $opname->tanggal->format('d M Y') }} &middot;
            Lokasi: {{ $opname->lokasi->nama ?? 'Semua Lokasi' }} &middot;
            Status: {{ $opname->isSelesai() ? 'Selesai' : 'Berproses' }}
        </div>

        <table>
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th class="text-center">Stok Sistem</th>
                    <th class="text-center">Stok Fisik</th>
                    <th class="text-center">Selisih</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i => $item)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $item->barang->kode_barang ?? '-' }}</td>
                        <td>{{ $item->barang->nama_barang ?? '-' }}</td>
                        <td class="text-center">{{ $item->stok_sistem }}</td>
                        <td class="text-center">{{ $item->stok_fisik ?? '-' }}</td>
                        <td class="text-center {{ $item->selisih > 0 ? 'success' : ($item->selisih < 0 ? 'danger' : '') }}">
                            {{ $item->selisih === null ? '-' : ($item->selisih > 0 ? '+' : '') . $item->selisih }}
                        </td>
                        <td>{{ $item->catatan ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p style="margin-top:24px;">Dibuat oleh: {{ $opname->user ?? '-' }}</p>
    </body>
</html>
