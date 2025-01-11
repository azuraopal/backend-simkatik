<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\PembelianController;
use App\Http\Controllers\Api\PenjualanController;
use App\Http\Controllers\Api\ProdukController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    
    Route::prefix('users')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
        Route::get('produk/print-pdf', [ProdukController::class, 'printPdf']);
        Route::get('penjualan/print-pdf', [PenjualanController::class, 'printPdf']);
        Route::get('pembelian/print-pdf', [PembelianController::class, 'printPdf']);

        Route::middleware('auth:sanctum')->group(function () {

            // Routes Auth
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/update-profile-picture', [AuthController::class, 'updateProfilePicture']);
            Route::post('/verify-old-password-v2', [AuthController::class, 'verifyOldPasswordV2']);
            Route::post('/reset-password-v2', [AuthController::class, 'resetPasswordV2'])
                ->middleware(['password.verified']);
            Route::get('/profile', [AuthController::class, 'getProfile']);

            // Routes Statistik Dashboard
            Route::get('/laba-bulanan', action: [DashboardController::class, 'getLabaBersihBulanan']);
            Route::get('/laba-tahunan', [DashboardController::class, 'getLabaBersihTahunan']);
            Route::get('/total-produk', [DashboardController::class, 'getTotalProduk']);
            Route::get('/total-terjual', [DashboardController::class, 'getTotalProdukTerjual']);
            Route::get('/all', [DashboardController::class, 'getSemuaStatistik']);

            // Routes kategori
            Route::get('kategori', [KategoriController::class, 'index']);
            Route::post('kategori', [KategoriController::class, 'store']);
            Route::put('kategori/{id}', [KategoriController::class, 'update']);
            Route::delete('kategori/{id}', [KategoriController::class, 'destroy']);

            // Routes produk
            Route::get('produk', [ProdukController::class, 'index']);
            Route::post('produk', [ProdukController::class, 'store']);
            Route::put('produk/{id}', [ProdukController::class, 'update']);
            Route::delete('produk/{id}', [ProdukController::class, 'destroy']);
            Route::put('produk/{id}/tambah-stok', [ProdukController::class, 'tambahStok']);

            // Routes Penjualan
            Route::get('penjualan', [PenjualanController::class, 'index']);
            Route::post('penjualan', [PenjualanController::class, 'store']);

            // Routes Pembelian
            Route::get('pembelian', [PembelianController::class, 'index']);
        });
    });
});