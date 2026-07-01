<?php

use App\Http\Controllers\Api\StationDeviceController;
use Illuminate\Support\Facades\Route;

// Android TV / device endpoints. Authenticated per-station via X-Station-Token.
Route::prefix('device/stations/{station:code}')
    ->middleware('station.token')
    ->group(function (): void {
        Route::post('/heartbeat', [StationDeviceController::class, 'heartbeat']);
        Route::get('/state', [StationDeviceController::class, 'state']);
        Route::get('/commands/next', [StationDeviceController::class, 'nextCommand']);
        Route::post('/commands/{command}/acknowledge', [StationDeviceController::class, 'acknowledge']);
    });
