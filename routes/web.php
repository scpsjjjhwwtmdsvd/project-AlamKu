<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlokasiDanaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DonaturController;
use App\Http\Controllers\FotoGeotagController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JenisPohonController;
use App\Http\Controllers\KelompokTaniController;
use App\Http\Controllers\LaporanLapanganController;
use App\Http\Controllers\LokasiHutanController;
use App\Http\Controllers\LogPerubahanController;
use App\Http\Controllers\MitraController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PesananAdopsiController;
use App\Http\Controllers\PohonController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ProyekController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman publik
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/program', [ProgramController::class, 'index'])->name('program.index');
Route::get('/program/{proyek}', [ProgramController::class, 'show'])->name('program.show');

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/

Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
Route::post('/masuk', [AuthController::class, 'login'])->name('login.attempt');
Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
Route::post('/daftar', [AuthController::class, 'register'])->name('register.store');
Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Ruang Donatur (pengguna)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:donatur'])->group(function (): void {
    Route::prefix('akun')->name('akun.')->group(function (): void {
        Route::get('/', [DonaturController::class, 'dashboard'])->name('dashboard');
        Route::get('/adopsi', [DonaturController::class, 'adoptions'])->name('adoptions');
        Route::get('/adopsi/{id}', [DonaturController::class, 'showAdoption'])->whereNumber('id')->name('adoptions.show');
        Route::get('/profil', [DonaturController::class, 'profile'])->name('profile');
        Route::put('/profil', [DonaturController::class, 'updateProfile'])->name('profile.update');
    });

    Route::post('/program/{proyek}/adopsi', [DonaturController::class, 'storeAdoption'])->name('program.adopt');
});

/*
|--------------------------------------------------------------------------
| Ruang Tani (mitra)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:mitra'])->prefix('mitra')->name('mitra.')->group(function (): void {
    Route::get('/', [MitraController::class, 'dashboard'])->name('dashboard');
    Route::get('/proyek', [MitraController::class, 'projects'])->name('projects');
    Route::get('/proyek/{proyek}', [MitraController::class, 'showProject'])->whereNumber('proyek')->name('projects.show');
    Route::put('/proyek/{proyek}/status', [MitraController::class, 'updateProjectStatus'])->whereNumber('proyek')->name('projects.status');
    Route::get('/pohon', [MitraController::class, 'trees'])->name('trees');
    Route::put('/pohon/{pohon}/status', [MitraController::class, 'updateTreeStatus'])->whereNumber('pohon')->name('trees.status');
    Route::get('/laporan', [MitraController::class, 'reports'])->name('reports');
    Route::get('/laporan/baru', [MitraController::class, 'createReport'])->name('reports.create');
    Route::post('/laporan', [MitraController::class, 'storeReport'])->name('reports.store');
    Route::get('/laporan/{laporan}/edit', [MitraController::class, 'editReport'])->whereNumber('laporan')->name('reports.edit');
    Route::put('/laporan/{laporan}', [MitraController::class, 'updateReport'])->whereNumber('laporan')->name('reports.update');
    Route::get('/profil', [MitraController::class, 'profile'])->name('profile');
    Route::put('/profil', [MitraController::class, 'updateProfile'])->name('profile.update');
});

/*
|--------------------------------------------------------------------------
| Konsol Admin
|--------------------------------------------------------------------------
|
| Seluruh resource CRUD tetap memakai nama route lama (proyek.index, dst.)
| agar kontrak data dan view CRUD tidak berubah, tetapi URL-nya dipindah
| ke /admin dan hanya dapat diakses oleh peran admin.
|
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function (): void {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    Route::resource('kelompok-tani', KelompokTaniController::class);
    Route::resource('lokasi-hutan', LokasiHutanController::class);
    Route::resource('jenis-pohon', JenisPohonController::class);
    Route::resource('proyek', ProyekController::class);
    Route::resource('alokasi-dana', AlokasiDanaController::class);
    Route::resource('pesanan-adopsi', PesananAdopsiController::class);
    Route::resource('pohon', PohonController::class);
    Route::resource('pembayaran', PembayaranController::class);
    Route::resource('laporan-lapangan', LaporanLapanganController::class);
    Route::resource('foto-geotag', FotoGeotagController::class);
    Route::resource('log-perubahan', LogPerubahanController::class);
});
