<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }}</title>
        <style>
            body { font-family: Arial, Helvetica, sans-serif; color: #0f172a; font-size: 11px; padding: 16px; }
            h1 { font-size: 18px; margin: 0 0 4px; }
            .meta { color: #64748b; margin-bottom: 12px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #cbd5e1; padding: 5px 7px; text-align: left; }
            th { background: #f1f5f9; }
            .no-print { margin-bottom: 12px; }
            @media print { .no-print { display: none; } @page { margin: 10mm; } }
        </style>
    </head>
    <body>
        <div class="no-print">
            <button onclick="window.print()">Cetak / Simpan PDF</button>
        </div>

        <h1>{{ $title }}</h1>
        <div class="meta">Periode: {{ $dari->format('d M Y') }} – {{ $sampai->format('d M Y') }} &middot; Dicetak: {{ now()->format('d M Y H:i') }}</div>

        <table>
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
                        <td>{{ $index + 1 }}</td>
                        @foreach($row as $value)
                            <td>{{ $value }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="12">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>
</html>
