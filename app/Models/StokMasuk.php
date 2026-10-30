<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokMasuk extends Model
{
    use HasFactory;

    protected $fillable = [
        'barang_id',
        'kode_barang',
        'nama_barang',
        'jumlah',
        'user',
        'nama_pemasok',
        'keterangan',
        'tanggal_masuk',
    ];

    protected $casts = [
        'tanggal_masuk' => 'datetime',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
