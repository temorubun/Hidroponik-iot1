<?php

namespace App\Services\WebSocket;

use App\Models\Device;

class WebSocketBroadcaster
{
    protected static $clients = [];
    protected static $deviceIPs = [];

    public static function setClients(array $clients)
    {
        self::$clients = $clients;
    }

    public static function setDeviceIP($deviceId, $ipAddress)
    {
        self::$deviceIPs[$deviceId] = $ipAddress;
    }

    public static function removeDeviceIP($deviceId)
    {
        unset(self::$deviceIPs[$deviceId]);
    }

    public static function broadcastDeviceStatus(Device $device)
    {
        $statusData = [
            'type' => 'device_status',
            'device_id' => $device->id,
            'device_key' => $device->device_key,
            'device_name' => $device->name,
            'is_online' => $device->is_online,
            'last_online' => $device->last_online ? $device->last_online->format('Y-m-d H:i:s') : null,
            'ip_address' => self::$deviceIPs[$device->id] ?? null
        ];

        foreach (self::$clients as $client) {
            $client->send(json_encode($statusData));
        }
    }
} 