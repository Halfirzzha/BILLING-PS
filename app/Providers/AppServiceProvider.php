<?php

namespace App\Providers;

use App\Enums\RoleName;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
