<?php

namespace App\Observers;

use App\Models\Pengukuran;
use App\Services\EwsEngine;
use App\Services\NotificationDispatcher;
use App\Services\ZScoreCalculator;

class PengukuranObserver
{
    public function __construct(
        private ZScoreCalculator     $calculator,
        private EwsEngine            $ewsEngine,
        private NotificationDispatcher $dispatcher,
    ) {}

    /**
     * Dipanggil otomatis setiap kali data pengukuran baru disimpan.
     * Urutan: Hitung Z-score → Jalankan EWS → Kirim notifikasi
     */
    public function created(Pengukuran $pengukuran): void
    {
        // 1. Hitung Z-score dan tentukan status gizi
        $this->calculator->calculate($pengukuran);

        // 2. Jalankan EWS engine, dapatkan daftar peringatan
        $peringatan = $this->ewsEngine->run($pengukuran);

        // 3. Kirim notifikasi ke kader dan petugas
        if (!empty($peringatan)) {
            $posyanduId = $pengukuran->balita->posyandu_id;
            $this->dispatcher->dispatch($peringatan, $posyanduId);
        }
    }
}
