<?php

namespace App\WebSocket\Handlers;

use Workerman\Connection\TcpConnection;
use App\Models\Device;
use Workerman\Timer;

class DeviceHandler
{
    public function handlePing(TcpConnection $from, array $data, Device $device)
    {
        if (isset($data['type']) && $data['type'] === 'pong') {
            $device->update(['last_online' => now()]);
            \Log::debug("Received pong from {$device->name}");
            return true;
        }
        return false;
    }

    public function handleReboot(TcpConnection $from, array $data, Device $device, array &$deviceConnections, array &$connectionToDevice, array &$deviceIPs)
    {
        if ($device && $device->is_online) {
            \Log::info("Sending reboot command to device {$device->name}");
            try {
                // Send reboot command
                $rebootMessage = [
                    'type' => 'reboot',
                    'device_key' => $device->device_key,
                    'timestamp' => time()
                ];
                $deviceConnections[$device->device_key]->send(json_encode($rebootMessage));
                
                // Set device status to rebooting
                $device->update([
                    'is_online' => false,
                    'last_online' => now()
                ]);
                
                // Clean up existing connection
                $this->cleanupDeviceConnection($device, $deviceConnections, $connectionToDevice, $deviceIPs);
                
                return true;
            } catch (\Exception $e) {
                \Log::error("Error sending reboot command to {$device->name}: {$e->getMessage()}");
                return false;
            }
        }
        return false;
    }

    protected function cleanupDeviceConnection(Device $device, array &$deviceConnections, array &$connectionToDevice, array &$deviceIPs)
    {
        if (isset($deviceConnections[$device->device_key])) {
            $oldConn = $deviceConnections[$device->device_key];
            unset($connectionToDevice[$oldConn->id]);
            unset($deviceConnections[$device->device_key]);
        }
        unset($deviceIPs[$device->id]);
    }
} 