<?php

use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\StationDeviceController;
use Illuminate\Support\Facades\Route;

// Payment gateway webhook (provider verifies authenticity inside the gateway impl).
Route::post('/payments/webhook', PaymentWebhookController::class)->name('payments.webhook');

// Android TV / device endpoints. Authenticated per-station via X-Station-Token.
Route::prefix('device/stations/{station:code}')
    ->middleware('station.token')
    ->group(function (): void {
        Route::post('/heartbeat', [StationDeviceController::class, 'heartbeat']);
        Route::get('/state', [StationDeviceController::class, 'state']);
        Route::get('/commands/next', [StationDeviceController::class, 'nextCommand']);
        Route::post('/commands/{command}/acknowledge', [StationDeviceController::class, 'acknowledge']);
    });
