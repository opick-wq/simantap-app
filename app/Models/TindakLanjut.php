<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TindakLanjut extends Model
{
    protected $table = 'tindak_lanjut';

    protected $fillable = [
        'peringatan_id', 'balita_id', 'dicatat_oleh',
        'jenis_tindakan', 'dilaporkan_ke_atasan', 'catatan', 'status_akhir',
    ];

    protected $casts = [
        'jenis_tindakan'       => 'array',
        'dilaporkan_ke_atasan' => 'boolean',
    ];

    public function peringatan(): BelongsTo { return $this->belongsTo(Peringatan::class); }
    public function balita(): BelongsTo     { return $this->belongsTo(Balita::class, 'balita_id'); }
    public function pencatat(): BelongsTo   { return $this->belongsTo(User::class, 'dicatat_oleh'); }
}
