<?php

namespace App\WebSocket\Handlers;

use Workerman\Connection\TcpConnection;
use App\Models\Device;
use App\Services\TelegramService;

class SensorHandler
{
    public function handle(TcpConnection $from, array $data, Device $device)
    {
        \Log::info("Received sensor data:", $data);
        
        $pinNumber = $data['pin'] ?? null;
        if (!$pinNumber) {
            \Log::error("No pin number in sensor data");
            return false;
        }
        
        $pin = $device->pins()->where('pin_number', $pinNumber)->first();
        if (!$pin) {
            \Log::error("Pin not found: {$pinNumber}");
            return false;
        }
        
        if ($pin->type === 'ph_sensor' && isset($data['value'])) {
            $phValue = floatval($data['value']);
            
            \Log::info("Processing pH value from pin {$pinNumber}: {$phValue}", [
                'raw_value' => $data['value'],
                'device_key' => $device->device_key,
                'pin_type' => $pin->type
            ]);
            
            $pin->update([
                'value' => $phValue,
                'last_update' => now()
            ]);

            $this->checkAlertConditions($device, $pin, $phValue);
            return true;
        }
        
        \Log::info("No pH value in sensor data");
        return false;
    }

    protected function checkAlertConditions(Device $device, $pin, float $phValue)
    {
        if ($pin->settings && 
            isset($pin->settings['alerts']) && 
            isset($pin->settings['alerts']['enabled']) && 
            $pin->settings['alerts']['enabled'] && 
            isset($pin->settings['alerts']['telegram_chat_id'])) {
            
            $chatId = $pin->settings['alerts']['telegram_chat_id'];
            $telegramService = app(TelegramService::class);

            try {
                $this->checkMinThreshold($device, $pin, $phValue, $chatId, $telegramService);
                $this->checkMaxThreshold($device, $pin, $phValue, $chatId, $telegramService);
            } catch (\Exception $e) {
                \Log::error("Error in pH alert processing:", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    protected function checkMinThreshold(Device $device, $pin, float $phValue, string $chatId, TelegramService $telegramService)
    {
        if (isset($pin->settings['alerts']['min_threshold']) && 
            isset($pin->settings['alerts']['alert_below_min']) && 
            $pin->settings['alerts']['alert_below_min']) {
            
            $minThreshold = floatval($pin->settings['alerts']['min_threshold']);
            
            if ($phValue < $minThreshold) {
                $message = "⚠️ *pH Alert!*\n" .
                         "*Device:* {$device->name}\n" .
                         "*Current pH:* {$phValue}\n" .
                         "*Min Threshold:* {$minThreshold}\n" .
                         "*Status:* Below minimum\n" .
                         "*Time:* " . now()->setTimezone('Asia/Jakarta')->format('H:i:s');
                
                $telegramService->sendMessage($chatId, $message);
            }
        }
    }

    protected function checkMaxThreshold(Device $device, $pin, float $phValue, string $chatId, TelegramService $telegramService)
    {
        if (isset($pin->settings['alerts']['max_threshold']) && 
            isset($pin->settings['alerts']['alert_above_max']) && 
            $pin->settings['alerts']['alert_above_max']) {
            
            $maxThreshold = floatval($pin->settings['alerts']['max_threshold']);
            
            if ($phValue > $maxThreshold) {
                $message = "⚠️ *pH Alert!*\n" .
                         "*Device:* {$device->name}\n" .
                         "*Current pH:* {$phValue}\n" .
                         "*Max Threshold:* {$maxThreshold}\n" .
                         "*Status:* Above maximum\n" .
                         "*Time:* " . now()->setTimezone('Asia/Jakarta')->format('H:i:s');
                
                $telegramService->sendMessage($chatId, $message);
            }
        }
    }
} 