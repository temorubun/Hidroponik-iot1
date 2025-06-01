<?php

namespace App\WebSocket;

use Workerman\Connection\TcpConnection;
use App\Models\Device;
use Carbon\Carbon;
use Workerman\Timer;
use App\Services\TelegramService;

class WebSocketHandler
{
    protected $clients;
    protected $deviceConnections;
    protected $connectionToDevice;
    protected $deviceIPs;
    protected $pingTimers;

    public function __construct()
    {
        $this->clients = [];
        $this->deviceConnections = [];
        $this->connectionToDevice = [];
        $this->deviceIPs = [];
        $this->pingTimers = [];
    }

    public function onOpen(TcpConnection $conn)
    {
        $this->clients[$conn->id] = $conn;
    }

    public function onMessage(TcpConnection $from, $msg)
    {
        try {
            \Log::info("WebSocket raw message received:", ['message' => $msg]);
            
            $data = json_decode($msg, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error("Failed to parse WebSocket message:", [
                    'error' => json_last_error_msg(),
                    'raw_message' => $msg
                ]);
                return;
            }
            
            \Log::info("WebSocket message parsed:", ['data' => $data]);
            
            // Handle device authentication
            if (isset($data['type'])) {
                \Log::info("Processing message type: " . $data['type']);
                
                if ($data['type'] === 'auth') {
                    $deviceKey = $data['device_key'];
                    
                    // Find device and update status
                    $device = Device::where('device_key', $deviceKey)->first();
                    if ($device) {
                        // Clean up any existing connection for this device
                        if (isset($this->deviceConnections[$deviceKey])) {
                            $oldConn = $this->deviceConnections[$deviceKey];
                            unset($this->connectionToDevice[$oldConn->id]);
                            unset($this->deviceConnections[$deviceKey]);
                        }

                        // Get device IP address
                        $clientIP = $from->getRemoteIp();
                        $this->deviceIPs[$device->id] = $clientIP;

                        // Store connection mappings
                        $this->deviceConnections[$deviceKey] = $from;
                        $this->connectionToDevice[$from->id] = $device;
                        
                        // Update device status to online
                        $device->update([
                            'is_online' => true,
                            'last_online' => now()
                        ]);

                        \Log::info("Device {$device->name} connected from IP: {$clientIP}");

                        // Send success response
                        $from->send(json_encode([
                            'status' => 'success',
                            'message' => 'Authenticated successfully'
                        ]));

                        // Send all pin configurations after successful authentication
                        \Log::info("Sending all pin configurations to {$device->name}");
                        foreach ($device->pins as $pin) {
                            \Log::info("- Sending config for pin {$pin->pin_number} ({$pin->type})", [
                                'settings' => $pin->settings,
                                'is_active' => $pin->is_active,
                                'value' => $pin->value
                            ]);
                            
                            // For pH sensors, send special configuration
                            if ($pin->type === 'ph_sensor') {
                                // Log raw settings first
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
                                            '4' => floatval($pin->settings['calibration']['4'] ?? 4090), // pH 4.01 = 4090-4095
                                            '7' => floatval($pin->settings['calibration']['7'] ?? 3140), // pH 6.86 = ~3140
                                            '10' => floatval($pin->settings['calibration']['10'] ?? 2350) // pH 9.18 = 2350-2400
                                        ]
                                    ]
                                ];
                                
                                \Log::info("Sending pH sensor config for pin {$pin->pin_number}:", [
                                    'settings' => $configMessage['settings'],
                                    'calibration' => $configMessage['settings']['calibration'],
                                    'raw_calibration_4' => $pin->settings['calibration']['4'] ?? 'not set',
                                    'raw_calibration_7' => $pin->settings['calibration']['7'] ?? 'not set',
                                    'raw_calibration_10' => $pin->settings['calibration']['10'] ?? 'not set'
                                ]);
                                
                                $from->send(json_encode($configMessage));
                            } else {
                                $this->broadcastPinConfig($device, $pin);
                            }
                        }

                        // Broadcast status change to all clients
                        $this->broadcastDeviceStatus($device);

                        // Restart ping timer for this device
                        $this->restartPingTimer($device);
                    } else {
                        \Log::error("Authentication failed: Invalid device key");
                        $from->send(json_encode([
                            'status' => 'error',
                            'message' => 'Invalid device key'
                        ]));
                        $from->close();
                    }
                }
                // Handle ping response
                else if ($data['type'] === 'pong') {
                    if (isset($this->connectionToDevice[$from->id])) {
                        $device = $this->connectionToDevice[$from->id];
                        $device->update(['last_online' => now()]);
                        \Log::debug("Received pong from {$device->name}");
                    }
                }
                // Handle get_pins request
                else if ($data['type'] === 'get_pins') {
                    $deviceKey = $data['device_key'];
                    $device = Device::where('device_key', $deviceKey)->first();
                    
                    if ($device) {
                        echo "[" . Carbon::now()->format('Y-m-d H:i:s') . "] Sending pin configuration to {$device->name}\n";
                        foreach ($device->pins as $pin) {
                            $this->broadcastPinConfig($device, $pin);
                        }
                    }
                }
                // Handle pin configuration confirmation
                else if ($data['type'] === 'pin_configured') {
                    $deviceKey = isset($data['device_key']) ? $data['device_key'] : null;
                    $device = Device::where('device_key', $deviceKey)->first();
                    
                    if ($device) {
                        echo "[" . Carbon::now()->format('Y-m-d H:i:s') . "] Pin {$data['pin']} configured on {$device->name}\n";
                    }
                }
                // Handle web client authentication
                else if ($data['type'] === 'web_auth') {
                    $deviceKey = $data['device_key'];
                    $device = Device::where('device_key', $deviceKey)->first();
                    
                    if ($device) {
                        // Store this connection as a web client
                        $this->clients[$from->id] = $from;
                        
                        // Send current device status immediately
                        if ($device->is_online && isset($this->deviceIPs[$device->id])) {
                            $this->broadcastDeviceStatus($device);
                        }
                    }
                }
                // Handle pin control message
                else if ($data['type'] === 'pin') {
                    $deviceKey = isset($data['device_key']) ? $data['device_key'] : null;
                    
                    // Log pin control message
                    echo "[" . Carbon::now()->format('Y-m-d H:i:s') . "] Received pin control message: " . json_encode($data) . "\n";
                    
                    // Find the device connection
                    if (isset($this->deviceConnections[$deviceKey])) {
                        $deviceConn = $this->deviceConnections[$deviceKey];
                        $device = Device::where('device_key', $deviceKey)->first();
                        
                        if ($device && $device->is_online) {
                            echo "[" . Carbon::now()->format('Y-m-d H:i:s') . "] Forwarding pin control to device {$device->name}\n";
                            try {
                                $deviceConn->send(json_encode($data));
                                
                                // Send success response back to web client
                                if (isset($this->clients[$from->id])) {
                                    $from->send(json_encode([
                                        'status' => 'success',
                                        'message' => 'Pin control message sent successfully'
                                    ]));
                                }
                            } catch (\Exception $e) {
                                echo "[" . Carbon::now()->format('Y-m-d H:i:s') . "] Error sending pin control: {$e->getMessage()}\n";
                                if (isset($this->clients[$from->id])) {
                                    $from->send(json_encode([
                                        'status' => 'error',
                                        'message' => 'Failed to send pin control message'
                                    ]));
                                }
                            }
                        } else {
                            echo "[" . Carbon::now()->format('Y-m-d H:i:s') . "] Device offline or not found\n";
                            if (isset($this->clients[$from->id])) {
                                $from->send(json_encode([
                                    'status' => 'error',
                                    'message' => 'Device is offline'
                                ]));
                            }
                        }
                    } else {
                        echo "[" . Carbon::now()->format('Y-m-d H:i:s') . "] Device connection not found for key: {$deviceKey}\n";
                        if (isset($this->clients[$from->id])) {
                            $from->send(json_encode([
                                'status' => 'error',
                                'message' => 'Device not connected'
                            ]));
                        }
                    }
                }
                // Handle reboot command
                else if ($data['type'] === 'reboot') {
                    $deviceKey = isset($data['device_key']) ? $data['device_key'] : null;
                    
                    if (isset($this->deviceConnections[$deviceKey])) {
                        $deviceConn = $this->deviceConnections[$deviceKey];
                        $device = Device::where('device_key', $deviceKey)->first();
                        
                        if ($device && $device->is_online) {
                            \Log::info("Sending reboot command to device {$device->name}");
                            try {
                                // Send reboot command
                                $rebootMessage = [
                                    'type' => 'reboot',
                                    'device_key' => $device->device_key,
                                    'timestamp' => time()
                                ];
                                $deviceConn->send(json_encode($rebootMessage));
                                
                                // Set device status to rebooting
                                $device->update([
                                    'is_online' => false,
                                    'last_online' => now()
                                ]);
                                
                                // Clean up existing connection
                                $this->cleanupDeviceConnection($device);
                                
                                // Broadcast status to web clients
                                $this->broadcastDeviceStatus($device);
                                
                                // Send success response back to web client
                                if (isset($this->clients[$from->id])) {
                                    $from->send(json_encode([
                                        'status' => 'success',
                                        'message' => 'Reboot command sent successfully'
                                    ]));
                                }

                                \Log::info("Reboot command sent successfully to {$device->name}");
                            } catch (\Exception $e) {
                                \Log::error("Error sending reboot command to {$device->name}: {$e->getMessage()}");
                                if (isset($this->clients[$from->id])) {
                                    $from->send(json_encode([
                                        'status' => 'error',
                                        'message' => 'Failed to send reboot command: ' . $e->getMessage()
                                    ]));
                                }
                            }
                        } else {
                            $errorMsg = $device ? 'Device is offline' : 'Device not found';
                            \Log::warning("Cannot reboot {$deviceKey}: {$errorMsg}");
                            if (isset($this->clients[$from->id])) {
                                $from->send(json_encode([
                                    'status' => 'error',
                                    'message' => $errorMsg
                                ]));
                            }
                        }
                    } else {
                        \Log::warning("Cannot reboot: Device not connected");
                        if (isset($this->clients[$from->id])) {
                            $from->send(json_encode([
                                'status' => 'error',
                                'message' => 'Device not connected'
                            ]));
                        }
                    }
                }
                // Handle reboot response
                else if ($data['type'] === 'reboot_response') {
                    $deviceKey = $data['device_key'] ?? null;
                    $device = Device::where('device_key', $deviceKey)->first();
                    
                    if ($device) {
                        \Log::info("Received reboot confirmation from {$device->name}: " . ($data['message'] ?? 'No message'));
                        
                        // Update device status
                        $device->update([
                            'is_online' => false,
                            'last_online' => now()
                        ]);
                        
                        // Broadcast reboot status to all web clients
                        $statusMessage = [
                            'type' => 'reboot_status',
                            'device_id' => $device->id,
                            'status' => $data['status'] ?? 'success',
                            'message' => $data['message'] ?? 'Device is rebooting'
                        ];
                        
                        foreach ($this->clients as $client) {
                            $client->send(json_encode($statusMessage));
                        }
                    }
                }
                // Handle sensor data
                else if ($data['type'] === 'sensor_data') {
                    \Log::info("Received sensor data:", $data);
                    
                    $deviceKey = $data['device_key'] ?? null;
                    if (!$deviceKey) {
                        \Log::error("No device_key in sensor data");
                        return;
                    }
                    
                    $device = Device::where('device_key', $deviceKey)->first();
                    if (!$device) {
                        \Log::error("Device not found for key: " . $deviceKey);
                        return;
                    }
                    
                    \Log::info("Found device: {$device->name}");
                    
                    // Get pin number from data
                    $pinNumber = $data['pin'] ?? null;
                    if (!$pinNumber) {
                        \Log::error("No pin number in sensor data");
                        return;
                    }
                    
                    // Find the pin in the device
                    $pin = $device->pins()->where('pin_number', $pinNumber)->first();
                    if (!$pin) {
                        \Log::error("Pin not found: {$pinNumber}");
                        return;
                    }
                    
                    // Handle pH sensor data
                    if ($pin->type === 'ph_sensor' && isset($data['value'])) {
                        $phValue = floatval($data['value']);
                        
                        \Log::info("Processing pH value from pin {$pinNumber}: {$phValue}", [
                            'raw_value' => $data['value'],
                            'device_key' => $deviceKey,
                            'pin_type' => $pin->type
                        ]);
                        
                        // Update pin value in database
                        $pin->update([
                            'value' => $phValue,
                            'last_update' => now()
                        ]);

                        // Broadcast to web clients
                        $broadcastData = [
                            'type' => 'sensor_update',
                            'device_id' => $device->id,
                            'pin' => [
                                'id' => $pin->id,
                                'value' => $phValue
                            ],
                            'timestamp' => now()->toDateTimeString()
                        ];

                        foreach ($this->clients as $client) {
                            $client->send(json_encode($broadcastData));
                        }

                        // Check alert conditions if configured
                        if ($pin->settings && 
                            isset($pin->settings['alerts']) && 
                            isset($pin->settings['alerts']['enabled']) && 
                            $pin->settings['alerts']['enabled'] && 
                            isset($pin->settings['alerts']['telegram_chat_id'])) {
                            
                            $chatId = $pin->settings['alerts']['telegram_chat_id'];
                            $telegramService = app(TelegramService::class);

                            \Log::info("Alert configuration found:", [
                                'enabled' => true,
                                'chat_id' => $chatId,
                                'current_value' => $phValue,
                                'min_threshold' => $pin->settings['alerts']['min_threshold'] ?? 'not set',
                                'max_threshold' => $pin->settings['alerts']['max_threshold'] ?? 'not set',
                                'alert_below_min' => $pin->settings['alerts']['alert_below_min'] ?? false,
                                'alert_above_max' => $pin->settings['alerts']['alert_above_max'] ?? false
                            ]);

                            try {
                                // Check min threshold
                                if (isset($pin->settings['alerts']['min_threshold']) && 
                                    isset($pin->settings['alerts']['alert_below_min']) && 
                                    $pin->settings['alerts']['alert_below_min']) {
                                    
                                    $minThreshold = floatval($pin->settings['alerts']['min_threshold']);
                                    
                                    \Log::info("Checking minimum threshold condition:", [
                                        'current_value' => $phValue,
                                        'min_threshold' => $minThreshold,
                                        'is_below' => $phValue < $minThreshold
                                    ]);
                                    
                                    if ($phValue < $minThreshold) {
                                        \Log::info("Preparing to send below minimum alert");
                                        
                                        $message = "⚠️ *pH Alert!*\n" .
                                                 "*Device:* {$device->name}\n" .
                                                 "*Current pH:* {$phValue}\n" .
                                                 "*Min Threshold:* {$minThreshold}\n" .
                                                 "*Status:* Below minimum\n" .
                                                 "*Time:* " . now()->setTimezone('Asia/Jakarta')->format('H:i:s');
                                        
                                        $result = $telegramService->sendMessage($chatId, $message);
                                        \Log::info("Below minimum alert sent:", ['success' => $result]);
                                    }
                                }

                                // Check max threshold
                                if (isset($pin->settings['alerts']['max_threshold']) && 
                                    isset($pin->settings['alerts']['alert_above_max']) && 
                                    $pin->settings['alerts']['alert_above_max']) {
                                    
                                    $maxThreshold = floatval($pin->settings['alerts']['max_threshold']);
                                    
                                    \Log::info("Checking maximum threshold condition:", [
                                        'current_value' => $phValue,
                                        'max_threshold' => $maxThreshold,
                                        'is_above' => $phValue > $maxThreshold
                                    ]);
                                    
                                    if ($phValue > $maxThreshold) {
                                        \Log::info("Preparing to send above maximum alert");
                                        
                                        $message = "⚠️ *pH Alert!*\n" .
                                                 "*Device:* {$device->name}\n" .
                                                 "*Current pH:* {$phValue}\n" .
                                                 "*Max Threshold:* {$maxThreshold}\n" .
                                                 "*Status:* Above maximum\n" .
                                                 "*Time:* " . now()->setTimezone('Asia/Jakarta')->format('H:i:s');
                                        
                                        $result = $telegramService->sendMessage($chatId, $message);
                                        \Log::info("Above maximum alert sent:", ['success' => $result]);
                                    }
                                }
                            } catch (\Exception $e) {
                                \Log::error("Error in pH alert processing:", [
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString()
                                ]);
                            }
                        } else {
                            \Log::warning("pH alerts not properly configured:", [
                                'has_settings' => isset($pin->settings),
                                'has_alerts' => isset($pin->settings['alerts']),
                                'alerts_enabled' => $pin->settings['alerts']['enabled'] ?? false,
                                'has_chat_id' => isset($pin->settings['alerts']['telegram_chat_id']),
                                'raw_settings' => $pin->settings
                            ]);
                        }
                    } else {
                        \Log::info("No pH value in sensor data");
                    }
                }
            } else {
                \Log::warning("Message type not specified:", ['data' => $data]);
            }
        } catch (\Exception $e) {
            \Log::error("Error processing WebSocket message:", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function startPingTimer($device)
    {
        // Remove old timer if exists
        if (isset($this->pingTimers[$device->id])) {
            Timer::del($this->pingTimers[$device->id]);
            unset($this->pingTimers[$device->id]);
        }

        // Create new timer for ping every 10 seconds
        $timer_id = Timer::add(10, function() use ($device) {
            if (isset($this->deviceConnections[$device->device_key])) {
                $connection = $this->deviceConnections[$device->device_key];
                
                try {
                    // Send ping message
                    $connection->send(json_encode([
                        'type' => 'ping',
                        'timestamp' => time()
                    ]));
                    \Log::debug("Sent ping to {$device->name}");
                    
                    // Check last online time
                    $lastOnline = $device->last_online;
                    $now = now();
                    
                    // If no response for 30 seconds, consider device offline
                    if ($lastOnline && $now->diffInSeconds($lastOnline) > 30) {
                        \Log::info("Device {$device->name} considered offline - no pong for 30 seconds");
                        $device->update(['is_online' => false]);
                        $this->broadcastDeviceStatus($device);
                        
                        // Clean up connection
                        unset($this->deviceConnections[$device->device_key]);
                        unset($this->connectionToDevice[$connection->id]);
                        unset($this->deviceIPs[$device->id]);
                        $connection->close();
                    }
                } catch (\Exception $e) {
                    \Log::error("Error sending ping to {$device->name}: " . $e->getMessage());
                }
            }
        }, [], true);

        $this->pingTimers[$device->id] = $timer_id;
        \Log::info("Started ping timer for {$device->name}");
    }

    public function onClose(TcpConnection $conn)
    {
        if (isset($this->connectionToDevice[$conn->id])) {
            $device = $this->connectionToDevice[$conn->id];
            
            // Stop ping timer
            if (isset($this->pingTimers[$device->id])) {
                Timer::del($this->pingTimers[$device->id]);
                unset($this->pingTimers[$device->id]);
            }

            // Remove device IP
            unset($this->deviceIPs[$device->id]);

            // Update device status to offline
            $device->update([
                'is_online' => false,
                'last_online' => now()
            ]);

            // Clean up connections
            unset($this->deviceConnections[$device->device_key]);
            unset($this->connectionToDevice[$conn->id]);

            // Log disconnection
            \Log::info("{$device->name} disconnected");
            
            // Broadcast status change
            $this->broadcastDeviceStatus($device);
        }
        
        unset($this->clients[$conn->id]);
    }

    public function onError(TcpConnection $conn, $code, $msg)
    {
        if (isset($this->connectionToDevice[$conn->id])) {
            $device = $this->connectionToDevice[$conn->id];
            
            // Update device status to offline in database only
            Device::where('id', $device->id)->update([
                'is_online' => false,
                'last_online' => now()
            ]);

            echo "[" . Carbon::now()->format('Y-m-d H:i:s') . "] {$device->name} error: {$msg}\n";
        }
        $conn->close();
    }

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

    // Tambahkan method untuk broadcast konfigurasi pin
    public function broadcastPinConfig($device, $pin)
    {
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
                \Log::error("Error sending pin config to {$device->name}: {$e->getMessage()}");
            }
        } else {
            \Log::warning("Cannot send pin config to {$device->name}: Device not connected");
        }
    }

    // Add new method for cleaning up device connections
    protected function cleanupDeviceConnection($device)
    {
        // Stop ping timer if exists
        if (isset($this->pingTimers[$device->id])) {
            Timer::del($this->pingTimers[$device->id]);
            unset($this->pingTimers[$device->id]);
        }

        // Clean up old connection if exists
        if (isset($this->deviceConnections[$device->device_key])) {
            $oldConn = $this->deviceConnections[$device->device_key];
            unset($this->connectionToDevice[$oldConn->id]);
            unset($this->deviceConnections[$device->device_key]);
        }

        // Clean up device IP
        unset($this->deviceIPs[$device->id]);
    }

    // Add new method for restarting ping timer
    protected function restartPingTimer($device)
    {
        // Stop existing timer if any
        if (isset($this->pingTimers[$device->id])) {
            Timer::del($this->pingTimers[$device->id]);
            unset($this->pingTimers[$device->id]);
        }

        // Start new ping timer
        $timer_id = Timer::add(10, function() use ($device) {
            if (isset($this->deviceConnections[$device->device_key])) {
                $connection = $this->deviceConnections[$device->device_key];
                
                try {
                    $connection->send(json_encode([
                        'type' => 'ping',
                        'timestamp' => time()
                    ]));
                    \Log::debug("Sent ping to {$device->name}");
                    
                    // Check last online time
                    $lastOnline = $device->last_online;
                    $now = now();
                    
                    if ($lastOnline && $now->diffInSeconds($lastOnline) > 30) {
                        \Log::info("Device {$device->name} considered offline - no pong for 30 seconds");
                        $this->cleanupDeviceConnection($device);
                        $device->update(['is_online' => false]);
                        $this->broadcastDeviceStatus($device);
                    }
                } catch (\Exception $e) {
                    \Log::error("Error sending ping to {$device->name}: " . $e->getMessage());
                    $this->cleanupDeviceConnection($device);
                }
            }
        }, [], true);

        $this->pingTimers[$device->id] = $timer_id;
        \Log::info("Started ping timer for {$device->name}");
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