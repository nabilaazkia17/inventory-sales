<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect('/dashboard') : redirect('/login'));

Route::get('/login', [AuthController::class, 'form'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Semua role yang sudah login (admin, staff, user) boleh MELIHAT daftar data master
    Route::resource('categories', CategoryController::class)->only('index');
    Route::resource('products', ProductController::class)->only('index');
    Route::resource('suppliers', SupplierController::class)->only('index');

    // Detail produk dan daftar stok rendah (whereNumber supaya /products/create tidak tertangkap sebagai {product})
    Route::get('/products/{product}', [ProductController::class, 'show'])->whereNumber('product')->name('products.show');
    Route::get('/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');

    // Admin dan staff boleh membuat & melihat transaksi pembelian / penjualan
    Route::middleware('role:admin,staff')->group(function () {
        Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
        Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
    });

    // Hanya ADMIN: kelola data master, kelola user, dan membatalkan transaksi
    Route::middleware('role:admin')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['index', 'show']);
        Route::resource('products', ProductController::class)->except(['index', 'show']);
        Route::resource('suppliers', SupplierController::class)->except(['index', 'show']);
        Route::resource('users', UserController::class)->except('show');

        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');

        Route::post('purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
        Route::post('sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
    });
});
