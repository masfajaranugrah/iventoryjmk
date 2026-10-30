<table border="1">
    <thead>
        <tr>
            <th colspan="8" style="font-size:14px;font-weight:bold;">Stock Opname {{ $opname->kode_opname }}</th>
        </tr>
        <tr>
            <td colspan="8">Tanggal: {{ $opname->tanggal->format('d/m/Y') }} | Lokasi: {{ $opname->lokasi->nama ?? 'Semua Lokasi' }} | Status: {{ $opname->isSelesai() ? 'Selesai' : 'Berproses' }}</td>
        </tr>
        <tr style="background:#eee;font-weight:bold;">
            <th>No</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Tipe</th>
            <th>Stok Sistem</th>
            <th>Stok Fisik</th>
            <th>Selisih</th>
            <th>Catatan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->barang->kode_barang ?? '-' }}</td>
                <td>{{ $item->barang->nama_barang ?? '-' }}</td>
                <td>{{ $item->barang?->isTrackedPerUnit() ? 'Per Unit' : 'Per Jumlah' }}</td>
                <td>{{ $item->stok_sistem }}</td>
                <td>{{ $item->stok_fisik ?? '' }}</td>
                <td>{{ $item->selisih ?? '' }}</td>
                <td>{{ $item->catatan ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
