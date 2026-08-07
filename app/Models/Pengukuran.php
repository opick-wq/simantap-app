<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Pengukuran extends Model
{
    protected $table = 'pengukuran';

    protected $fillable = [
        'balita_id', 'sesi_id', 'dicatat_oleh', 'tanggal_ukur',
        'umur_bulan', 'umur_bulan_adjusted',
        'berat_badan_kg', 'kenaikan_bb_gram', 'status_kbb', 'tinggi_badan_cm', 'posisi_ukur',
        'lingkar_lengan_atas_cm', 'lingkar_kepala_cm',
        'imt_u', 'z_score_bb_u', 'z_score_tb_u', 'z_score_bb_tb', 'z_score_imt_u',
        'status_gizi', 'status_stunting', 'status_wasting', 'status_imt_u',
        'flag_ews', 'catatan',
        'is_weight_faltering',
        'is_validated', 'validated_by', 'validated_at', 'catatan_validasi',
    ];

    protected $casts = [
        'tanggal_ukur'   => 'date',
        'berat_badan_kg' => 'float',
        'tinggi_badan_cm'=> 'float',
        'kenaikan_bb_gram' => 'float',
        'imt_u'            => 'float',
        'z_score_bb_u'   => 'float',
        'z_score_tb_u'   => 'float',
        'z_score_bb_tb'  => 'float',
        'z_score_imt_u'  => 'float',
        'is_weight_faltering' => 'boolean',
        'is_validated'   => 'boolean',
        'validated_at'   => 'datetime',
    ];

    protected $appends = [
        'warn_ews',
        'label_status_gizi',
        'label_stunting',
        'label_wasting',
        'label_kbb',
        'label_imt_u',
    ];

    // ── Booted Method (Otomatisasi Kalkulasi) ─────────────────
    protected static function booted()
    {
        static::saving(function ($pengukuran) {
            // Pastikan ada referensi balita dan tanggal ukur
            if ($pengukuran->balita_id && $pengukuran->tanggal_ukur) {
                $balita = Balita::find($pengukuran->balita_id);
                
                if ($balita && $balita->tanggal_lahir) {
                    $tglLahir = Carbon::parse($balita->tanggal_lahir);
                    $tglUkur = Carbon::parse($pengukuran->tanggal_ukur);
                    
                    // 1. Hitung Umur Bulan (Selisih bulan lahir dan ukur)
                    $umurBulan = $tglLahir->diffInMonths($tglUkur);
                    $pengukuran->umur_bulan = $umurBulan;
                    
                    // 2. Hitung Umur Adjusted jika Prematur (Koreksi)
                    // Standar aterm adalah 40 minggu (1 bulan ~ 4 minggu)
                    if ($balita->prematur === 'Y' && $balita->usia_gestasi) {
                        $koreksiMinggu = 40 - (int) $balita->usia_gestasi;
                        $koreksiBulan = $koreksiMinggu / 4;
                        // Pastikan umur tidak minus
                        $pengukuran->umur_bulan_adjusted = max(0, $umurBulan - $koreksiBulan);
                    } else {
                        $pengukuran->umur_bulan_adjusted = $umurBulan;
                    }

                    // 3. (Opsional) Panggil Service Z-Score Anda di Sini
                    // Jika Anda punya service untuk menghitung stunting/wasting, panggil di sini
                    // Contoh:
                    // $hasilGizi = AntropometriService::hitung($pengukuran->umur_bulan, $pengukuran->berat_badan_kg, $pengukuran->tinggi_badan_cm, $balita->jenis_kelamin);
                    // $pengukuran->status_stunting = $hasilGizi['status_stunting'];
                    // $pengukuran->z_score_bb_u = $hasilGizi['z_score_bb_u'];
                }
            }
        });
    }

    // ── Relasi ────────────────────────────────────────────────

    public function balita(): BelongsTo
    {
        return $this->belongsTo(Balita::class);
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(SesiPosyandu::class, 'sesi_id');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function peringatan(): HasMany
    {
        return $this->hasMany(Peringatan::class);
    }

    // ── Accessor ──────────────────────────────────────────────

    public function getWarnEwsAttribute(): string
    {
        return match($this->flag_ews) {
            'MERAH'  => 'text-red-600 bg-red-50',
            'KUNING' => 'text-yellow-600 bg-yellow-50',
            default  => 'text-green-600 bg-green-50',
        };
    }

    public function getLabelStatusGiziAttribute(): string
    {
        return match($this->status_gizi) {
            'GIZI_BURUK'   => 'Berat Badan Sangat Kurang',
            'GIZI_KURANG'  => 'Berat Badan Kurang',
            'GIZI_BAIK'    => 'Berat Badan Normal',
            'RISIKO_LEBIH' => 'Risiko Berat Badan Lebih',
            default        => '-',
        };
    }

    public function getLabelStuntingAttribute(): string
    {
        return match($this->status_stunting) {
            'SANGAT_PENDEK' => 'Sangat Pendek',
            'PENDEK'        => 'Pendek (Stunted)',
            'NORMAL'        => 'Normal',
            'TINGGI'        => 'Tinggi',
            default         => '-',
        };
    }

    public function getLabelWastingAttribute(): string
    {
        return match($this->status_wasting) {
            'SANGAT_KURUS'   => 'Sangat Kurus',
            'KURUS'          => 'Kurus',
            'NORMAL'         => 'Normal',
            'BERISIKO_GEMUK' => 'Berisiko Gemuk',
            'GEMUK'          => 'Gemuk',
            'OBESITAS'       => 'Obesitas',
            default          => '-',
        };
    }

    public function getLabelKbbAttribute(): string
    {
        return match($this->status_kbb) {
            'N' => 'Naik Cukup',
            'T' => 'Naik Kurang',
            'O' => 'Tidak Naik / Turun',
            default => '—',
        };
    }

    public function getLabelImtUAttribute(): string
    {
        return match($this->status_imt_u) {
            'SANGAT_KURUS'   => 'Gizi Buruk',
            'KURUS'          => 'Gizi Kurang',
            'NORMAL'         => 'Gizi Baik',
            'BERISIKO_GEMUK' => 'Berisiko Gizi Lebih',
            'GEMUK'          => 'Gizi Lebih',
            'OBESITAS'       => 'Obesitas',
            default          => '-',
        };
    }
}