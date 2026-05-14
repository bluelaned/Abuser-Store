<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CallbackController;
use App\Http\Controllers\DiscordAuthController;
use App\Http\Controllers\AdminUserController;

// --- HALAMAN PUBLIK ---
Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/checkout/{id}', [ProductController::class, 'checkout'])->name('checkout');
Route::view('/terms', 'tos')->name('tos');
Route::view('/privacy', 'privacy')->name('privacy');
Route::get('/reviews', [App\Http\Controllers\ReviewController::class, 'publicIndex'])->name('reviews.index');

Route::post('/checkout/check-promo', [PaymentController::class, 'checkPromo'])->name('payment.check_promo');
Route::post('/checkout/process', [PaymentController::class, 'process'])->name('payment.process');
Route::post('/payment/stripe', [TransactionController::class, 'stripeProcess'])->name('payment.stripe');

Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/invoice/{reference}', [TransactionController::class, 'show'])->name('transaction.show');
Route::post('/callback/midtrans', [CallbackController::class, 'handle']);

Route::post('/reviews', [App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store')->middleware('auth');

// --- AUTHENTICATION ---
Route::get('/auth/discord', [DiscordAuthController::class, 'redirect'])->name('auth.discord');
Route::get('/login', [DiscordAuthController::class, 'redirect'])->name('login'); // Alias for auth middleware
Route::get('/auth/discord/callback', [DiscordAuthController::class, 'callback']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- SESSION & USER UPDATE ---
Route::get('/set-session-product/{id}', function($id) {
    session(['checkout_product_id' => $id]);
    return response()->json(['status' => 'success']);
});
Route::post('/update-email', [DiscordAuthController::class, 'saveEmail'])->name('user.update_email');

// --- PROFILE ---
Route::get('/profile/{name}.{id}', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
Route::post('/profile/{name}.{id}/banner', [App\Http\Controllers\ProfileController::class, 'updateBanner'])->name('profile.update_banner')->middleware('auth');
Route::post('/profile/{name}.{id}/bio', [App\Http\Controllers\ProfileController::class, 'updateBio'])->name('profile.update_bio')->middleware('auth');
Route::post('/profile/{name}.{id}/avatar', [App\Http\Controllers\ProfileController::class, 'updateAvatarAndFrame'])->name('profile.update_avatar')->middleware('auth');

// --- HALAMAN ADMIN (Wajib Login & Role Admin) ---
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    
    Route::get('/dashboard', [ProductController::class, 'adminDashboard'])->name('admin.dashboard');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('admin.transactions.index');
    Route::get('/transactions/chart-data', [TransactionController::class, 'chartData'])->name('admin.transactions.chart');
    Route::delete('/transaction/{id}', [TransactionController::class, 'destroy'])->name('admin.transaction.destroy');
    Route::delete('/transactions/truncate', [TransactionController::class, 'truncate'])->name('admin.transactions.truncate');

    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/store', [ProductController::class, 'store'])->name('admin.store');
    Route::get('/edit/{id}', [ProductController::class, 'edit'])->name('admin.edit');
    Route::put('/update/{id}', [ProductController::class, 'update'])->name('admin.update');
    Route::delete('/delete/{id}', [ProductController::class, 'destroy'])->name('admin.delete');

    Route::get('/product/{id}/vouchers', [VoucherController::class, 'index'])->name('admin.vouchers.index');
    Route::post('/vouchers', [VoucherController::class, 'store'])->name('admin.vouchers.store');
    Route::delete('/vouchers/delete/{id}', [VoucherController::class, 'destroy'])->name('admin.vouchers.destroy');

    Route::get('/promos', [PromoController::class, 'index'])->name('admin.promos');
    Route::post('/promos', [PromoController::class, 'store'])->name('admin.promos.store');
    Route::delete('/promos/{id}', [PromoController::class, 'destroy'])->name('admin.promos.delete');

    // Manajemen User
    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users');
    Route::get('/users/search', [AdminUserController::class, 'searchJson'])->name('admin.users.search');
    Route::post('/users/update/{id}', [AdminUserController::class, 'update'])->name('admin.user.update');
    Route::post('/users/{id}/update-email', [AdminUserController::class, 'updateEmail'])->name('admin.user.update_email');
    Route::post('/users/delete/{id}', [AdminUserController::class, 'destroy'])->name('admin.user.delete');
    Route::get('/users/{id}/history', [AdminUserController::class, 'history'])->name('admin.user.history');

    // Manajemen Reviews
    Route::get('/reviews', [App\Http\Controllers\ReviewController::class, 'index'])->name('admin.reviews.index');
    Route::patch('/reviews/{id}/toggle-publish', [App\Http\Controllers\ReviewController::class, 'togglePublish'])->name('admin.reviews.toggle_publish');
    Route::delete('/reviews/{id}', [App\Http\Controllers\ReviewController::class, 'destroy'])->name('admin.reviews.destroy');
});