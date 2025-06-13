<?php

namespace App\Services\Device;

use App\Models\Device;
use App\Services\DeviceStatusService;
use Illuminate\Support\Facades\Log;
use Workerman\Timer;
use App\Services\Device\Traits\WebSocketLoggingTrait;

class DevicePingService
{
    use WebSocketLoggingTrait;
    
    protected $activeTimers = [];
    protected $pingAttempts = [];
    protected $maxInactiveTime = 60; // 60 seconds before stopping ping
    protected $deviceStatusService;

    public function __construct(DeviceStatusService $deviceStatusService)
    {
        $this->deviceStatusService = $deviceStatusService;
    }

    public function startPinging(Device $device, string $ipAddress)
    {
        if (empty($ipAddress)) {
            Log::warning("Cannot start pinging device without IP address", ['device_id' => $device->id]);
            return;
        }

        // Stop existing ping if any
        $this->stopPinging($device);

        $message = "🎯 Starting Ping Service | Device: {$device->device_key} | IP: {$ipAddress}";
        echo "\n{$message}";
        Log::info($message);

        // Initialize ping attempts counter
        $this->pingAttempts[$device->id] = [
            'last_success' => time(),
            'ip_address' => $ipAddress
        ];

        // Create a new timer for this device
        $this->activeTimers[$device->id] = Timer::add(1, function() use ($device, $ipAddress) {
            $this->pingDevice($device, $ipAddress);
        });
    }

    protected function pingDevice(Device $device, string $ipAddress)
    {
        if (!isset($this->pingAttempts[$device->id])) {
            $this->stopPinging($device);
            return;
        }

        // Execute ping command
        $pingResult = $this->executePing($ipAddress);
        $currentTime = time();
        
        // Log ping result
        $this->logPingResult($device, $pingResult, $ipAddress);
        
        if ($pingResult) {
            // Ping successful
            $this->pingAttempts[$device->id]['last_success'] = $currentTime;
            
            if (!$device->is_online) {
                $this->deviceStatusService->markOnline($device);
                $this->logDeviceStatus($device, true, $ipAddress);
            }
        } else {
            // Ping failed
            if ($device->is_online) {
                $this->deviceStatusService->markOffline($device);
                $this->logDeviceStatus($device, false, $ipAddress);
            }
        }

        // Check if we should stop pinging
        $lastSuccess = $this->pingAttempts[$device->id]['last_success'];
        if (($currentTime - $lastSuccess) > $this->maxInactiveTime) {
            $message = "⏹️ Stopping Ping Service | " .
                      "Device: {$device->device_key} | " .
                      "IP: {$ipAddress} | " .
                      "Inactive Time: " . ($currentTime - $lastSuccess) . "s";
            echo "\n{$message}";
            Log::info($message);
            
            $this->stopPinging($device);
        }
    }

    protected function executePing(string $ip): bool
    {
        // Adjust ping command based on OS
        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = "ping -n 1 -w 1000 " . escapeshellarg($ip);
        } else {
            $cmd = "ping -c 1 -W 1 " . escapeshellarg($ip);
        }

        exec($cmd, $output, $returnCode);
        return $returnCode === 0;
    }

    protected function logPingResult(Device $device, bool $success, string $ipAddress)
    {
        $status = $success ? "successful" : "failed";
        Log::debug("Ping {$status} for device {$device->name} ({$ipAddress})");
    }

    protected function logDeviceStatus(Device $device, bool $isOnline, string $ipAddress)
    {
        $status = $isOnline ? "online" : "offline";
        Log::info("Device {$device->name} is now {$status} ({$ipAddress})");
    }

    public function stopPinging(Device $device)
    {
        if (isset($this->activeTimers[$device->id])) {
            Timer::del($this->activeTimers[$device->id]);
            unset($this->activeTimers[$device->id]);
            unset($this->pingAttempts[$device->id]);
            
            // Mark device as offline when stopping ping
            $this->deviceStatusService->markOffline($device);
            
            $message = "⏹️ Ping Service Stopped | Device: {$device->device_key}";
            echo "\n{$message}";
            Log::info($message);
        }
    }

    public function isBeingPinged(Device $device): bool
    {
        return isset($this->activeTimers[$device->id]);
    }
} 