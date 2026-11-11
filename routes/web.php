<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Situs publik (pelanggan, tanpa akun)
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'home'])->name('home');
Route::get('/menu', [HomeController::class, 'menu'])->name('menu');
Route::get('/promo', [HomeController::class, 'promo'])->name('promo');
Route::get('/galeri', [HomeController::class, 'gallery'])->name('gallery');
Route::get('/kontak', [HomeController::class, 'contact'])->name('contact');
Route::get('/sitemap.xml', [HomeController::class, 'sitemap'])->name('sitemap');

Route::get('/keranjang', [CartController::class, 'index'])->name('cart');
Route::post('/keranjang', [CartController::class, 'add'])->middleware('throttle:60,1')->name('cart.add');
Route::patch('/keranjang', [CartController::class, 'update'])->name('cart.update');
Route::delete('/keranjang/{product}', [CartController::class, 'remove'])->name('cart.remove');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:20,1')->name('checkout.store');
Route::get('/pesanan/{token}', [CheckoutController::class, 'confirmation'])->middleware('throttle:60,1')->name('order.confirmation');

/*
|--------------------------------------------------------------------------
| Login admin
|--------------------------------------------------------------------------
*/
Route::get('/admin/login', [LoginController::class, 'show'])->name('login');
Route::post('/admin/login', [LoginController::class, 'login'])->middleware('throttle:5,1')->name('login.attempt');
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Dashboard admin. Hak akses ditolak secara default dan dibuka per peran.
|   staff        : pesanan, validasi bayar, input di warung, status ketersediaan menu
|   owner        : + kelola menu/promo/galeri/pengaturan/akun staf, laporan penjualan, biaya, QR, log
|   super_admin  : + laba rugi, margin, analitik, kelola semua akun
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'active'])->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/akun', [Admin\UserController::class, 'account'])->name('account');
    Route::put('/akun', [Admin\UserController::class, 'updateAccount'])->name('account.update');

    // Staf, Pemilik, Super Admin
    Route::get('/pesanan', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/poll', [Admin\OrderController::class, 'poll'])->name('orders.poll');
    Route::get('/pesanan/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::get('/pesanan/{order}/struk', [Admin\OrderController::class, 'receipt'])->name('orders.receipt');
    Route::post('/pesanan/{order}/status', [Admin\OrderController::class, 'status'])->name('orders.status');
    Route::post('/pesanan/{order}/pembayaran', [Admin\OrderController::class, 'pay'])->name('orders.pay');

    Route::get('/kasir', [Admin\PosController::class, 'create'])->name('pos');
    Route::post('/kasir', [Admin\PosController::class, 'store'])->name('pos.store');

    Route::get('/menu', [Admin\ProductController::class, 'index'])->name('products.index');
    Route::patch('/menu/{product}/ketersediaan', [Admin\ProductController::class, 'toggle'])->name('products.toggle');

    // Pemilik dan Super Admin
    Route::middleware('role:owner,super_admin')->group(function () {
        Route::post('/pesanan/{order}/pembayaran/batal', [Admin\OrderController::class, 'revertPay'])->name('orders.pay.revert');

        Route::get('/menu/tambah', [Admin\ProductController::class, 'create'])->name('products.create');
        Route::post('/menu', [Admin\ProductController::class, 'store'])->name('products.store');
        Route::get('/menu/{product}/ubah', [Admin\ProductController::class, 'edit'])->name('products.edit');
        Route::put('/menu/{product}', [Admin\ProductController::class, 'update'])->name('products.update');
        Route::delete('/menu/{product}', [Admin\ProductController::class, 'destroy'])->name('products.destroy');

        Route::get('/kategori', [Admin\CategoryController::class, 'index'])->name('categories.index');
        Route::post('/kategori', [Admin\CategoryController::class, 'store'])->name('categories.store');
        Route::put('/kategori/{category}', [Admin\CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/kategori/{category}', [Admin\CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/promo', [Admin\PromoController::class, 'index'])->name('promos.index');
        Route::get('/promo/tambah', [Admin\PromoController::class, 'create'])->name('promos.create');
        Route::post('/promo', [Admin\PromoController::class, 'store'])->name('promos.store');
        Route::get('/promo/{promo}/ubah', [Admin\PromoController::class, 'edit'])->name('promos.edit');
        Route::put('/promo/{promo}', [Admin\PromoController::class, 'update'])->name('promos.update');
        Route::delete('/promo/{promo}', [Admin\PromoController::class, 'destroy'])->name('promos.destroy');

        Route::get('/galeri', [Admin\GalleryController::class, 'index'])->name('gallery.index');
        Route::post('/galeri', [Admin\GalleryController::class, 'store'])->name('gallery.store');
        Route::put('/galeri/{photo}', [Admin\GalleryController::class, 'update'])->name('gallery.update');
        Route::delete('/galeri/{photo}', [Admin\GalleryController::class, 'destroy'])->name('gallery.destroy');

        Route::get('/pengaturan', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/pengaturan', [Admin\SettingController::class, 'update'])->name('settings.update');

        Route::get('/pengguna', [Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/pengguna/tambah', [Admin\UserController::class, 'create'])->name('users.create');
        Route::post('/pengguna', [Admin\UserController::class, 'store'])->name('users.store');
        Route::get('/pengguna/{user}/ubah', [Admin\UserController::class, 'edit'])->name('users.edit');
        Route::put('/pengguna/{user}', [Admin\UserController::class, 'update'])->name('users.update');

        Route::get('/biaya', [Admin\ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/biaya', [Admin\ExpenseController::class, 'store'])->name('expenses.store');
        Route::delete('/biaya/{expense}', [Admin\ExpenseController::class, 'destroy'])->name('expenses.destroy');

        Route::get('/laporan', [Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('/laporan/cetak', [Admin\ReportController::class, 'print'])->name('reports.print');
        Route::get('/laporan/ekspor', [Admin\ReportController::class, 'export'])->name('reports.export');

        Route::get('/qr', [Admin\QrController::class, 'show'])->name('qr');
        Route::get('/log', [Admin\AuditLogController::class, 'index'])->name('audit');
    });

    // Super Admin saja (si bos/manajer)
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/analitik', [Admin\AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/analitik/cetak', [Admin\AnalyticsController::class, 'print'])->name('analytics.print');
        Route::get('/analitik/ekspor', [Admin\AnalyticsController::class, 'export'])->name('analytics.export');
    });
});
