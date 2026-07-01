<?php

namespace App\Http\Controllers;

use App\Models\Station;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StationPortalController extends Controller
{
    public function display(Station $station): View
    {
        return view('stations.display', [
            'station' => $station,
            'activeSession' => $station->playSessions()->where('status', 'active')->latest('started_at')->first(),
        ]);
    }

    public function join(Request $request, string $token): View|RedirectResponse
    {
        $station = Station::query()->where('qr_token', $token)->firstOrFail();

        if ($request->user()) {
            return redirect()->route('portal.index', [
                'station' => $station->code,
            ]);
        }

        return view('stations.join', [
            'station' => $station,
        ]);
    }
}
