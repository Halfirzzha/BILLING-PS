<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Models\TimePackage;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PurchaseController extends Controller
{
    public function store(Request $request, TimePackage $timePackage, BillingService $billingService): RedirectResponse
    {
        $request->validate([
            'payment_method' => ['required', 'in:wallet'],
        ]);

        try {
            $billingService->purchaseTimePackage(
                user: $request->user(),
                package: $timePackage,
                method: PaymentMethod::Wallet,
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors([
                'purchase' => $exception->getMessage(),
            ]);
        }

        return back()->with('status', "Paket {$timePackage->name} berhasil dibeli.");
    }
}
