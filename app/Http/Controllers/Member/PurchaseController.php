<?php

namespace App\Http\Controllers\Member;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\TimePackage;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class PurchaseController extends Controller
{
    public function store(Request $request, TimePackage $package, BillingService $billing): RedirectResponse
    {
        try {
            $billing->purchaseTimePackage($request->user(), $package, PaymentMethod::Wallet);

            return back()->with('status', "Paket {$package->name} berhasil dibeli. Selamat bermain!");
        } catch (Throwable $e) {
            return back()->withErrors(['purchase' => $e->getMessage()]);
        }
    }
}
