<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\Lokasi;
use App\Models\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangSeeder extends Seeder
{
    /**
     * Master barang JMK: 95 kode (JMK-INV01 - JMK-INV95).
     * "unit"      = perangkat/alat yang dilacak per unit + Asset ID.
     * "jumlah"    = barang habis pakai yang dilacak per jumlah/satuan.
     *
     * @return array<int, array<string, string>>
     */
    private function items(): array
    {
        $unit = [
            ['Modem GPON Huawei HG8145V5', 'Perangkat Jaringan', 'Huawei', 'HG8145V5', 'Unit'],
            ['Modem GPON ZTE F609', 'Perangkat Jaringan', 'ZTE', 'F609', 'Unit'],
            ['Modem ADSL TP-Link TD-8817', 'Perangkat Jaringan', 'TP-Link', 'TD-8817', 'Unit'],
            ['Router Mikrotik RB750Gr3', 'Router', 'Mikrotik', 'RB750Gr3', 'Unit'],
            ['Router Mikrotik RB4011', 'Router', 'Mikrotik', 'RB4011', 'Unit'],
            ['Router TP-Link Archer C6', 'Router', 'TP-Link', 'Archer C6', 'Unit'],
            ['Router Huawei AX3', 'Router', 'Huawei', 'AX3', 'Unit'],
            ['Access Point Ubiquiti UniFi AC Lite', 'Access Point', 'Ubiquiti', 'UAP-AC-Lite', 'Unit'],
            ['Access Point Ubiquiti UniFi 6 Lite', 'Access Point', 'Ubiquiti', 'U6-Lite', 'Unit'],
            ['Access Point TP-Link EAP225', 'Access Point', 'TP-Link', 'EAP225', 'Unit'],
            ['OLT Huawei MA5608T', 'OLT', 'Huawei', 'MA5608T', 'Unit'],
            ['OLT ZTE C320', 'OLT', 'ZTE', 'C320', 'Unit'],
            ['OLT VSOL V1600G', 'OLT', 'VSOL', 'V1600G', 'Unit'],
            ['ONT Huawei HG8010H', 'ONT', 'Huawei', 'HG8010H', 'Unit'],
            ['ONT ZTE F601', 'ONT', 'ZTE', 'F601', 'Unit'],
            ['OTDR Fiber Fusion', 'Alat Ukur', 'Fiber Fusion', '-', 'Unit'],
            ['OPM Optical Power Meter', 'Alat Ukur', 'Generic', '-', 'Unit'],
            ['VFL Visual Fault Locator', 'Alat Ukur', 'Generic', '-', 'Unit'],
            ['Fusion Splicer Fujikura 88S', 'Alat Ukur', 'Fujikura', '88S', 'Unit'],
            ['Fusion Splicer Signal Fire AI-9', 'Alat Ukur', 'Signal Fire', 'AI-9', 'Unit'],
            ['Switch Cisco Catalyst 2960 24 Port', 'Switch', 'Cisco', '2960-24TT', 'Unit'],
            ['Switch HPE 8 Port', 'Switch', 'HPE', 'OfficeConnect 8G', 'Unit'],
            ['Switch PoE 16 Port', 'Switch', 'Generic', 'PoE-16', 'Unit'],
            ['Switch Mikrotik CRS326', 'Switch', 'Mikrotik', 'CRS326', 'Unit'],
            ['Media Converter Gigabit 10/100/1000', 'Media Converter', 'Generic', 'MC-GE', 'Unit'],
            ['Media Converter 10G', 'Media Converter', 'Generic', 'MC-10G', 'Unit'],
            ['SFP Module 1.25G', 'SFP', 'Generic', 'SFP-1G', 'Unit'],
            ['SFP+ Module 10G', 'SFP', 'Generic', 'SFP-10G', 'Unit'],
            ['GPON OLT SFP Class B+', 'SFP', 'Generic', 'B+', 'Unit'],
            ['CCTV Camera Hikvision Dome', 'CCTV', 'Hikvision', 'DS-2CD', 'Unit'],
            ['CCTV Camera Hikvision Bullet', 'CCTV', 'Hikvision', 'DS-2CE', 'Unit'],
            ['CCTV Camera Dahua Bullet', 'CCTV', 'Dahua', 'IPC-HFW', 'Unit'],
            ['NVR Hikvision 8 Channel', 'CCTV', 'Hikvision', 'DS-7608', 'Unit'],
            ['DVR Hikvision 16 Channel', 'CCTV', 'Hikvision', 'DS-7216', 'Unit'],
            ['Hard Disk WD Purple 1TB', 'Storage', 'Western Digital', 'WD10PURZ', 'Unit'],
            ['HTB Motorola', 'Telekomunikasi', 'Motorola', '-', 'Unit'],
            ['HTB Baofeng UV-5R', 'Telekomunikasi', 'Baofeng', 'UV-5R', 'Unit'],
            ['Repeater HTB', 'Telekomunikasi', 'Generic', '-', 'Unit'],
            ['Antenna Grid 2.4GHz', 'Antenna', 'Generic', 'Grid-2G', 'Unit'],
            ['Antenna Sectoral 5GHz', 'Antenna', 'Generic', 'Sector-5G', 'Unit'],
            ['Radio Access Point Ubiquiti PowerBeam', 'Radio', 'Ubiquiti', 'PowerBeam', 'Unit'],
            ['Radio Access Point Mikrotik SXT', 'Radio', 'Mikrotik', 'SXT', 'Unit'],
            ['Radio LiteBeam AC', 'Radio', 'Ubiquiti', 'LiteBeam AC', 'Unit'],
            ['Server Dell PowerEdge', 'Server', 'Dell', 'PowerEdge', 'Unit'],
            ['Server HP ProLiant', 'Server', 'HP', 'ProLiant', 'Unit'],
            ['NAS Synology DS220', 'Storage', 'Synology', 'DS220', 'Unit'],
            ['UPS APC 1500VA', 'Power', 'APC', '1500VA', 'Unit'],
            ['UPS ICA 1000VA', 'Power', 'ICA', '1000VA', 'Unit'],
            ['Stabilizer 1000VA', 'Power', 'Generic', '1000VA', 'Unit'],
            ['Rack Server 20U', 'Rack', 'Generic', '20U', 'Unit'],
            ['Rack Server 42U', 'Rack', 'Generic', '42U', 'Unit'],
            ['Patch Panel 24 Port', 'Rack', 'Generic', '24P', 'Unit'],
            ['ODF Optical Distribution Frame 24', 'Fiber Optic', 'Generic', 'ODF-24', 'Unit'],
            ['OTB Optical Termination Box', 'Fiber Optic', 'Generic', 'OTB', 'Unit'],
            ['ODP Optical Distribution Point', 'Fiber Optic', 'Generic', 'ODP', 'Unit'],
            ['Closure Joint Fiber Optic', 'Fiber Optic', 'Generic', 'Closure', 'Unit'],
            ['Adaptor SC/APC', 'Fiber Optic', 'Generic', 'SC/APC', 'Unit'],
            ['Attenuator SC/APC 5dB', 'Fiber Optic', 'Generic', '5dB', 'Unit'],
            ['Power Supply 12V 5A', 'Power', 'Generic', '12V5A', 'Unit'],
            ['Power Supply 48V 10A', 'Power', 'Generic', '48V10A', 'Unit'],
            ['Tangga Fiber', 'Alat Kerja', 'Generic', '-', 'Unit'],
            ['Kunci Fiber', 'Alat Kerja', 'Generic', '-', 'Unit'],
            ['Tester Kabel LAN', 'Alat Kerja', 'Generic', 'RJ45-Tester', 'Unit'],
            ['Crimping Tool RJ45', 'Alat Kerja', 'Generic', '-', 'Unit'],
            ['Tang Kombinasi', 'Alat Kerja', 'Generic', '-', 'Unit'],
            ['Obeng Set', 'Alat Kerja', 'Generic', '-', 'Set'],
            ['Bor Listrik', 'Alat Kerja', 'Generic', '-', 'Unit'],
            ['Gerinda Tangan', 'Alat Kerja', 'Generic', '-', 'Unit'],
            ['Multimeter Digital', 'Alat Kerja', 'Generic', '-', 'Unit'],
            ['Tang Ampere Clamp', 'Alat Kerja', 'Generic', 'Clamp', 'Unit'],
            ['Gergaji Besi', 'Alat Kerja', 'Generic', '-', 'Unit'],
            ['Solder Set', 'Alat Kerja', 'Generic', '-', 'Set'],
            ['Heat Gun', 'Alat Kerja', 'Generic', '-', 'Unit'],
            ['Alat Pelubang Plat', 'Alat Kerja', 'Generic', '-', 'Unit'],
            ['Cutter Besar', 'Alat Kerja', 'Generic', '-', 'Unit'],
            ['Meteran 50M', 'Alat Kerja', 'Generic', '50M', 'Unit'],
            ['Tool Kit Jaringan', 'Alat Kerja', 'Generic', 'TK-Net', 'Set'],
            ['Tas Tool Set', 'Alat Kerja', 'Generic', '-', 'Unit'],
            ['Helm Proyek', 'Safety', 'Generic', '-', 'Unit'],
            ['Rompi Safety', 'Safety', 'Generic', '-', 'Unit'],
            ['Harnes Body', 'Safety', 'Generic', '-', 'Unit'],
            ['Sepatu Safety', 'Safety', 'Generic', '-', 'Unit'],
        ];

        $jumlah = [
            ['Kabel UTP Cat6', 'Kabel', 'Generic', 'Cat6', 'Roll'],
            ['Kabel UTP Cat5e', 'Kabel', 'Generic', 'Cat5e', 'Roll'],
            ['Kabel Fiber Optic 1 Core', 'Kabel', 'Generic', '1C', 'Meter'],
            ['Kabel Dropcore', 'Kabel', 'Generic', 'Dropcore', 'Meter'],
            ['Konektor RJ45', 'Konektor', 'Generic', 'RJ45', 'Pcs'],
            ['Konektor SC/APC', 'Konektor', 'Generic', 'SC/APC', 'Pcs'],
            ['Konektor LC', 'Konektor', 'Generic', 'LC', 'Pcs'],
            ['Isolasi Listrik', 'Consumable', 'Generic', '-', 'Roll'],
            ['Isolasi Fiber', 'Consumable', 'Generic', '-', 'Roll'],
            ['Cable Tie', 'Consumable', 'Generic', '-', 'Pack'],
            ['Pipa PVC 20mm', 'Consumable', 'Generic', '20mm', 'Batang'],
            ['Bracket Antenna', 'Consumable', 'Generic', '-', 'Pcs'],
            ['Baut & Mur Set', 'Consumable', 'Generic', '-', 'Set'],
        ];

        $result = [];
        $no = 1;

        foreach ([['unit', $unit], ['jumlah', $jumlah]] as [$tipe, $rows]) {
            foreach ($rows as $row) {
                $result[] = [
                    'kode_barang' => sprintf('JMK-INV%02d', $no),
                    'nama_barang' => $row[0],
                    'kategori' => $row[1],
                    'merek' => $row[2],
                    'model' => $row[3],
                    'satuan' => $row[4],
                    'tipe_pelacakan' => $tipe,
                ];
                $no++;
            }
        }

        return $result;
    }

    public function run(): void
    {
        Lokasi::firstOrCreate(['nama' => 'Gudang Utama'], ['keterangan' => 'Penyimpanan utama']);
        Lokasi::firstOrCreate(['nama' => 'Rak Perangkat'], ['keterangan' => 'Rak perangkat aktif']);
        Lokasi::firstOrCreate(['nama' => 'Di Teknisi'], ['keterangan' => 'Perangkat dibawa teknisi']);

        DB::transaction(function () {
            foreach ($this->items() as $item) {
                Barang::updateOrCreate(
                    ['kode_barang' => $item['kode_barang']],
                    $item
                );
            }

            Sequence::updateOrCreate(['name' => 'sku_jmk'], ['value' => 95]);
        });
    }
}
