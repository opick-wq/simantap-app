<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    public $timestamps = false;

    protected $table = 'notifikasi';

    protected $fillable = [
        'peringatan_id', 'user_id', 'tipe_penerima',
        'sudah_dibaca', 'dibaca_pada', 'created_at',
    ];

    protected $casts = [
        'sudah_dibaca' => 'boolean',
        'dibaca_pada'  => 'datetime',
        'created_at'   => 'datetime',
    ];

    public function peringatan(): BelongsTo
    {
        return $this->belongsTo(Peringatan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
