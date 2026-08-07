<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Posyandu extends Model
{
    protected $table = 'posyandu';

    protected $fillable = [
        'wilayah_id', 'nama', 'alamat', 'jadwal_minggu', 'jadwal_hari',
        'jadwal_jam', 'koordinat_lat', 'koordinat_lng', 'aktif',
    ];

    protected $casts = ['aktif' => 'boolean'];

    public function wilayah(): BelongsTo  { return $this->belongsTo(Wilayah::class); }
    public function kader(): HasMany      { return $this->hasMany(User::class); }
    public function balita(): HasMany     { return $this->hasMany(Balita::class); }
    public function sesi(): HasMany       { return $this->hasMany(SesiPosyandu::class); }

    public function getTotalBalitaAktifAttribute(): int
    {
        return $this->balita()->where('aktif', true)->count();
    }
}
