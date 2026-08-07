<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wilayah extends Model
{
    protected $table = 'wilayah';
    protected $fillable = ['nama', 'kecamatan', 'kabupaten', 'provinsi', 'kode_bps'];

    public function posyandu(): HasMany
    {
        return $this->hasMany(Posyandu::class);
    }
}
