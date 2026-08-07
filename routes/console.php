<?php

use Illuminate\Support\Facades\Schedule;

// Nonaktifkan balita yang sudah ≥ 61 bulan — jalankan setiap hari tengah malam
Schedule::command('balita:nonaktifkan-lulus')->dailyAt('00:05');
