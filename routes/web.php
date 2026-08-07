<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BalitaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EwsController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\OrangTuaController;
use App\Http\Controllers\PengukuranController;
use App\Http\Controllers\PosyanduController;
use App\Http\Controllers\SesiPosyanduController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\ImportBalitaController;
use App\Http\Controllers\UsulanNonaktifController;
use Illuminate\Support\Facades\Route;

// ── Auth ──────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
     ->middleware('auth')
     ->name('logout');

// ── Portal Orang Tua ──────────────────────────────────────────────────────
Route::middleware(['auth', 'role:orang_tua'])->prefix('ortu')->name('ortu.')->group(function () {
    Route::get('/dashboard', [OrangTuaController::class, 'dashboard'])->name('dashboard');
    Route::get('/anak/{balita}', [OrangTuaController::class, 'showAnak'])->name('anak.show');
    Route::get('/jadwal', [OrangTuaController::class, 'jadwal'])->name('jadwal');
});

// ── Portal Dinas (read-only agregat) ─────────────────────────────────────
Route::middleware(['auth', 'role:dinas,admin'])->prefix('dinas')->name('dinas.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dinas'])->name('dashboard');
});

// ── Dashboard & Manajemen Admin ───────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');

    // Manajemen Wilayah
    Route::get('/wilayah', [WilayahController::class, 'index'])->name('wilayah.index');
    Route::post('/wilayah', [WilayahController::class, 'store'])->name('wilayah.store');
    Route::put('/wilayah/{wilayah}', [WilayahController::class, 'update'])->name('wilayah.update');
    Route::delete('/wilayah/{wilayah}', [WilayahController::class, 'destroy'])->name('wilayah.destroy');
});

// ── Aplikasi Utama (kader, petugas, admin) ────────────────────────────────
Route::middleware(['auth', 'role:kader,nakes,petugas,admin', 'posyandu.access'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Balita
    Route::resource('balita', BalitaController::class)->except(['destroy'])->parameters(['balita' => 'balita']);
    Route::post('/balita/{balita}/buat-akun-ortu', [BalitaController::class, 'buatAkunOrtu'])->name('balita.buat-akun-ortu');
    Route::delete('/balita/{balita}/hapus-akun-ortu', [BalitaController::class, 'hapusAkunOrtu'])->name('balita.hapus-akun-ortu');

    // Import Balita
    Route::get('/balita-import', [ImportBalitaController::class, 'page'])->name('balita.import.page');
    Route::post('/balita-import', [ImportBalitaController::class, 'import'])->name('balita.import');
    Route::get('/balita-import/template', [ImportBalitaController::class, 'template'])->name('balita.import.template');

    // Usulan Nonaktif Balita
    Route::post('/balita/{balita}/usulan-nonaktif', [UsulanNonaktifController::class, 'store'])->name('usulan-nonaktif.store');
    Route::patch('/usulan-nonaktif/{usulan}/nakes', [UsulanNonaktifController::class, 'tindakLanjutNakes'])->name('usulan-nonaktif.nakes');
    Route::patch('/usulan-nonaktif/{usulan}/petugas', [UsulanNonaktifController::class, 'keputusanPetugas'])->name('usulan-nonaktif.petugas');

    // Pengukuran
    Route::get('/pengukuran/create', [PengukuranController::class, 'create'])->name('pengukuran.create');
    Route::post('/pengukuran', [PengukuranController::class, 'store'])->name('pengukuran.store');
    Route::get('/pengukuran/{pengukuran}', [PengukuranController::class, 'show'])->name('pengukuran.show');
    Route::get('/pengukuran/{pengukuran}/edit', [PengukuranController::class, 'edit'])->name('pengukuran.edit');
    Route::patch('/pengukuran/{pengukuran}', [PengukuranController::class, 'update'])->name('pengukuran.update');
    Route::delete('/pengukuran/{pengukuran}', [PengukuranController::class, 'destroy'])->name('pengukuran.destroy');

    // EWS & Peringatan
    Route::get('/peringatan', [EwsController::class, 'index'])->name('peringatan.index');
    Route::get('/peringatan/{peringatan}', [EwsController::class, 'show'])->name('peringatan.show');
    Route::patch('/peringatan/{peringatan}', [EwsController::class, 'updateStatus'])->name('peringatan.update');
    Route::patch('/peringatan/{peringatan}/lapor', [EwsController::class, 'laporKader'])->name('peringatan.lapor');
    Route::patch('/peringatan/{peringatan}/verifikasi', [EwsController::class, 'verifikasi'])->name('peringatan.verifikasi');

    // Sesi Posyandu
    Route::resource('sesi', SesiPosyanduController::class)->only(['index', 'store', 'update', 'destroy']);

    // Laporan
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.pdf');
    Route::get('/laporan/excel', [LaporanController::class, 'exportExcel'])->name('laporan.excel');
    Route::get('/laporan/json', [LaporanController::class, 'exportJson'])->name('laporan.json');
});

// ── Admin & Petugas — Manajemen Posyandu ─────────────────────────────────
Route::middleware(['auth', 'role:admin,petugas'])->group(function () {
    Route::resource('posyandu', PosyanduController::class)->except(['show', 'destroy']);
});

// ── Manajemen Akun User (admin full, petugas buat nakes, nakes buat kader) ─
Route::middleware(['auth', 'role:admin,petugas,nakes'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
});

// Ganti password sendiri (semua role yang login)
Route::middleware(['auth'])->group(function () {
    Route::get('/akun/ganti-password', fn() => inertia('Akun/GantiPassword'))->name('akun.ganti-password.page');
    Route::patch('/akun/ganti-password', [UserController::class, 'gantiPasswordSendiri'])->name('akun.ganti-password');
});

// ── Notifikasi (polling — kader, petugas, admin) ──────────────────────────
Route::middleware(['auth', 'role:kader,nakes,petugas,admin'])->group(function () {
    Route::get('/notif/unread', [NotifikasiController::class, 'unread'])->name('notif.unread');
    Route::patch('/notif/{notifikasi}/baca', [NotifikasiController::class, 'markRead'])->name('notif.baca');
});

// Validasi pengukuran — nakes, petugas, admin
Route::middleware(['auth', 'role:nakes,petugas,admin'])->group(function () {
    Route::patch('/pengukuran/{pengukuran}/validasi', [PengukuranController::class, 'validasi'])->name('pengukuran.validasi');
});
