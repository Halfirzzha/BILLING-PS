<?php

use App\Http\Controllers\Auth\MemberAuthController;
use App\Http\Controllers\MemberPortalController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\StationPortalController;
use App\Models\Station;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home', [
        'stations' => Station::query()->where('is_active', true)->orderBy('name')->get(),
    ]);
})->name('home');

Route::get('/member/login', [MemberAuthController::class, 'createLogin'])->name('member.login');
Route::post('/member/login', [MemberAuthController::class, 'login'])->name('member.login.store');
Route::get('/member/register', [MemberAuthController::class, 'createRegister'])->name('member.register');
Route::post('/member/register', [MemberAuthController::class, 'register'])->name('member.register.store');
Route::post('/member/logout', [MemberAuthController::class, 'logout'])->middleware('auth')->name('member.logout');

Route::get('/tv/{station:code}', [StationPortalController::class, 'display'])->name('stations.display');
Route::get('/join/{token}', [StationPortalController::class, 'join'])->name('stations.join');

Route::middleware('auth')->group(function (): void {
    Route::get('/portal', MemberPortalController::class)->name('portal.index');
    Route::post('/portal/packages/{timePackage}', [PurchaseController::class, 'store'])->name('portal.packages.purchase');
    Route::post('/portal/stations/{station}/sessions', [SessionController::class, 'store'])->name('portal.sessions.store');
    Route::delete('/portal/sessions/{playSession}', [SessionController::class, 'destroy'])->name('portal.sessions.destroy');
});
