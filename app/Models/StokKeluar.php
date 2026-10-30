<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokKeluar extends Model
{
    use HasFactory;

    protected $fillable = [
        'barang_id',
        'kode_barang',
        'nama_barang',
        'jumlah',
        'user',
        'nama_pengambil',
        'tanggal_keluar',
    ];

    protected $casts = [
        'tanggal_keluar' => 'datetime',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
