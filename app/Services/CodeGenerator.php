<?php

namespace App\Services;

use App\Models\Sequence;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class CodeGenerator
{
    /**
     * Ambil nomor urut berikutnya dari sebuah sequence secara atomik.
     * Harus dipanggil di dalam transaksi database agar aman dari race condition.
     */
    public function next(string $name, int $start = 0): int
    {
        Sequence::firstOrCreate(['name' => $name], ['value' => $start]);

        $sequence = Sequence::where('name', $name)->lockForUpdate()->first();

        $sequence->value = $sequence->value + 1;
        $sequence->save();

        return $sequence->value;
    }

    /**
     * Asset ID unik global, contoh: JMK-AST-000001.
     * Tidak akan dipakai ulang meskipun unit dihapus (nomor selalu naik).
     */
    public function assetId(): string
    {
        return sprintf('JMK-AST-%06d', $this->next('asset_id'));
    }

    /**
     * SKU jenis barang, contoh: JMK-INV96.
     */
    public function sku(): string
    {
        return sprintf('JMK-INV%02d', $this->next('sku_jmk', 95));
    }

    /**
     * Kode transaksi harian, contoh: TRX-20261024-0001.
     */
    public function transactionCode(?CarbonInterface $date = null): string
    {
        $date = $date ? Carbon::instance($date) : now();
        $key = 'trx_' . $date->format('Ymd');
        $number = $this->next($key);

        return sprintf('TRX-%s-%04d', $date->format('Ymd'), $number);
    }

    /**
     * Kode sesi opname, contoh: OPN-20261024-0001.
     */
    public function opnameCode(?CarbonInterface $date = null): string
    {
        $date = $date ? Carbon::instance($date) : now();
        $key = 'opn_' . $date->format('Ymd');
        $number = $this->next($key);

        return sprintf('OPN-%s-%04d', $date->format('Ymd'), $number);
    }
}
