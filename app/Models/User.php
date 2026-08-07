<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'posyandu_id', 'nama', 'email', 'password',
        'role', 'nomor_hp', 'aktif',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'aktif'             => 'boolean',
    ];

    public function posyandu(): BelongsTo
    {
        return $this->belongsTo(Posyandu::class);
    }

    // Untuk Nakes: relasi ke banyak posyandu
    public function posyandus(): BelongsToMany
    {
        return $this->belongsToMany(Posyandu::class, 'user_posyandu');
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class);
    }

    public function notifikasiBelumDibaca(): HasMany
    {
        return $this->hasMany(Notifikasi::class)->where('sudah_dibaca', false);
    }

    // ── Helpers peran ─────────────────────────────────────────

    public function isKader(): bool      { return $this->role === 'kader'; }
    public function isNakes(): bool      { return $this->role === 'nakes'; }
    public function isPetugas(): bool    { return $this->role === 'petugas'; }
    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isDinas(): bool      { return $this->role === 'dinas'; }
    public function isOrangTua(): bool   { return $this->role === 'orang_tua'; }

    public function canInputData(): bool
    {
        return in_array($this->role, ['kader', 'nakes', 'admin']);
    }

    public function canValidate(): bool
    {
        return in_array($this->role, ['nakes', 'petugas', 'admin']);
    }

    // ID Posyandu yang boleh diakses user ini
    public function accessiblePosyanduIds(): array
    {
        if (in_array($this->role, ['admin', 'dinas'])) {
            return Posyandu::pluck('id')->toArray();
        }
        if (in_array($this->role, ['nakes', 'petugas'])) {
            $ids = $this->posyandus()->pluck('posyandu.id')->toArray();
            if (empty($ids) && $this->posyandu_id) {
                return [$this->posyandu_id];
            }
            return $ids;
        }
        // Kader & orang_tua: satu posyandu
        return $this->posyandu_id ? [$this->posyandu_id] : [];
    }

    // Balita yang terhubung ke akun orang_tua ini
    public function balitaOrtu(): HasMany
    {
        return $this->hasMany(Balita::class, 'user_id_ortu');
    }
}
