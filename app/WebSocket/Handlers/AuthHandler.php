<?php

namespace App\WebSocket\Handlers;

use Workerman\Connection\TcpConnection;
use App\Models\Device;

class AuthHandler
{
    public function handle(TcpConnection $from, array $data, array &$deviceConnections, array &$connectionToDevice, array &$deviceIPs)
    {
        \Log::info("Processing authentication request", [
            'client_ip' => $from->getRemoteIp(),
            'data' => $data
        ]);

        $deviceKey = $data['device_key'];
        
        // Find device and update status
        $device = Device::where('device_key', $deviceKey)->first();
        if ($device) {
            \Log::info("Device found", [
                'device_name' => $device->name,
                'device_id' => $device->id,
                'existing_connection' => isset($deviceConnections[$deviceKey])
            ]);

            // Clean up any existing connection for this device
            if (isset($deviceConnections[$deviceKey])) {
                $oldConn = $deviceConnections[$deviceKey];
                \Log::info("Cleaning up existing connection", [
                    'old_connection_id' => $oldConn->id,
                    'device_name' => $device->name
                ]);
                unset($connectionToDevice[$oldConn->id]);
                unset($deviceConnections[$deviceKey]);
            }

            // Get device IP address
            $clientIP = $from->getRemoteIp();
            $deviceIPs[$device->id] = $clientIP;

            // Store connection mappings
            $deviceConnections[$deviceKey] = $from;
            $connectionToDevice[$from->id] = $device;
            
            \Log::info("Connection mappings updated", [
                'device_key' => $deviceKey,
                'connection_id' => $from->id,
                'device_connections_count' => count($deviceConnections),
                'connection_to_device_count' => count($connectionToDevice)
            ]);
            
            // Update device status to online
            $device->update([
                'is_online' => true,
                'last_online' => now()
            ]);

            \Log::info("Device {$device->name} connected from IP: {$clientIP}");

            // Send auth response
            $response = [
                'type' => 'auth_response',
                'status' => 'success',
                'message' => 'Authenticated successfully'
            ];
            \Log::info("Sending auth response", ['response' => $response]);
            $from->send(json_encode($response));

            return $device;
        }

        \Log::error("Authentication failed: Invalid device key", [
            'device_key' => $deviceKey,
            'client_ip' => $from->getRemoteIp()
        ]);

        $response = [
            'type' => 'auth_response',
            'status' => 'error',
            'message' => 'Invalid device key'
        ];
        \Log::info("Sending error response", ['response' => $response]);
        $from->send(json_encode($response));
        $from->close();

        return null;
    }
} 