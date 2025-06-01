<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Pin;
use App\Observers\PinObserver;

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
        Pin::observe(PinObserver::class);
    }
}
