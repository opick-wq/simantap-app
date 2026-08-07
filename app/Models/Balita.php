<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Balita extends Model
{
    protected $table = 'balita';

    protected $fillable = [
        'posyandu_id', 'nik_balita', 'nama', 'tanggal_lahir', 'jenis_kelamin',
        'nik_ibu', 'nama_ibu', 'nomor_hp_ibu', 'nik_ayah', 'nama_ayah',
        'alamat', 'rt_rw', 'berat_lahir_gram', 'panjang_lahir_cm',
        'prematur', 'usia_gestasi_minggu', 'user_id_ortu', 'aktif',
        'tanggal_nonaktif', 'alasan_nonaktif',
    ];

    protected $appends = ['umur_bulan', 'umur_lengkap'];

    protected $casts = [
        'tanggal_lahir'    => 'date',
        'tanggal_nonaktif' => 'date',
        'prematur'         => 'boolean',
        'aktif'            => 'boolean',
    ];

    // ── Relasi ────────────────────────────────────────────────

    public function posyandu(): BelongsTo
    {
        return $this->belongsTo(Posyandu::class);
    }

    public function orangTua(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id_ortu');
    }

    public function pengukuran(): HasMany
    {
        return $this->hasMany(Pengukuran::class)->orderBy('tanggal_ukur', 'desc');
    }

    public function peringatan(): HasMany
    {
        return $this->hasMany(Peringatan::class)->orderBy('created_at', 'desc');
    }

    public function tindakLanjut(): HasMany
    {
        return $this->hasMany(\App\Models\TindakLanjut::class)->orderBy('created_at', 'desc');
    }

    public function usulanNonaktif(): HasMany
    {
        return $this->hasMany(UsulanNonaktif::class)->orderBy('created_at', 'desc');
    }

    // ── Accessor ──────────────────────────────────────────────

    public function getUmurBulanAttribute(): int
    {
        return (int) $this->tanggal_lahir->diffInMonths(now());
    }

    public function getUmurLengkapAttribute(): string
    {
        $bulan = $this->umur_bulan;
        if ($bulan < 1)  return '< 1 bulan';
        if ($bulan < 12) return "{$bulan} bulan";
        $tahun = intdiv($bulan, 12);
        $sisa  = $bulan % 12;
        return $sisa > 0 ? "{$tahun} tahun {$sisa} bulan" : "{$tahun} tahun";
    }

    public function getPengukuranTerakhirAttribute(): ?Pengukuran
    {
        return $this->pengukuran()->first();
    }

    public function getPeringatanAktifAttribute(): int
    {
        return $this->peringatan()->where('status_tindak_lanjut', 'BELUM')->count();
    }

    // ── Scope ─────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('nama', 'like', "%{$term}%")
              ->orWhere('nik_balita', 'like', "%{$term}%")
              ->orWhere('nama_ibu', 'like', "%{$term}%");
        });
    }

    public function scopeDiPosyandu($query, int $posyanduId)
    {
        return $query->where('posyandu_id', $posyanduId);
    }
}
