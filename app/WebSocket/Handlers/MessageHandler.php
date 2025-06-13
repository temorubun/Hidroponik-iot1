<?php

namespace App\WebSocket\Handlers;

use Workerman\Connection\TcpConnection;
use App\Models\Device;

class MessageHandler
{
    public function handle(TcpConnection $from, $msg)
    {
        try {
            \Log::info("WebSocket raw message received:", ['message' => $msg]);
            
            $data = json_decode($msg, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error("Failed to parse WebSocket message:", [
                    'error' => json_last_error_msg(),
                    'raw_message' => $msg
                ]);
                return null;
            }
            
            \Log::info("WebSocket message parsed:", ['data' => $data]);
            return $data;
        } catch (\Exception $e) {
            \Log::error("Error processing WebSocket message:", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function handleWebAuth(TcpConnection $from, array $data, array &$clients)
    {
        if (isset($data['type']) && $data['type'] === 'web_auth') {
            $deviceKey = $data['device_key'];
            $device = Device::where('device_key', $deviceKey)->first();
            
            if ($device) {
                $clients[$from->id] = $from;
                return $device;
            }
        }
        return null;
    }

    public function handleRebootResponse(array $data)
    {
        $deviceKey = $data['device_key'] ?? null;
        $device = Device::where('device_key', $deviceKey)->first();
        
        if ($device) {
            \Log::info("Received reboot confirmation from {$device->name}: " . ($data['message'] ?? 'No message'));
            
            $device->update([
                'is_online' => false,
                'last_online' => now()
            ]);
            
            return [
                'type' => 'reboot_status',
                'device_id' => $device->id,
                'status' => $data['status'] ?? 'success',
                'message' => $data['message'] ?? 'Device is rebooting'
            ];
        }
        return null;
    }
} 