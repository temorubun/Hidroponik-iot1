<?php

namespace App\WebSocket\Traits;

use App\Models\Device;
use Workerman\Connection\TcpConnection;

trait WebSocketConnectionTrait
{
    protected function broadcastDeviceStatus($device)
    {
        $statusData = [
            'type' => 'device_status',
            'device_id' => $device->id,
            'device_key' => $device->device_key,
            'device_name' => $device->name,
            'is_online' => $device->is_online,
            'last_online' => $device->last_online,
            'ip_address' => isset($this->deviceIPs[$device->id]) ? 
                           $this->deviceIPs[$device->id] : null
        ];

        foreach ($this->clients as $client) {
            $client->send(json_encode($statusData));
        }
    }

    protected function cleanupDeviceConnection($device)
    {
        if (isset($this->deviceConnections[$device->device_key])) {
            $oldConn = $this->deviceConnections[$device->device_key];
            unset($this->connectionToDevice[$oldConn->id]);
            unset($this->deviceConnections[$device->device_key]);
        }
        unset($this->deviceIPs[$device->id]);
    }

    public function sendToDevice($device, $message)
    {
        if (isset($this->deviceConnections[$device->device_key])) {
            $connection = $this->deviceConnections[$device->device_key];
            try {
                $connection->send($message);
                \Log::info("Message sent to device {$device->name}: {$message}");
                return true;
            } catch (\Exception $e) {
                \Log::error("Error sending message to device {$device->name}: {$e->getMessage()}");
                return false;
            }
        } else {
            \Log::warning("Cannot send message to device {$device->name}: Device not connected");
            return false;
        }
    }
} 