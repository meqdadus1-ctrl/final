<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SalaryController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ChatController;

/*
|--------------------------------------------------------------------------
| API Routes — HR System Mobile App
|--------------------------------------------------------------------------
*/

// ===== Public Routes (no auth) =====
Route::prefix('v1')->group(function () {

    Route::post('/login',  [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    // ===== Protected Routes =====
    Route::middleware('auth:sanctum')->group(function () {

        // الملف الشخصي
        Route::get('/profile',         [ProfileController::class, 'show']);
        Route::put('/profile/bank',    [ProfileController::class, 'updateBank']);

        // الرواتب
        Route::get('/salary',          [SalaryController::class, 'index']);
        Route::get('/salary/{id}',     [SalaryController::class, 'show']);
        Route::post('/salary/{id}/request-statement', [SalaryController::class, 'requestStatement']);

        // السلف
        Route::get('/loans',           [LoanController::class, 'index']);
        Route::post('/loans',          [LoanController::class, 'store']);

        // الإجازات
        Route::get('/leaves',          [LeaveController::class, 'index']);
        Route::post('/leaves',         [LeaveController::class, 'store']);

        // الإشعارات
        Route::get('/notifications',         [NotificationController::class, 'index']);
        Route::post('/notifications/read',   [NotificationController::class, 'markAllRead']);
        Route::post('/fcm-token',            [NotificationController::class, 'saveFcmToken']);

        // المحادثات
        Route::get('/chat',                  [ChatController::class, 'index']);
        Route::post('/chat',                 [ChatController::class, 'store']);
        Route::post('/chat/read',            [ChatController::class, 'markRead']);
        Route::get('/chat/new',              [ChatController::class, 'getNew']);
        Route::get('/chat/unread-count',     [ChatController::class, 'getUnreadCount']);

    });
});
