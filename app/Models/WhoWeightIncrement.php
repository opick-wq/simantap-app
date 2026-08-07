<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhoWeightIncrement extends Model
{
    public $timestamps = false;
    protected $table = 'who_weight_increment';

    /**
     * Lookup nilai min_gram berdasarkan jenis kelamin, interval aktual, dan usia awal.
     * Interval aktual dibulatkan ke interval tabel terdekat (3, 4, atau 6 bulan).
     */
    public static function lookup(string $gender, int $intervalAktual, int $usiaAwal): ?self
    {
        // Pilih interval tabel terdekat
        $interval = match (true) {
            $intervalAktual <= 3  => 3,
            $intervalAktual <= 5  => 4,
            default               => 6,
        };

        // Usia awal dibatasi sesuai cakupan tabel masing-masing
        $maxUsia = match ($interval) {
            3 => 21,
            4 => 20,
            6 => 18,
        };

        $usia = min($usiaAwal, $maxUsia);

        return self::where('jenis_kelamin', $gender)
            ->where('interval_bulan', $interval)
            ->where('usia_awal', $usia)
            ->first();
    }
}
