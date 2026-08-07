<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Peringatan extends Model
{
    protected $table = 'peringatan';

    protected $fillable = [
        'pengukuran_id', 'balita_id', 'jenis_peringatan',
        'level_risiko', 'pesan', 'status_tindak_lanjut',
        // Kader
        'sudah_verifikasi_ulang', 'sudah_eskalasi_nakes', 'catatan_kader',
        'dilaporkan_oleh', 'dilaporkan_pada',
        // Nakes/Petugas
        'jenis_tindakan', 'dilaporkan_ke_atasan', 'catatan_petugas',
        'ditindaklanjuti_oleh', 'ditindaklanjuti_pada',
        // Verifikasi
        'status_verifikasi', 'diverifikasi_oleh', 'diverifikasi_pada', 'catatan_verifikasi',
    ];

    protected $casts = [
        'ditindaklanjuti_pada'   => 'datetime',
        'dilaporkan_pada'        => 'datetime',
        'diverifikasi_pada'      => 'datetime',
        'jenis_tindakan'         => 'array',
        'dilaporkan_ke_atasan'   => 'boolean',
        'sudah_verifikasi_ulang' => 'boolean',
        'sudah_eskalasi_nakes'   => 'boolean',
    ];

    public function pengukuran(): BelongsTo
    {
        return $this->belongsTo(Pengukuran::class);
    }

    public function balita(): BelongsTo
    {
        return $this->belongsTo(Balita::class);
    }

    public function penanggungjawab(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditindaklanjuti_oleh');
    }

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dilaporkan_oleh');
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class);
    }

    public function tindakLanjut(): HasMany
    {
        return $this->hasMany(TindakLanjut::class);
    }

    public function getWarnLevelAttribute(): string
    {
        return $this->level_risiko === 'MERAH'
            ? 'border-red-400 bg-red-50'
            : 'border-yellow-400 bg-yellow-50';
    }

    public function getLabelJenisAttribute(): string
    {
        return match($this->jenis_peringatan) {
            'GIZI_BURUK'        => 'Gizi Buruk',
            'GIZI_KURANG'       => 'Gizi Kurang',
            'RISIKO_GIZI'       => 'Berisiko Gizi Kurang',
            'SANGAT_PENDEK'     => 'Sangat Pendek (Severe Stunting)',
            'PENDEK_STUNTED'    => 'Pendek (Stunting)',
            'RISIKO_PENDEK'     => 'Berisiko Pendek',
            'WEIGHT_LOSS'       => 'Penurunan Berat Badan',
            'WEIGHT_STAGNATION' => 'Stagnasi Berat Badan',
            'ZSCORE_DROP'       => 'Penurunan Z-Score Signifikan',
            'ZSCORE_DROP_MILD'  => 'Penurunan Z-Score Ringan',
            'ZSCORE_DROP_PROG'  => 'Penurunan Z-Score Progresif',
            'ABSEN_2BULAN'      => 'Tidak Hadir 2 Bulan',
            'ABSEN_LAMA'        => 'Tidak Hadir 3+ Bulan',
            default             => $this->jenis_peringatan,
        };
    }

    public function scopeBelum($query)
    {
        return $query->whereIn('status_tindak_lanjut', ['BELUM', 'DILAPORKAN']);
    }

    public function scopeMerah($query)
    {
        return $query->where('level_risiko', 'MERAH');
    }
}
