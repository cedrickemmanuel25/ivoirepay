<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KycController;
use App\Http\Controllers\Api\MerchantController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ─── Auth routes (public) ────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/send-otp',   [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/register',   [AuthController::class, 'register']);
    Route::post('/login-pin',  [AuthController::class, 'loginWithPin']);
});

// ─── Yengapay Webhook (public, signature-verified) ───────────────────────────
Route::post('/webhooks/yengapay', [TransactionController::class, 'webhook']);

// ─── Merchant info by QR (public) ────────────────────────────────────────────
Route::get('/merchants/{merchantId}', [TransactionController::class, 'getMerchantInfo']);

// ─── Protected routes ─────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/setup-pin',  [AuthController::class, 'setupPin']);
    Route::post('/auth/change-pin', [AuthController::class, 'changePin']);
    Route::post('/auth/logout',     [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return collect($user->toArray())->only(['id', 'name', 'phone', 'role', 'avatar', 'kyc_status', 'has_pin'])->all();
    });

    // Profile (client)
    Route::post('/profile/info',              [AuthController::class, 'updateProfile']);

    // Transactions (client)
    Route::get('/transactions',               [TransactionController::class, 'indexClient']);
    Route::get('/transactions/{id}',          [TransactionController::class, 'showClient']);
    Route::get('/transactions/{id}/receipt',  [TransactionController::class, 'downloadReceiptClient']);
    Route::post('/transactions/initiate',     [TransactionController::class, 'initiate']);
    Route::post('/transactions/{id}/confirm', [TransactionController::class, 'confirm']);

    // KYC (merchant role)
    Route::prefix('merchant/kyc')->group(function () {
        Route::post('/submit',    [KycController::class, 'submit']);
        Route::get('/status',     [KycController::class, 'status']);
        Route::post('/documents', [KycController::class, 'uploadDocument']);
    });

    // Merchant profile & operations
    Route::prefix('merchant')->group(function () {
        Route::get('/profile',      [MerchantController::class, 'profile']);
        Route::get('/qr-code',      [MerchantController::class, 'qrCode']);
        Route::get('/transactions', [MerchantController::class, 'transactions']);
        Route::get('/dashboard',    [MerchantController::class, 'dashboard']);
        Route::post('/withdrawal',  [MerchantController::class, 'withdrawal']);
        Route::get('/withdrawals', [MerchantController::class, 'withdrawals']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/',             [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/read-all',    [NotificationController::class, 'markAllRead']);
        Route::post('/{id}/read',   [NotificationController::class, 'markRead']);
    });
});



