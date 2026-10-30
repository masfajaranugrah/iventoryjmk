<?php

namespace App\Console\Commands;

use App\Models\Barang;
use App\Models\BarangUnit;
use App\Models\Transaksi;
use Illuminate\Console\Command;

class ReconcileStock extends Command
{
    protected $signature = 'stock:reconcile
                            {--fix : Perbaiki stok barang per unit dari status unit}
                            {--baseline : Catat saldo awal untuk barang per jumlah yang belum punya ledger}';

    protected $description = 'Periksa dan sinkronkan stok barang dengan ledger transaksi atau status unit';

    public function handle(): int
    {
        $fix = (bool) $this->option('fix');
        $baseline = (bool) $this->option('baseline');
        $checked = 0;
        $mismatches = 0;
        $fixed = 0;
        $baselined = 0;

        $this->info('Memeriksa stok barang...');

        Barang::withTrashed()->chunkById(200, function ($barangs) use ($fix, $baseline, &$checked, &$mismatches, &$fixed, &$baselined) {
            foreach ($barangs as $barang) {
                $checked++;

                if ($baseline && ! $barang->isTrackedPerUnit()) {
                    if ($this->createBaseline($barang)) {
                        $baselined++;
                    }
                }

                $expected = $this->expectedStock($barang);
                $actual = (int) $barang->stok;

                if ($expected === $actual) {
                    continue;
                }

                $mismatches++;

                $this->warn(sprintf(
                    '%s %s | stok tercatat: %d | seharusnya: %d | %s',
                    $barang->kode_barang,
                    $barang->nama_barang . ($barang->trashed() ? ' (dihapus)' : ''),
                    $actual,
                    $expected,
                    $barang->isTrackedPerUnit() ? 'unit tersedia' : 'ledger transaksi'
                ));

                if ($fix && $barang->isTrackedPerUnit()) {
                    $barang->stok = $expected;
                    $barang->save();
                    $fixed++;
                } elseif ($fix) {
                    $this->line('  -> Barang per jumlah tidak diubah. Jalankan --baseline untuk mencatat saldo awal.');
                }
            }
        });

        $this->info("Selesai. {$checked} barang diperiksa, {$mismatches} stok tidak sesuai.");

        if ($baselined > 0) {
            $this->info("{$baselined} saldo awal dibuat. Jalankan ulang tanpa --baseline untuk memverifikasi.");
        }

        if ($fixed > 0) {
            $this->info("{$fixed} stok barang per unit diperbaiki.");
        }

        return self::SUCCESS;
    }

    private function expectedStock(Barang $barang): int
    {
        if ($barang->isTrackedPerUnit()) {
            return $barang->units()->where('status', BarangUnit::STATUS_TERSEDIA)->count();
        }

        return $this->ledgerTotal($barang);
    }

    private function ledgerTotal(Barang $barang): int
    {
        return (int) Transaksi::where('barang_id', $barang->id)
            ->get(['tipe', 'jumlah'])
            ->sum(fn (Transaksi $transaksi) => $transaksi->stockEffect());
    }

    private function createBaseline(Barang $barang): bool
    {
        $difference = (int) $barang->stok - $this->ledgerTotal($barang);

        if ($difference === 0) {
            return false;
        }

        Transaksi::create([
            'kode_transaksi' => 'SALDO-AWAL-' . $barang->id . '-' . now()->format('YmdHis'),
            'barang_id' => $barang->id,
            'tipe' => Transaksi::TIPE_PENYESUAIAN,
            'jumlah' => $difference,
            'user' => 'system',
            'catatan' => 'Saldo awal sistem (rekonsiliasi stok)',
            'tanggal' => now(),
        ]);

        return true;
    }
}
