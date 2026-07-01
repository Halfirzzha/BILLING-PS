<?php

use App\Http\Controllers\Api\StationDeviceController;
use Illuminate\Support\Facades\Route;

Route::prefix('device/stations/{station:code}')->group(function (): void {
    Route::post('/heartbeat', [StationDeviceController::class, 'heartbeat']);
    Route::get('/commands/next', [StationDeviceController::class, 'nextCommand']);
    Route::post('/commands/{command}/acknowledge', [StationDeviceController::class, 'acknowledge']);
});
