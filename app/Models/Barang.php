<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barang extends Model
{
    use HasFactory, SoftDeletes;

    public const TRACK_UNIT = 'unit';
    public const TRACK_QTY = 'jumlah';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'kategori',
        'merek',
        'model',
        'stok',
        'satuan',
        'tipe_pelacakan',
    ];

    public function stokKeluars()
    {
        return $this->hasMany(StokKeluar::class);
    }

    public function stokMasuks()
    {
        return $this->hasMany(StokMasuk::class);
    }

    public function units()
    {
        return $this->hasMany(BarangUnit::class);
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class);
    }

    public function isTrackedPerUnit(): bool
    {
        return $this->tipe_pelacakan === self::TRACK_UNIT;
    }

    public function availableUnitsCount(): int
    {
        return $this->units()->where('status', BarangUnit::STATUS_TERSEDIA)->count();
    }
}
