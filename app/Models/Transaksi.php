<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    public const TIPE_MASUK = 'masuk';
    public const TIPE_KELUAR = 'keluar';
    public const TIPE_KEMBALI = 'kembali';
    public const TIPE_PINDAH = 'pindah';
    public const TIPE_PEMERIKSAAN = 'pemeriksaan';
    public const TIPE_PENYESUAIAN = 'penyesuaian';
    public const TIPE_PEMBATALAN = 'pembatalan';

    protected $fillable = [
        'kode_transaksi',
        'barang_id',
        'barang_unit_id',
        'tipe',
        'jumlah',
        'status_sebelum',
        'status_sesudah',
        'lokasi_id',
        'lokasi_sebelum_id',
        'nama_pengambil',
        'nama_pemasok',
        'user',
        'user_id',
        'catatan',
        'tanggal',
        'voided_at',
        'voided_by',
        'void_reason',
        'reversal_of_id',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'voided_at' => 'datetime',
    ];

    /**
     * @return array<string, string>
     */
    public static function tipeLabels(): array
    {
        return [
            self::TIPE_MASUK => 'Masuk',
            self::TIPE_KELUAR => 'Keluar',
            self::TIPE_KEMBALI => 'Kembali',
            self::TIPE_PINDAH => 'Pindah',
            self::TIPE_PEMERIKSAAN => 'Pemeriksaan',
            self::TIPE_PENYESUAIAN => 'Penyesuaian',
            self::TIPE_PEMBATALAN => 'Pembatalan',
        ];
    }

    public function tipeLabel(): string
    {
        return self::tipeLabels()[$this->tipe] ?? ucfirst($this->tipe);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class)->withTrashed();
    }

    public function unit()
    {
        return $this->belongsTo(BarangUnit::class, 'barang_unit_id');
    }

    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class);
    }

    public function lokasiSebelum()
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_sebelum_id');
    }

    public function userAccount()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function reversalOf()
    {
        return $this->belongsTo(self::class, 'reversal_of_id');
    }

    public function scopeNotVoided($query)
    {
        return $query->whereNull('voided_at');
    }

    public function scopeVoided($query)
    {
        return $query->whereNotNull('voided_at');
    }

    public function isVoided(): bool
    {
        return ! is_null($this->voided_at);
    }

    public function isReversal(): bool
    {
        return $this->tipe === self::TIPE_PEMBATALAN;
    }

    /**
     * Efek transaksi terhadap stok (masuk +, keluar -).
     */
    public function stockEffect(): int
    {
        return match ($this->tipe) {
            self::TIPE_MASUK, self::TIPE_KEMBALI, self::TIPE_PENYESUAIAN, self::TIPE_PEMBATALAN => (int) $this->jumlah,
            self::TIPE_KELUAR => -(int) $this->jumlah,
            default => 0,
        };
    }
}
