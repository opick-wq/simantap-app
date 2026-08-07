<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsulanNonaktif extends Model
{
    protected $table = 'usulan_nonaktif';

    protected $fillable = [
        'balita_id', 'pengusul_id', 'alasan', 'status',
        'nakes_id', 'catatan_nakes',
        'petugas_id', 'catatan_petugas', 'tindakan_akhir',
    ];

    public function balita(): BelongsTo   { return $this->belongsTo(Balita::class); }
    public function pengusul(): BelongsTo { return $this->belongsTo(User::class, 'pengusul_id'); }
    public function nakes(): BelongsTo    { return $this->belongsTo(User::class, 'nakes_id'); }
    public function petugas(): BelongsTo  { return $this->belongsTo(User::class, 'petugas_id'); }

    public function labelStatus(): string
    {
        return match($this->status) {
            'DIUSULKAN'  => 'Menunggu Nakes',
            'DITERUSKAN' => 'Menunggu Petugas Puskesmas',
            'DISETUJUI'  => 'Disetujui',
            'DITOLAK'    => 'Ditolak',
            default      => $this->status,
        };
    }
}
