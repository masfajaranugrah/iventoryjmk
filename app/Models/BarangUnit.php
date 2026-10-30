<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangUnit extends Model
{
    use HasFactory;

    public const STATUS_TERSEDIA = 'tersedia';
    public const STATUS_DI_TEKNISI = 'di_teknisi';
    public const STATUS_TERPASANG = 'terpasang';
    public const STATUS_MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    public const STATUS_PERLU_PEMERIKSAAN = 'perlu_pemeriksaan';
    public const STATUS_RUSAK = 'rusak';
    public const STATUS_HILANG = 'hilang';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    protected $fillable = [
        'barang_id',
        'asset_id',
        'serial_number',
        'status',
        'lokasi_id',
        'catatan',
    ];

    /**
     * Daftar status unit dengan label Indonesia.
     *
     * @return array<string, string>
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_TERSEDIA => 'Tersedia',
            self::STATUS_DI_TEKNISI => 'Di Teknisi',
            self::STATUS_TERPASANG => 'Terpasang',
            self::STATUS_MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
            self::STATUS_PERLU_PEMERIKSAAN => 'Perlu Pemeriksaan',
            self::STATUS_RUSAK => 'Rusak',
            self::STATUS_HILANG => 'Hilang',
            self::STATUS_DIBATALKAN => 'Dibatalkan',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? ucfirst($this->status);
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_TERSEDIA;
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class)->withTrashed();
    }

    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class);
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class);
    }
}
