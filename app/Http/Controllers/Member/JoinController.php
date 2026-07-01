<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JoinController extends Controller
{
    public function show(Request $request, Station $station): RedirectResponse
    {
        $request->session()->put('join_station_id', $station->id);

        return redirect()->route('portal');
    }
}
