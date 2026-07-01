<?php

namespace App\Providers;

use App\Enums\RoleName;
use App\Services\Payments\FakePaymentGateway;
use App\Services\Payments\PaymentGateway;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function () {
            return match (config('services.payment.gateway', 'fake')) {
                // Add 'midtrans' / 'xendit' implementations here when credentials are available.
                default => new FakePaymentGateway(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user) {
            if ($user->hasAnyRole([
                RoleName::Developer->value,
                RoleName::SuperAdmin->value,
            ])) {
                return true;
            }

            return null;
        });
    }
}
