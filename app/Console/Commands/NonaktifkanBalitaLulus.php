<?php

namespace App\Console\Commands;

use App\Models\Balita;
use Illuminate\Console\Command;

class NonaktifkanBalitaLulus extends Command
{
    protected $signature   = 'balita:nonaktifkan-lulus';
    protected $description = 'Nonaktifkan balita yang sudah mencapai 61 bulan';

    public function handle(): int
    {
        // Balita ≥ 61 bulan: tanggal_lahir ≤ hari ini minus 61 bulan
        $batas = now()->subMonths(61)->toDateString();

        $jumlah = Balita::where('aktif', true)
            ->where('tanggal_lahir', '<=', $batas)
            ->update(['aktif' => false]);

        $this->info("$jumlah balita dinonaktifkan (usia ≥ 61 bulan).");

        return Command::SUCCESS;
    }
}
