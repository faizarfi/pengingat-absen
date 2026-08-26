<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebhookController;

// Public & Webhook
Route::view('/', 'auth.login');
Route::post('/webhook/fonnte', [WebhookController::class, 'handleFonnte'])->name('webhook.fonnte');

// Auth Routes
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->name('logout');
});

// Admin Routes
Route::middleware('auth')->prefix('admin')->name('admin.')->controller(AdminController::class)->group(function () {
    Route::get('/', 'index')->name('dashboard');
    Route::post('/settings', 'updateSetting')->name('settings.update');
    Route::post('/set-default-times', 'setDefaultTimes')->name('set-default-times');

    // Employees
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/export', 'exportEmployees')->name('export');
        Route::post('/import', 'importEmployees')->name('import');
        Route::post('/', 'storeEmployee')->name('store');
        Route::put('/{id}', 'updateEmployee')->name('update');
        Route::delete('/{id}', 'deleteEmployee')->name('delete');
    });

    // Send Actions (POST)
    Route::post('/send-now', 'sendNow')->name('send-now');
    Route::post('/send-pre-checkin', 'sendPreCheckinNow')->name('send-pre-checkin');
    Route::post('/send-pre-checkout', 'sendPreCheckoutNow')->name('send-pre-checkout');

    // Outbox & Holiday Actions (POST)
    Route::post('/outbox/retry-failed', 'retryFailedOutbox')->name('outbox.retry-failed');
    Route::post('/outbox/cancel-pending', 'cancelPendingOutbox')->name('outbox.cancel-pending');
    Route::post('/outbox/{id}/retry', 'retrySingleOutbox')->name('outbox.retry-single');
    Route::post('/holidays/sync', 'syncHolidays')->name('holidays.sync');

    // Fallback GET redirects (mencegah MethodNotAllowed jika diakses lewat URL)
    Route::get('/send-now', fn() => redirect()->route('admin.dashboard')->with('error', 'Gunakan tombol di dashboard untuk mengirim pengingat (POST).'));
    Route::get('/send-pre-checkin', fn() => redirect()->route('admin.dashboard')->with('error', 'Gunakan tombol di dashboard untuk mengirim pengingat masuk (POST).'));
    Route::get('/send-pre-checkout', fn() => redirect()->route('admin.dashboard')->with('error', 'Gunakan tombol di dashboard untuk mengirim pengingat pulang (POST).'));
});