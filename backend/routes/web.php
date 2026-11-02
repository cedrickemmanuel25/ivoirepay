<?php

use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('landing'))->name('landing');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.submit');

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [App\Http\Controllers\Admin\AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Admin\AdminAuthController::class, 'login']);
    Route::post('/logout', [App\Http\Controllers\Admin\AdminAuthController::class, 'logout'])->name('logout');

    // Protected Admin Routes
    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        
        // KYC
        Route::get('/kyc', [App\Http\Controllers\Admin\KycController::class, 'index'])->name('kyc.index');
        Route::get('/kyc/{id}', [App\Http\Controllers\Admin\KycController::class, 'show'])->name('kyc.show');
        Route::post('/kyc/{id}/approve', [App\Http\Controllers\Admin\KycController::class, 'approve'])->name('kyc.approve');
        Route::post('/kyc/{id}/reject', [App\Http\Controllers\Admin\KycController::class, 'reject'])->name('kyc.reject');

        // Transactions
        Route::get('/transactions', [App\Http\Controllers\Admin\TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/export/pdf', [App\Http\Controllers\Admin\TransactionController::class, 'exportPdf'])->name('transactions.export.pdf');
        Route::get('/transactions/export/csv', [App\Http\Controllers\Admin\TransactionController::class, 'exportCsv'])->name('transactions.export.csv');

        // Merchants
        Route::get('/merchants', [App\Http\Controllers\Admin\MerchantController::class, 'index'])->name('merchants.index');
        Route::get('/merchants/{id}', [App\Http\Controllers\Admin\MerchantController::class, 'show'])->name('merchants.show');
        Route::post('/merchants/{id}/toggle', [App\Http\Controllers\Admin\MerchantController::class, 'toggle'])->name('merchants.toggle');
        Route::get('/merchants/{id}/qr', [App\Http\Controllers\Admin\MerchantController::class, 'downloadQr'])->name('merchants.qr');

        // Withdrawals
        Route::get('/withdrawals', [App\Http\Controllers\Admin\WithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('/withdrawals/{withdrawal}/process', [App\Http\Controllers\Admin\WithdrawalController::class, 'process'])->name('withdrawals.process');
        Route::post('/withdrawals/{withdrawal}/cancel', [App\Http\Controllers\Admin\WithdrawalController::class, 'cancel'])->name('withdrawals.cancel');

        // Users
        Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/{id}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
        Route::post('/users/{id}/toggle', [App\Http\Controllers\Admin\UserController::class, 'toggleActive'])->name('users.toggle');
        Route::post('/users/{id}/sms', [App\Http\Controllers\Admin\UserController::class, 'sendSms'])->name('users.sms');
        Route::delete('/users/{id}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

        // Notifications
        Route::get('/notifications', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/api/unread-count', [App\Http\Controllers\Admin\NotificationController::class, 'unreadCount'])->name('notifications.api.unread');
        Route::post('/notifications/mark-all-read', [App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-read');
        Route::post('/notifications/send-bulk', [App\Http\Controllers\Admin\NotificationController::class, 'sendBulk'])->name('notifications.send-bulk');

        // Profile
        Route::get('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('profile.index');
        Route::post('/profile/info', [App\Http\Controllers\Admin\ProfileController::class, 'updateInfo'])->name('profile.info');
        Route::post('/profile/avatar', [App\Http\Controllers\Admin\ProfileController::class, 'updateAvatar'])->name('profile.avatar');
        Route::post('/profile/password', [App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('profile.password');

        // Settings
        Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

        // Placeholder for password request to avoid 404 in view
        Route::get('/password/reset', fn() => "Reset Password Placeholder")->name('password.request');
    });
});

