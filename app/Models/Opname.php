<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opname extends Model
{
    use HasFactory;

    public const STATUS_PROSES = 'proses';
    public const STATUS_SELESAI = 'selesai';

    protected $fillable = [
        'kode_opname',
        'tanggal',
        'lokasi_id',
        'status',
        'user',
        'finalized_at',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'finalized_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OpnameItem::class);
    }

    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class);
    }

    public function isSelesai(): bool
    {
        return $this->status === self::STATUS_SELESAI;
    }
}
