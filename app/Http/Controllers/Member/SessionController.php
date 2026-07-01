<?php

namespace App\Http\Controllers\Member;

use App\Enums\PlaySessionStatus;
use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Services\SessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class SessionController extends Controller
{
    public function start(Request $request, SessionService $sessions): RedirectResponse
    {
        $stationId = $request->integer('station_id') ?: $request->session()->get('join_station_id');
        $station = Station::findOrFail($stationId);

        try {
            $sessions->startSession($request->user(), $station);

            return back()->with('status', 'Sesi dimulai. Selamat bermain!');
        } catch (Throwable $e) {
            return back()->withErrors(['session' => $e->getMessage()]);
        }
    }

    public function end(Request $request, SessionService $sessions): RedirectResponse
    {
        $active = $request->user()->playSessions()
            ->where('status', PlaySessionStatus::Active->value)
            ->latest('started_at')
            ->first();

        if (! $active) {
            return back()->withErrors(['session' => 'Tidak ada sesi aktif.']);
        }

        try {
            $sessions->endSession($active);

            return back()->with('status', 'Sesi selesai. Terima kasih!');
        } catch (Throwable $e) {
            return back()->withErrors(['session' => $e->getMessage()]);
        }
    }
}
