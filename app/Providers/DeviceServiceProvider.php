<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Device\DevicePingService;
use App\Services\DeviceStatusService;

class DeviceServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(DeviceStatusService::class, function ($app) {
            return new DeviceStatusService();
        });

        $this->app->singleton(DevicePingService::class, function ($app) {
            return new DevicePingService(
                $app->make(DeviceStatusService::class)
            );
        });
    }

    public function boot()
    {
        //
    }
} 