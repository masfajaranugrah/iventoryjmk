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
        return $this->reserve($name, 1, $start)[0];
    }

    /**
     * Pesan beberapa nomor urut sekaligus dalam satu kali update.
     * Jauh lebih cepat daripada memanggil next() berulang kali.
     *
     * @return array<int, int>
     */
    public function reserve(string $name, int $count, int $start = 0): array
    {
        if ($count < 1) {
            return [];
        }

        Sequence::firstOrCreate(['name' => $name], ['value' => $start]);

        $sequence = Sequence::where('name', $name)->lockForUpdate()->first();

        $from = (int) $sequence->value + 1;
        $sequence->value = (int) $sequence->value + $count;
        $sequence->save();

        return range($from, (int) $sequence->value);
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
     * @return array<int, string>
     */
    public function assetIds(int $count): array
    {
        return array_map(
            fn (int $number) => sprintf('JMK-AST-%06d', $number),
            $this->reserve('asset_id', $count)
        );
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
        $key = 'trx_'.$date->format('Ymd');
        $number = $this->next($key);

        return sprintf('TRX-%s-%04d', $date->format('Ymd'), $number);
    }

    /**
     * @return array<int, string>
     */
    public function transactionCodes(int $count, ?CarbonInterface $date = null): array
    {
        $date = $date ? Carbon::instance($date) : now();
        $key = 'trx_'.$date->format('Ymd');

        return array_map(
            fn (int $number) => sprintf('TRX-%s-%04d', $date->format('Ymd'), $number),
            $this->reserve($key, $count)
        );
    }

    /**
     * Kode sesi opname, contoh: OPN-20261024-0001.
     */
    public function opnameCode(?CarbonInterface $date = null): string
    {
        $date = $date ? Carbon::instance($date) : now();
        $key = 'opn_'.$date->format('Ymd');
        $number = $this->next($key);

        return sprintf('OPN-%s-%04d', $date->format('Ymd'), $number);
    }
}
