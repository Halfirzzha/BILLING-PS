<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\TimePackage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberPortalController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $station = null;

        if ($request->filled('station')) {
            $station = Station::query()
                ->where('code', $request->string('station'))
                ->orWhere('qr_token', $request->string('station'))
                ->first();
        }

        return view('portal.index', [
            'member' => $user,
            'station' => $station,
            'packages' => TimePackage::query()->where('is_active', true)->orderBy('minutes')->get(),
            'activeSession' => $user->playSessions()->where('status', 'active')->latest('started_at')->first(),
            'timeBalance' => $user->remaining_minutes,
        ]);
    }
}
