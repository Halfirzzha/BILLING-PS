<?php

namespace App\Http\Controllers;

use App\Models\PlaySession;
use App\Models\Station;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class SessionController extends Controller
{
    public function store(Request $request, Station $station, BillingService $billingService): RedirectResponse
    {
        try {
            $billingService->startSession($request->user(), $station);
        } catch (RuntimeException $exception) {
            return back()->withErrors([
                'session' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('portal.index', [
            'station' => $station->code,
        ])->with('status', "Sesi untuk {$station->name} berhasil dimulai.");
    }

    public function destroy(Request $request, PlaySession $playSession, BillingService $billingService): RedirectResponse
    {
        abort_unless($playSession->user_id === $request->user()->id, 403);

        try {
            $billingService->endSession($playSession, $request->user());
        } catch (RuntimeException $exception) {
            return back()->withErrors([
                'session' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('portal.index')->with('status', 'Sesi berhasil diakhiri.');
    }
}
