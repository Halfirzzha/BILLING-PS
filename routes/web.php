<?php

use App\Http\Controllers\Member\AuthController;
use App\Http\Controllers\Member\JoinController;
use App\Http\Controllers\Member\PortalController;
use App\Http\Controllers\Member\PurchaseController;
use App\Http\Controllers\Member\SessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('landing'))->name('home');

// Scan-to-join a station (public QR target). Stores station context, then
// sends the member to login/register (if guest) or straight to the portal.
Route::get('/join/{station:qr_token}', [JoinController::class, 'show'])->name('join');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/portal', [PortalController::class, 'index'])->name('portal');
    Route::post('/portal/purchase/{package}', [PurchaseController::class, 'store'])->name('portal.purchase');
    Route::post('/portal/session/start', [SessionController::class, 'start'])->name('portal.session.start');
    Route::post('/portal/session/end', [SessionController::class, 'end'])->name('portal.session.end');
});
