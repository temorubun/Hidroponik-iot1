<?php

namespace App\WebSocket\Traits;

use App\Models\Device;

trait WebSocketMessageHandlerTrait
{
    public function broadcastPinConfig($device, $pin)
    {
        \Log::info("Attempting to broadcast pin config", [
            'device_name' => $device->name,
            'device_key' => $device->device_key,
            'device_online' => $device->is_online,
            'pin_id' => $pin->id,
            'pin_number' => $pin->pin_number,
            'pin_type' => $pin->type,
            'has_connection' => isset($this->deviceConnections[$device->device_key])
        ]);

        if (isset($this->deviceConnections[$device->device_key])) {
            $connection = $this->deviceConnections[$device->device_key];
            
            $configMessage = [
                'type' => 'config',
                'pin' => $pin->pin_number,
                'pin_type' => $pin->type,
                'name' => $pin->name
            ];
            
            \Log::info("Broadcasting pin config to {$device->name}: " . json_encode($configMessage));
            
            try {
                $connection->send(json_encode($configMessage));
                \Log::info("Successfully sent pin config to {$device->name}");
            } catch (\Exception $e) {
                \Log::error("Error sending pin config to {$device->name}: {$e->getMessage()}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            \Log::warning("Cannot send pin config to {$device->name}: Device not connected", [
                'device_key' => $device->device_key,
                'device_online' => $device->is_online
            ]);
        }
    }

    protected function sendPHSensorConfig($device, $pin, $connection)
    {
        \Log::info("Raw pH sensor settings:", [
            'pin_number' => $pin->pin_number,
            'settings' => $pin->settings,
            'calibration' => $pin->settings['calibration'] ?? 'No calibration data'
        ]);

        $configMessage = [
            'type' => 'pin_config',
            'pin' => $pin->pin_number,
            'settings' => [
                'type' => 'ph_sensor',
                'samples' => $pin->settings['samples'] ?? 10,
                'interval' => $pin->settings['interval'] ?? 1000,
                'calibration' => [
                    '4' => floatval($pin->settings['calibration']['4'] ?? 4090),
                    '7' => floatval($pin->settings['calibration']['7'] ?? 3140),
                    '10' => floatval($pin->settings['calibration']['10'] ?? 2350)
                ]
            ]
        ];
        
        \Log::info("Sending pH sensor config for pin {$pin->pin_number}:", [
            'settings' => $configMessage['settings'],
            'calibration' => $configMessage['settings']['calibration']
        ]);
        
        $connection->send(json_encode($configMessage));
    }
} 