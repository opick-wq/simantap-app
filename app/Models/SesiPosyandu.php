<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SesiPosyandu extends Model
{
    protected $table = 'sesi_posyandu';

    protected $fillable = [
        'posyandu_id', 'dipimpin_oleh', 'dibuat_oleh', 'tanggal',
        'tema', 'status', 'jumlah_hadir', 'catatan', 'selesai',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'selesai' => 'boolean',
    ];

    public function posyandu(): BelongsTo   { return $this->belongsTo(Posyandu::class); }
    public function pemimpin(): BelongsTo   { return $this->belongsTo(User::class, 'dipimpin_oleh'); }
    public function pengukuran(): HasMany   { return $this->hasMany(Pengukuran::class, 'sesi_id'); }
}
