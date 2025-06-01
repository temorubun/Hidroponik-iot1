<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class DeviceApiController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function updateStatus(Request $request)
    {
        \Log::info('Received pin update request:', $request->all());

        $validated = $request->validate([
            'device_key' => 'required|string',
            'pins' => 'required|array',
            'pins.*.pin_number' => 'required|integer',
            'pins.*.value' => 'required|numeric',
            'is_active' => 'required|boolean',
            'send_notification' => 'boolean'
        ]);

        $device = Device::where('device_key', $validated['device_key'])->firstOrFail();
        
        \Log::info("Updating device status: {$device->name}");
        
        $device->update([
            'is_online' => true,
            'last_online' => now(),
        ]);

        $results = [];
        foreach ($validated['pins'] as $pinData) {
            $pin = $device->pins()->where('pin_number', $pinData['pin_number'])->first();
            
            if ($pin) {
                $oldValue = $pin->value;
                
                \Log::info("Processing pin update:", [
                    'pin_name' => $pin->name,
                    'pin_type' => $pin->type,
                    'old_value' => $oldValue,
                    'new_value' => $pinData['value']
                ]);

                // Update pin value
                $pin->update([
                    'value' => $pinData['value'],
                    'is_active' => $validated['is_active'],
                    'last_update' => now()
                ]);

                // Kirim notifikasi jika flag send_notification true
                if ($request->input('send_notification', false)) {
                    try {
                        // Ambil chat ID dari pengaturan pin atau gunakan default
                        $chatId = $pin->settings['alerts']['telegram_chat_id'] ?? env('TELEGRAM_DEFAULT_CHAT_ID');
                        
                        if ($chatId) {
                            $message = "🔄 *Pin Update Notification*\n\n" .
                                     "📍 Device: {$device->name}\n" .
                                     "🔌 Pin: {$pin->name}\n" .
                                     "📊 Old Value: {$oldValue}\n" .
                                     "📈 New Value: {$pinData['value']}\n" .
                                     "⏰ Time: " . now()->setTimezone('Asia/Jakarta')->format('H:i:s') . "\n" .
                                     "📅 Date: " . now()->setTimezone('Asia/Jakarta')->format('Y-m-d');

                            $result = $this->telegramService->sendMessage($chatId, $message);
                            \Log::info("Update notification sent:", ['success' => $result]);
                        } else {
                            \Log::warning("No Telegram chat ID configured for notifications");
                        }
                    } catch (\Exception $e) {
                        \Log::error("Failed to send update notification:", [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }

                // Cek kondisi alert untuk pH sensor jika diperlukan
                if ($pin->type === 'ph_sensor' && isset($pin->settings['alerts']['enabled']) && $pin->settings['alerts']['enabled']) {
                    $this->checkAndSendPhAlerts($pin, $device, $pinData['value']);
                }

                $results[] = [
                    'pin_number' => $pin->pin_number,
                    'name' => $pin->name,
                    'value' => $pin->value,
                    'is_active' => $pin->is_active,
                    'last_update' => $pin->last_update
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pin status updated successfully',
            'data' => $results
        ]);
    }

    protected function shouldSendAlert($oldValue, $newValue, $threshold, $condition)
    {
        switch ($condition) {
            case 'above':
                return $oldValue <= $threshold && $newValue > $threshold;
            case 'below':
                return $oldValue >= $threshold && $newValue < $threshold;
            default:
                return false;
        }
    }

    private function formatSensorAlert($pin, $device, $value, $threshold, $type)
    {
        $unit = '';
        $valueFormatted = $value;
        
        // Format value and add unit based on sensor type
        switch ($pin->type) {
            case 'ph_sensor':
                $unit = 'pH';
                $valueFormatted = number_format($value, 1);
                break;
            case 'analog_input':
                if (strpos(strtolower($pin->name), 'temp') !== false) {
                    $unit = '°C';
                    $valueFormatted = number_format($value, 1);
                } else if (strpos(strtolower($pin->name), 'humid') !== false) {
                    $unit = '%';
                    $valueFormatted = round($value);
                }
                break;
        }
        
        $message = "⚠️ *Alert: {$pin->name}*\n\n";
        $message .= "📍 Device: {$device->name}\n";
        $message .= "📊 Current Value: {$valueFormatted}{$unit}\n";
        $message .= "🎯 Threshold: {$threshold}{$unit}\n";
        $message .= "❗ Status: " . ($type === 'below' ? "Below minimum" : "Above maximum") . "\n";
        $message .= "⏰ Time: " . now()->setTimezone('Asia/Tokyo')->format('H:i') . "\n";
        $message .= "📅 Date: " . now()->setTimezone('Asia/Tokyo')->format('Y-m-d');
        
        return $message;
    }
    
    private function formatDigitalAlert($pin, $device, $status)
    {
        return "🔔 *{$device->name}*\n" .
               "📍 {$pin->name} is now *{$status}*\n" .
               "⏰ " . now()->setTimezone('Asia/Tokyo')->format('H:i');
    }

    // Tambahkan method baru untuk menangani alert pH
    private function checkAndSendPhAlerts($pin, $device, $value)
    {
        try {
            $chatId = $pin->settings['alerts']['telegram_chat_id'] ?? env('TELEGRAM_DEFAULT_CHAT_ID');
            if (!$chatId) return;

            $value = floatval($value);
            
            // Check minimum threshold
            if (isset($pin->settings['alerts']['min_threshold']) && 
                isset($pin->settings['alerts']['alert_below_min']) && 
                $pin->settings['alerts']['alert_below_min']) {
                
                $minThreshold = floatval($pin->settings['alerts']['min_threshold']);
                if ($value < $minThreshold) {
                    $message = "⚠️ *pH Alert!*\n\n" .
                              "📍 Device: {$device->name}\n" .
                              "📊 Current pH: {$value}\n" .
                              "🔻 Min Threshold: {$minThreshold}\n" .
                              "❗ Status: Below minimum\n" .
                              "⏰ Time: " . now()->setTimezone('Asia/Jakarta')->format('H:i:s') . "\n" .
                              "📅 Date: " . now()->setTimezone('Asia/Jakarta')->format('Y-m-d');
                    
                    $this->telegramService->sendMessage($chatId, $message);
                }
            }

            // Check maximum threshold
            if (isset($pin->settings['alerts']['max_threshold']) && 
                isset($pin->settings['alerts']['alert_above_max']) && 
                $pin->settings['alerts']['alert_above_max']) {
                
                $maxThreshold = floatval($pin->settings['alerts']['max_threshold']);
                if ($value > $maxThreshold) {
                    $message = "⚠️ *pH Alert!*\n\n" .
                              "📍 Device: {$device->name}\n" .
                              "📊 Current pH: {$value}\n" .
                              "🔺 Max Threshold: {$maxThreshold}\n" .
                              "❗ Status: Above maximum\n" .
                              "⏰ Time: " . now()->setTimezone('Asia/Jakarta')->format('H:i:s') . "\n" .
                              "📅 Date: " . now()->setTimezone('Asia/Jakarta')->format('Y-m-d');
                    
                    $this->telegramService->sendMessage($chatId, $message);
                }
            }
        } catch (\Exception $e) {
            \Log::error("Error sending pH alert: " . $e->getMessage());
        }
    }
} 