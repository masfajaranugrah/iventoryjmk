<table border="1">
    <thead>
        <tr>
            <th colspan="{{ count($rows[0] ?? ['-']) + 1 }}" style="font-size:14px;font-weight:bold;">
                {{ [
                    'ringkasan' => 'Laporan Ringkasan Stok (Masuk / Keluar)',
                    'mutasi' => 'Laporan Mutasi Stok',
                    'stok' => 'Laporan Stok Barang',
                    'lokasi' => 'Laporan Stok per Lokasi',
                    'hilang_rusak' => 'Laporan Barang Hilang / Rusak',
                    'selisih' => 'Laporan Selisih Opname',
                ][$jenis] }}
            </th>
        </tr>
        <tr>
            <td colspan="{{ count($rows[0] ?? ['-']) + 1 }}">
                Periode: {{ $dari->format('d/m/Y') }} - {{ $sampai->format('d/m/Y') }}
            </td>
        </tr>
        <tr style="background:#eee;font-weight:bold;">
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
