<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangUnitController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\OpnameController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StokBarangController;
use App\Http\Controllers\StokKeluarController;
use App\Http\Controllers\StokMasukController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/sign-in', [AuthController::class, 'showLogin'])->name('login');
Route::post('/sign-in', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/sign-up', [AuthController::class, 'showRegister'])->name('register');
Route::post('/sign-up', [AuthController::class, 'register'])->middleware('guest');
Route::get('/forgot-password', fn () => view('forgot-password'));
Route::get('/reset-password', fn () => view('reset-password'));
Route::get('/confirm-email', fn () => view('confirm-email'));
Route::get('/lock-screen', fn () => view('lock-screen'));

Route::prefix('dashboard')->middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('stok-barang.index'));

    Route::get('/stok-barang', [StokBarangController::class, 'index'])->name('stok-barang.index');
    Route::middleware('role:admin')->group(function () {
        Route::post('/stok-barang', [StokBarangController::class, 'store'])->name('stok-barang.store');
        Route::put('/stok-barang/{barang}', [StokBarangController::class, 'update'])->name('stok-barang.update');
        Route::delete('/stok-barang/{barang}', [StokBarangController::class, 'destroy'])->name('stok-barang.destroy');
    });

    Route::get('/stok-masuk', [StokMasukController::class, 'index'])->name('stok-masuk.index');
    Route::post('/stok-masuk', [StokMasukController::class, 'store'])->name('stok-masuk.store');
    Route::get('/stok-keluar', [StokKeluarController::class, 'index'])->name('stok-keluar.index');
    Route::post('/stok-keluar', [StokKeluarController::class, 'store'])->name('stok-keluar.store');
    Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');
    Route::post('/riwayat/{transaksi}/void', [RiwayatController::class, 'void'])
        ->name('riwayat.void')
        ->middleware('role:admin');

    Route::get('/search/suggest', [SearchController::class, 'suggest'])->name('search.suggest');
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');

    Route::get('/opname', [OpnameController::class, 'index'])->name('opname.index');
    Route::get('/opname/{opname}', [OpnameController::class, 'show'])->name('opname.show');
    Route::put('/opname/{opname}/items', [OpnameController::class, 'updateItems'])->name('opname.items');
    Route::get('/opname/{opname}/export', [OpnameController::class, 'export'])->name('opname.export');
    Route::get('/opname/{opname}/print', [OpnameController::class, 'print'])->name('opname.print');
    Route::middleware('role:admin')->group(function () {
        Route::post('/opname', [OpnameController::class, 'store'])->name('opname.store');
        Route::post('/opname/{opname}/finalize', [OpnameController::class, 'finalize'])->name('opname.finalize');
        Route::delete('/opname/{opname}', [OpnameController::class, 'destroy'])->name('opname.destroy');
    });

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
    Route::get('/laporan/print', [LaporanController::class, 'print'])->name('laporan.print');

    Route::get('/scan-barcode', [StokBarangController::class, 'scan'])->name('scan-barcode.index');
    Route::post('/scan-barcode/process', [StokBarangController::class, 'prosesScan'])->name('scan-barcode.process');
    Route::post('/scan-barcode/store', [StokBarangController::class, 'simpanScan'])->name('scan-barcode.store');

    Route::get('/barang-units', [BarangUnitController::class, 'index'])->name('barang-units.index');
    Route::get('/barang-units/labels', [BarangUnitController::class, 'labels'])->name('barang-units.labels');
    Route::middleware('role:admin')->group(function () {
        Route::put('/barang-units/{unit}', [BarangUnitController::class, 'update'])->name('barang-units.update');
        Route::post('/lokasi', [LokasiController::class, 'store'])->name('lokasi.store');
        Route::delete('/lokasi/{lokasi}', [LokasiController::class, 'destroy'])->name('lokasi.destroy');
    });
});
