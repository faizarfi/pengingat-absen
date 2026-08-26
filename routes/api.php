<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgentApiController;
use App\Http\Middleware\AgentTokenMiddleware;

/*
|--------------------------------------------------------------------------
| Agent API Routes
|--------------------------------------------------------------------------
|
| Endpoint untuk WA Desktop Agent.
| Semua route dilindungi oleh AgentTokenMiddleware (Bearer Token).
|
*/

Route::prefix('agent')
    ->middleware(AgentTokenMiddleware::class)
    ->controller(AgentApiController::class)
    ->group(function () {
        // Ambil pesan pending
        Route::get('/messages', 'getMessages');

        // Update status pesan
        Route::post('/messages/{id}/processing', 'markProcessing');
        Route::post('/messages/{id}/sent', 'markSent');
        Route::post('/messages/{id}/failed', 'markFailed');

        // Heartbeat & status
        Route::post('/heartbeat', 'heartbeat');
        Route::get('/status', 'status');
    });

/*
|--------------------------------------------------------------------------
| Telegram Bot Webhook
|--------------------------------------------------------------------------
*/
Route::post('/telegram/webhook', [\App\Http\Controllers\TelegramWebhookController::class, 'handle'])->name('telegram.webhook');
