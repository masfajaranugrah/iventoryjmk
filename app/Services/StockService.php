<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\BarangUnit;
use App\Models\Transaksi;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockService
{
    public function __construct(private CodeGenerator $codes)
    {
    }

    /**
     * Penerimaan barang baru.
     *
     * - Barang per unit: membuat sejumlah unit baru dengan Asset ID & barcode unik.
     * - Barang per jumlah: menambah stok pada jenis barang.
     *
     * @param  array<int, string|null>  $serialNumbers
     */
    public function receive(
        Barang $barang,
        int $jumlah,
        array $serialNumbers = [],
        ?int $lokasiId = null,
        ?string $user = null,
        ?string $namaPemasok = null,
        ?string $catatan = null,
        ?int $userId = null,
        ?CarbonInterface $tanggal = null
    ): void {
        if ($jumlah < 1) {
            throw new RuntimeException('Jumlah barang harus minimal 1.');
        }

        if ($barang->isTrackedPerUnit()) {
            $this->assertSerialsAvailable($barang, $jumlah, $serialNumbers);
        }

        DB::transaction(function () use ($barang, $jumlah, $serialNumbers, $lokasiId, $user, $namaPemasok, $catatan, $userId, $tanggal) {
            $barang = Barang::lockForUpdate()->findOrFail($barang->id);

            if ($barang->isTrackedPerUnit()) {
                for ($i = 0; $i < $jumlah; $i++) {
                    $serial = $serialNumbers[$i] ?? null;
                    $serial = is_string($serial) ? trim($serial) : null;

                    $unit = BarangUnit::create([
                        'barang_id' => $barang->id,
                        'asset_id' => $this->codes->assetId(),
                        'serial_number' => $serial !== '' ? $serial : null,
                        'status' => BarangUnit::STATUS_TERSEDIA,
                        'lokasi_id' => $lokasiId,
                    ]);

                    $this->record($barang, [
                        'barang_unit_id' => $unit->id,
                        'tipe' => Transaksi::TIPE_MASUK,
                        'jumlah' => 1,
                        'status_sesudah' => $unit->status,
                        'lokasi_id' => $lokasiId,
                        'user' => $user,
                        'user_id' => $userId,
                        'nama_pemasok' => $namaPemasok,
                        'catatan' => $catatan,
                        'tanggal' => $tanggal,
                    ]);
                }

                $this->syncUnitStock($barang);
            } else {
                $barang->stok = $barang->stok + $jumlah;
                $barang->save();

                $this->record($barang, [
                    'tipe' => Transaksi::TIPE_MASUK,
                    'jumlah' => $jumlah,
                    'lokasi_id' => $lokasiId,
                    'user' => $user,
                    'user_id' => $userId,
                    'nama_pemasok' => $namaPemasok,
                    'catatan' => $catatan,
                    'tanggal' => $tanggal,
                ]);
            }
        });
    }

    /**
     * Barang keluar.
     *
     * - Barang per unit: unit status berubah (default "di_teknisi").
     * - Barang per jumlah: stok dikurangi sejumlah qty.
     */
    public function issue(
        Barang $barang,
        int $jumlah,
        ?string $namaPengambil = null,
        ?string $user = null,
        ?string $catatan = null,
        string $status = BarangUnit::STATUS_DI_TEKNISI,
        ?int $lokasiId = null,
        ?int $userId = null,
        ?CarbonInterface $tanggal = null
    ): void {
        DB::transaction(function () use ($barang, $jumlah, $namaPengambil, $user, $catatan, $status, $lokasiId, $userId, $tanggal) {
            $barang = Barang::lockForUpdate()->findOrFail($barang->id);

            if ($barang->isTrackedPerUnit()) {
                $units = BarangUnit::where('barang_id', $barang->id)
                    ->where('status', BarangUnit::STATUS_TERSEDIA)
                    ->orderBy('id')
                    ->limit($jumlah)
                    ->lockForUpdate()
                    ->get();

                if ($units->count() < $jumlah) {
                    throw new RuntimeException('Stok tersedia tidak mencukupi. Tersedia: ' . $units->count() . ' unit.');
                }

                foreach ($units as $unit) {
                    $this->applyUnitStatus($unit, $status, $namaPengambil, $user, $catatan, Transaksi::TIPE_KELUAR, $lokasiId, $userId, $tanggal);
                }

                $this->syncUnitStock($barang);
            } else {
                if ($barang->stok < $jumlah) {
                    throw new RuntimeException('Stok tidak mencukupi. Sisa: ' . $barang->stok . '.');
                }

                $barang->stok = $barang->stok - $jumlah;
                $barang->save();

                $this->record($barang, [
                    'tipe' => Transaksi::TIPE_KELUAR,
                    'jumlah' => $jumlah,
                    'lokasi_id' => $lokasiId,
                    'nama_pengambil' => $namaPengambil,
                    'user' => $user,
                    'user_id' => $userId,
                    'catatan' => $catatan,
                    'tanggal' => $tanggal,
                ]);
            }
        });
    }

    /**
     * Barang kembali ke gudang. Untuk unit, Asset ID lama tetap dipakai.
     */
    public function returnUnit(
        string $assetId,
        ?int $lokasiId = null,
        string $status = BarangUnit::STATUS_TERSEDIA,
        ?string $user = null,
        ?string $catatan = null,
        ?string $namaPengambil = null,
        ?int $userId = null,
        ?CarbonInterface $tanggal = null
    ): BarangUnit {
        return DB::transaction(function () use ($assetId, $lokasiId, $status, $user, $catatan, $namaPengambil, $userId, $tanggal) {
            $unit = BarangUnit::where('asset_id', $assetId)->lockForUpdate()->firstOrFail();
            $barang = Barang::lockForUpdate()->findOrFail($unit->barang_id);

            $this->applyUnitStatus($unit, $status, $namaPengambil, $user, $catatan, Transaksi::TIPE_KEMBALI, $lokasiId, $userId, $tanggal);
            $this->syncUnitStock($barang);

            return $unit->fresh(['barang', 'lokasi']);
        });
    }

    /**
     * Keluar berdasarkan Asset ID (hasil scan).
     */
    public function issueUnit(
        string $assetId,
        ?string $namaPengambil = null,
        ?string $user = null,
        ?string $catatan = null,
        string $status = BarangUnit::STATUS_DI_TEKNISI,
        ?int $lokasiId = null,
        ?int $userId = null,
        ?CarbonInterface $tanggal = null
    ): BarangUnit {
        return DB::transaction(function () use ($assetId, $namaPengambil, $user, $catatan, $status, $lokasiId, $userId, $tanggal) {
            $unit = BarangUnit::where('asset_id', $assetId)->lockForUpdate()->firstOrFail();
            $barang = Barang::lockForUpdate()->findOrFail($unit->barang_id);

            if (! $unit->isAvailable()) {
                throw new RuntimeException('Unit ' . $unit->asset_id . ' sedang tidak tersedia di gudang (status: ' . $unit->statusLabel() . ').');
            }

            $this->applyUnitStatus($unit, $status, $namaPengambil, $user, $catatan, Transaksi::TIPE_KELUAR, $lokasiId, $userId, $tanggal);
            $this->syncUnitStock($barang);

            return $unit->fresh(['barang', 'lokasi']);
        });
    }

    /**
     * Pindah lokasi unit (tanpa mengubah ketersediaan).
     */
    public function moveUnit(
        string $assetId,
        ?int $lokasiId,
        ?string $user = null,
        ?string $catatan = null,
        ?int $userId = null,
        ?CarbonInterface $tanggal = null
    ): BarangUnit {
        return DB::transaction(function () use ($assetId, $lokasiId, $user, $catatan, $userId, $tanggal) {
            $unit = BarangUnit::where('asset_id', $assetId)->lockForUpdate()->firstOrFail();
            $barang = Barang::lockForUpdate()->findOrFail($unit->barang_id);

            $lokasiSebelumId = $unit->lokasi_id;
            $unit->lokasi_id = $lokasiId;
            $unit->save();

            $this->record($barang, [
                'barang_unit_id' => $unit->id,
                'tipe' => Transaksi::TIPE_PINDAH,
                'jumlah' => 1,
                'status_sebelum' => $unit->status,
                'status_sesudah' => $unit->status,
                'lokasi_id' => $lokasiId,
                'lokasi_sebelum_id' => $lokasiSebelumId,
                'user' => $user,
                'user_id' => $userId,
                'catatan' => $catatan,
                'tanggal' => $tanggal,
            ]);

            return $unit->fresh(['barang', 'lokasi']);
        });
    }

    /**
     * Sinkronkan kolom stok jenis barang dengan jumlah unit berstatus tersedia.
     */
    public function syncUnitStock(Barang $barang): void
    {
        $barang->stok = $barang->units()->where('status', BarangUnit::STATUS_TERSEDIA)->count();
        $barang->save();
    }

    /**
     * Sesuaikan stok (koreksi selisih opname). delta positif menambah, negatif mengurangi.
     *
     * - Barang per jumlah: kolom stok disesuaikan.
     * - Barang per unit: kekurangan ditandai unit "hilang" (FIFO), kelebihan dibuatkan unit baru.
     */
    public function applyAdjustment(
        Barang $barang,
        int $delta,
        ?string $user = null,
        ?string $catatan = null,
        ?int $lokasiId = null,
        ?int $userId = null,
        ?CarbonInterface $tanggal = null
    ): void {
        if ($delta === 0) {
            return;
        }

        DB::transaction(function () use ($barang, $delta, $user, $catatan, $lokasiId, $userId, $tanggal) {
            $barang = Barang::lockForUpdate()->findOrFail($barang->id);

            if ($barang->isTrackedPerUnit()) {
                if ($delta < 0) {
                    $units = BarangUnit::where('barang_id', $barang->id)
                        ->where('status', BarangUnit::STATUS_TERSEDIA)
                        ->orderBy('id')
                        ->limit(abs($delta))
                        ->lockForUpdate()
                        ->get();

                    foreach ($units as $unit) {
                        $previous = $unit->status;
                        $unit->status = BarangUnit::STATUS_HILANG;
                        $unit->catatan = $catatan;
                        $unit->save();

                        $this->record($barang, [
                            'barang_unit_id' => $unit->id,
                            'tipe' => Transaksi::TIPE_PENYESUAIAN,
                            'jumlah' => -1,
                            'status_sebelum' => $previous,
                            'status_sesudah' => $unit->status,
                            'lokasi_id' => $unit->lokasi_id,
                            'user' => $user,
                            'user_id' => $userId,
                            'catatan' => $catatan,
                            'tanggal' => $tanggal,
                        ]);
                    }
                } else {
                    for ($i = 0; $i < $delta; $i++) {
                        $unit = BarangUnit::create([
                            'barang_id' => $barang->id,
                            'asset_id' => $this->codes->assetId(),
                            'status' => BarangUnit::STATUS_TERSEDIA,
                            'lokasi_id' => $lokasiId,
                        ]);

                        $this->record($barang, [
                            'barang_unit_id' => $unit->id,
                            'tipe' => Transaksi::TIPE_PENYESUAIAN,
                            'jumlah' => 1,
                            'status_sesudah' => $unit->status,
                            'lokasi_id' => $lokasiId,
                            'user' => $user,
                            'user_id' => $userId,
                            'catatan' => $catatan,
                            'tanggal' => $tanggal,
                        ]);
                    }
                }

                $this->syncUnitStock($barang);
            } else {
                $barang->stok = max(0, $barang->stok + $delta);
                $barang->save();

                $this->record($barang, [
                    'tipe' => Transaksi::TIPE_PENYESUAIAN,
                    'jumlah' => $delta,
                    'lokasi_id' => $lokasiId,
                    'user' => $user,
                    'user_id' => $userId,
                    'catatan' => $catatan,
                    'tanggal' => $tanggal,
                ]);
            }
        });
    }

    /**
     * Batalkan transaksi dengan membuat transaksi pembatalan (reversal) yang seimbang.
     * Transaksi asli tidak dihapus, hanya ditandai voided_at.
     */
    public function reverse(Transaksi $transaksi, string $reason, ?int $userId = null, ?string $user = null): Transaksi
    {
        if (trim($reason) === '') {
            throw new RuntimeException('Alasan pembatalan wajib diisi.');
        }

        return DB::transaction(function () use ($transaksi, $reason, $userId, $user) {
            $trx = Transaksi::lockForUpdate()->findOrFail($transaksi->id);

            if ($trx->isVoided()) {
                throw new RuntimeException('Transaksi ' . $trx->kode_transaksi . ' sudah dibatalkan sebelumnya.');
            }

            if ($trx->isReversal()) {
                throw new RuntimeException('Transaksi pembatalan tidak dapat dibatalkan.');
            }

            $barang = Barang::withTrashed()->lockForUpdate()->findOrFail($trx->barang_id);
            $unit = $trx->barang_unit_id
                ? BarangUnit::lockForUpdate()->find($trx->barang_unit_id)
                : null;

            $this->assertReversible($trx, $unit, $barang);

            $reversalEffect = -$trx->stockEffect();
            $statusBeforeReversal = $unit?->status;

            if ($barang->isTrackedPerUnit()) {
                $this->reverseUnit($trx, $unit, $reversalEffect, $reason);
                $this->syncUnitStock($barang);
            } else {
                $barang->stok = $barang->stok + $reversalEffect;
                if ($barang->stok < 0) {
                    throw new RuntimeException('Tidak dapat membatalkan transaksi: stok akan menjadi negatif.');
                }
                $barang->save();
            }

            $reversal = $this->record($barang, [
                'barang_unit_id' => $trx->barang_unit_id,
                'tipe' => Transaksi::TIPE_PEMBATALAN,
                'jumlah' => $reversalEffect,
                'status_sebelum' => $statusBeforeReversal,
                'status_sesudah' => $unit?->fresh()->status,
                'lokasi_id' => $trx->lokasi_id,
                'lokasi_sebelum_id' => $trx->lokasi_sebelum_id,
                'nama_pengambil' => $trx->nama_pengambil,
                'nama_pemasok' => $trx->nama_pemasok,
                'user' => $user,
                'user_id' => $userId,
                'catatan' => 'Pembatalan ' . $trx->kode_transaksi . ': ' . trim($reason),
                'reversal_of_id' => $trx->id,
                'tanggal' => now(),
            ]);

            $trx->update([
                'voided_at' => now(),
                'voided_by' => $userId,
                'void_reason' => trim($reason),
            ]);

            return $reversal->fresh(['barang', 'unit', 'reversalOf']);
        });
    }

    private function assertReversible(Transaksi $trx, ?BarangUnit $unit, Barang $barang): void
    {
        if ($trx->isReversal()) {
            throw new RuntimeException('Transaksi pembatalan tidak dapat dibatalkan.');
        }

        if ($unit) {
            $later = $unit->transaksis()
                ->where('id', '>', $trx->id)
                ->whereNull('voided_at')
                ->where('tipe', '!=', Transaksi::TIPE_PEMBATALAN)
                ->exists();

            if ($later) {
                throw new RuntimeException('Unit ' . $unit->asset_id . ' sudah memiliki transaksi lain setelah transaksi ini. Batalkan transaksi terbaru terlebih dahulu.');
            }

            if ($trx->tipe === Transaksi::TIPE_PINDAH && is_null($trx->lokasi_sebelum_id)) {
                throw new RuntimeException('Lokasi sebelumnya tidak tercatat, transaksi pindah ini tidak dapat dibatalkan otomatis.');
            }
        } elseif (! $barang->isTrackedPerUnit()) {
            if ($trx->stockEffect() > 0 && $barang->stok < $trx->stockEffect()) {
                throw new RuntimeException('Tidak dapat membatalkan transaksi: stok saat ini tidak mencukupi.');
            }
        }
    }

    private function reverseUnit(Transaksi $trx, ?BarangUnit $unit, int $reversalEffect, string $reason): void
    {
        if (! $unit) {
            throw new RuntimeException('Unit untuk transaksi ini tidak ditemukan.');
        }

        switch ($trx->tipe) {
            case Transaksi::TIPE_MASUK:
                if ($unit->status !== BarangUnit::STATUS_TERSEDIA) {
                    throw new RuntimeException('Unit ' . $unit->asset_id . ' sudah tidak tersedia (status: ' . $unit->statusLabel() . '), tidak dapat membatalkan penerimaan.');
                }
                $unit->status = BarangUnit::STATUS_DIBATALKAN;
                break;

            case Transaksi::TIPE_KELUAR:
                $unit->status = $trx->status_sebelum ?: BarangUnit::STATUS_TERSEDIA;
                if (! is_null($trx->lokasi_sebelum_id)) {
                    $unit->lokasi_id = $trx->lokasi_sebelum_id;
                }
                break;

            case Transaksi::TIPE_KEMBALI:
                $unit->status = $trx->status_sebelum ?: BarangUnit::STATUS_DI_TEKNISI;
                if (! is_null($trx->lokasi_sebelum_id)) {
                    $unit->lokasi_id = $trx->lokasi_sebelum_id;
                }
                break;

            case Transaksi::TIPE_PENYESUAIAN:
                if ($trx->jumlah < 0) {
                    if ($unit->status !== BarangUnit::STATUS_HILANG) {
                        throw new RuntimeException('Unit ' . $unit->asset_id . ' tidak dalam status hilang, tidak dapat membatalkan penyesuaian.');
                    }
                    $unit->status = $trx->status_sebelum ?: BarangUnit::STATUS_TERSEDIA;
                } else {
                    if ($unit->status !== BarangUnit::STATUS_TERSEDIA) {
                        throw new RuntimeException('Unit ' . $unit->asset_id . ' sudah tidak tersedia, tidak dapat membatalkan penyesuaian.');
                    }
                    $unit->status = BarangUnit::STATUS_DIBATALKAN;
                }
                break;

            case Transaksi::TIPE_PINDAH:
                $unit->lokasi_id = $trx->lokasi_sebelum_id;
                break;

            case Transaksi::TIPE_PEMERIKSAAN:
                $unit->status = $trx->status_sebelum ?: $unit->status;
                break;

            default:
                throw new RuntimeException('Tipe transaksi ' . $trx->tipeLabel() . ' tidak dapat dibatalkan.');
        }

        $unit->catatan = trim(($unit->catatan ? $unit->catatan . ' | ' : '') . 'Dibatalkan: ' . trim($reason));
        $unit->save();
    }

    private function applyUnitStatus(
        BarangUnit $unit,
        string $status,
        ?string $namaPengambil,
        ?string $user,
        ?string $catatan,
        string $tipe,
        ?int $lokasiId = null,
        ?int $userId = null,
        ?CarbonInterface $tanggal = null
    ): void {
        $previous = $unit->status;
        $previousLokasi = $unit->lokasi_id;

        $unit->status = $status;
        if (! is_null($lokasiId)) {
            $unit->lokasi_id = $lokasiId;
        }
        $unit->save();

        $this->record($unit->barang, [
            'barang_unit_id' => $unit->id,
            'tipe' => $tipe,
            'jumlah' => 1,
            'status_sebelum' => $previous,
            'status_sesudah' => $status,
            'lokasi_id' => ! is_null($lokasiId) ? $lokasiId : $unit->lokasi_id,
            'lokasi_sebelum_id' => $previousLokasi,
            'nama_pengambil' => $namaPengambil,
            'user' => $user,
            'user_id' => $userId,
            'catatan' => $catatan,
            'tanggal' => $tanggal,
        ]);
    }

    /**
     * @param  array<int, string|null>  $serialNumbers
     */
    private function assertSerialsAvailable(Barang $barang, int $jumlah, array $serialNumbers): void
    {
        $serials = array_values(array_filter(array_map(function ($serial) {
            return is_string($serial) ? trim($serial) : null;
        }, $serialNumbers), fn ($serial) => $serial !== null && $serial !== ''));

        if (count($serials) > $jumlah) {
            throw new RuntimeException('Jumlah serial number (' . count($serials) . ') melebihi jumlah barang (' . $jumlah . ').');
        }

        $duplicates = array_keys(array_filter(array_count_values($serials), fn ($count) => $count > 1));
        if ($duplicates) {
            throw new RuntimeException('Serial number duplikat dalam input: ' . implode(', ', $duplicates) . '.');
        }

        if ($serials) {
            $exists = BarangUnit::where('barang_id', $barang->id)
                ->whereIn('serial_number', $serials)
                ->pluck('serial_number')
                ->all();

            if ($exists) {
                throw new RuntimeException('Serial number sudah terdaftar: ' . implode(', ', $exists) . '.');
            }
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function record(Barang $barang, array $attributes): Transaksi
    {
        $attributes['tanggal'] = $attributes['tanggal'] ?? now();

        return Transaksi::create(array_merge([
            'kode_transaksi' => $this->codes->transactionCode($attributes['tanggal']),
            'barang_id' => $barang->id,
            'user' => $attributes['user'] ?? null,
            'user_id' => $attributes['user_id'] ?? null,
        ], $attributes));
    }
}
