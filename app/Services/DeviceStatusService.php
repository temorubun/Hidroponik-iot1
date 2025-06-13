<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Support\Facades\Log;
use App\Services\WebSocket\WebSocketBroadcaster;

class DeviceStatusService
{
    public function markOnline(Device $device)
    {
        $device->update([
            'is_online' => true,
            'last_online' => now()
        ]);

        Log::info("Device marked online: {$device->name}");
        WebSocketBroadcaster::broadcastDeviceStatus($device);
    }

    public function markOffline(Device $device)
    {
        $device->update([
            'is_online' => false,
            'last_online' => now()
        ]);

        Log::info("Device marked offline: {$device->name}");
        WebSocketBroadcaster::broadcastDeviceStatus($device);
    }
} 