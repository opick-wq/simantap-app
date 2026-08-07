<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhoZscoreReference extends Model
{
    protected $table = 'who_zscore_reference';
    public $timestamps = false;

    protected $fillable = [
        'jenis_kelamin', 'parameter', 'indikator',
        'l_value', 'm_value', 's_value',
        'sd_minus3', 'sd_minus2', 'sd_minus1',
        'median_val', 'sd_plus1', 'sd_plus2', 'sd_plus3',
    ];

    protected $casts = [
        'parameter'  => 'float',
        'l_value'    => 'float',
        'm_value'    => 'float',
        's_value'    => 'float',
        'sd_minus3'  => 'float',
        'sd_minus2'  => 'float',
        'sd_minus1'  => 'float',
        'median_val' => 'float',
        'sd_plus1'   => 'float',
        'sd_plus2'   => 'float',
        'sd_plus3'   => 'float',
    ];

    public static function lookup(string $indikator, string $gender, float $param): ?self
    {
        return static::where('indikator', $indikator)
                     ->where('jenis_kelamin', $gender)
                     ->where('parameter', $param)
                     ->first();
    }

    // Ambil semua nilai SD untuk grafik kurva pertumbuhan
    public static function getCurveData(string $indikator, string $gender): array
    {
        return static::where('indikator', $indikator)
                     ->where('jenis_kelamin', $gender)
                     ->orderBy('parameter')
                     ->get(['parameter', 'sd_minus3', 'sd_minus2', 'sd_minus1',
                            'median_val', 'sd_plus1', 'sd_plus2', 'sd_plus3'])
                     ->toArray();
    }
}
