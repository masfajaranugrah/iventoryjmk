<?php

return [
    [
        'type' => 'section',
        'name' => 'Inventaris',
        'items' => [
            [
                'type' => 'link',
                'name' => 'Stok Barang',
                'icon' => 'inventory_2',
                'url' => '/dashboard/stok-barang',
            ],
            [
                'type' => 'link',
                'name' => 'Unit & Barcode',
                'icon' => 'barcode',
                'url' => '/dashboard/barang-units',
            ],
            [
                'type' => 'link',
                'name' => 'Scan Barcode',
                'icon' => 'qr_code_scanner',
                'url' => '/dashboard/scan-barcode',
            ],
        ],
    ],
    [
        'type' => 'section',
        'name' => 'Transaksi',
        'items' => [
            [
                'type' => 'link',
                'name' => 'Stok Masuk',
                'icon' => 'move_to_inbox',
                'url' => '/dashboard/stok-masuk',
            ],
            [
                'type' => 'link',
                'name' => 'Stok Keluar',
                'icon' => 'outbox',
                'url' => '/dashboard/stok-keluar',
            ],
            [
                'type' => 'link',
                'name' => 'Riwayat Stok',
                'icon' => 'history',
                'url' => '/dashboard/riwayat',
            ],
        ],
    ],
    [
        'type' => 'section',
        'name' => 'Analisa',
        'items' => [
            [
                'type' => 'link',
                'name' => 'Stock Opname',
                'icon' => 'fact_check',
                'url' => '/dashboard/opname',
            ],
            [
                'type' => 'link',
                'name' => 'Laporan',
                'icon' => 'summarize',
                'url' => '/dashboard/laporan',
            ],
        ],
    ],
];
