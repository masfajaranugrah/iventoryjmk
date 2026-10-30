<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpnameItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'opname_id',
        'barang_id',
        'stok_sistem',
        'stok_fisik',
        'selisih',
        'catatan',
    ];

    protected $casts = [
        'stok_sistem' => 'integer',
        'stok_fisik' => 'integer',
        'selisih' => 'integer',
    ];

    public function opname()
    {
        return $this->belongsTo(Opname::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class)->withTrashed();
    }
}
