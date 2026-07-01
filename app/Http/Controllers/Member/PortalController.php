<?php

namespace App\Http\Controllers\Member;

use App\Enums\PlaySessionStatus;
use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Models\TimePackage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $station = null;
        if ($id = $request->session()->get('join_station_id')) {
            $station = Station::find($id);
        }

        $activeSession = $user->playSessions()
            ->where('status', PlaySessionStatus::Active->value)
            ->with('station')
            ->latest('started_at')
            ->first();

        $packages = TimePackage::query()
            ->where('is_active', true)
            ->when($station?->outlet_id, fn ($q) => $q->where('outlet_id', $station->outlet_id))
            ->orderBy('sort')
            ->orderBy('minutes')
            ->get();

        return view('portal.index', [
            'user' => $user,
            'station' => $station,
            'activeSession' => $activeSession,
            'packages' => $packages,
        ]);
    }
}
