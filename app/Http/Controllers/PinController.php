<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Pin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Models\PinTemplate;
use App\Services\TelegramService;

class PinController extends Controller
{
    public function index()
    {
        $pins = Pin::with('device')->get();
        return view('pins.index', compact('pins'));
    }

    public function charts()
    {
        $pins = Pin::with('device')->get();
        return view('pins.charts', compact('pins'));
    }

    public function create(Device $device)
    {
        $templates = collect(PinTemplate::getAvailableTemplates())
            ->map(function ($template) {
                return (object) $template;
            });
        return view('pins.create', compact('device', 'templates'));
    }

    public function store(Request $request, Device $device)
    {
        try {
            \Log::info('Creating new pin:', $request->all());
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'pin_number' => 'required|integer|min:0|max:39',
                'type' => 'required|string|in:' . implode(',', array_keys(Pin::types())),
                'settings' => 'nullable|array',
                'settings.calibration' => 'nullable|array',
                'settings.calibration.4' => 'nullable|numeric',
                'settings.calibration.7' => 'nullable|numeric',
                'settings.calibration.10' => 'nullable|numeric',
                'settings.samples' => 'nullable|integer|min:1',
                'settings.interval' => 'nullable|integer|min:100',
                'is_active' => 'boolean'
            ]);

            // Set default value for is_active if not provided
            $validated['is_active'] = $validated['is_active'] ?? true;
            $validated['device_id'] = $device->id;

            \Log::info('Validated data for pH sensor:', [
                'type' => $validated['type'],
                'settings' => $validated['settings'] ?? [],
                'calibration' => $validated['settings']['calibration'] ?? 'No calibration data'
            ]);

            // Check if pin number is already in use for this device
            if ($device->pins()->where('pin_number', $validated['pin_number'])->exists()) {
                \Log::warning('Pin number already in use:', [
                    'device_id' => $device->id,
                    'pin_number' => $validated['pin_number']
                ]);
                
                if ($request->wantsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Pin number is already in use on this device'
                    ], 422);
                }
                return back()->withInput()->withErrors(['pin_number' => 'Pin number is already in use']);
            }

            $pin = Pin::create($validated);
            $pin->load('device'); // Eager load the device relationship

            \Log::info('Pin created successfully:', [
                'pin_id' => $pin->id,
                'device_id' => $device->id
            ]);

            // Send pH sensor configuration if applicable
            if ($pin->type === 'ph_sensor' && $device->is_online) {
                try {
                    $webSocketHandler = app(\App\WebSocket\WebSocketHandler::class);
                    
                    // Get calibration values from validated data
                    $calibration = [
                        '4' => floatval($validated['settings']['calibration']['4'] ?? 3300),
                        '7' => floatval($validated['settings']['calibration']['7'] ?? 2048),
                        '10' => floatval($validated['settings']['calibration']['10'] ?? 1024)
                    ];
                    
                    \Log::info("pH Sensor Calibration Values:", $calibration);
                    
                    $configMessage = [
                        'type' => 'pin_config',
                        'pin' => $pin->pin_number,
                        'settings' => [
                            'type' => 'ph_sensor',
                            'samples' => intval($validated['settings']['samples'] ?? 10),
                            'interval' => intval($validated['settings']['interval'] ?? 1000),
                            'calibration' => $calibration
                        ]
                    ];
                    
                    \Log::info("Sending pH sensor config:", $configMessage);
                    $webSocketHandler->sendToDevice($device, json_encode($configMessage));
                } catch (\Exception $e) {
                    \Log::error("Error sending pH sensor config: " . $e->getMessage());
                }
            }

            if ($request->wantsJson()) {
                $response = [
                    'status' => 'success',
                    'message' => 'Pin created successfully',
                    'pin' => [
                        'id' => $pin->id,
                        'name' => $pin->name,
                        'pin_number' => $pin->pin_number,
                        'type' => $pin->type,
                        'is_active' => $pin->is_active,
                        'device' => [
                            'id' => $pin->device->id,
                            'name' => $pin->device->name,
                            'device_key' => $pin->device->device_key
                        ]
                    ]
                ];
                
                \Log::debug('Pin creation response:', $response);
                return response()->json($response);
            }

            return redirect()->route('devices.show', $device)
                ->with('success', 'Pin created successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error:', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Pin creation error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to create pin',
                    'error' => $e->getMessage()
                ], 500);
            }
            return back()->withInput()->with('error', 'Failed to create pin');
        }
    }

    public function edit(Device $device, Pin $pin)
    {
        return view('pins.edit', compact('device', 'pin'));
    }

    public function update(Request $request, Device $device, Pin $pin)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pin_number' => 'required|integer|min:0|max:40',
            'type' => 'required|string|in:digital_input,digital_output,analog_input,analog_output,ph_sensor',
            'description' => 'nullable|string',
            'settings' => 'nullable|array',
            'settings.alerts' => 'nullable|array',
            'settings.alerts.enabled' => 'nullable|boolean',
            'settings.alerts.telegram_chat_id' => 'nullable|string',
            'settings.alerts.message_template' => 'nullable|string',
            'settings.alerts.min_threshold' => 'nullable|numeric',
            'settings.alerts.max_threshold' => 'nullable|numeric',
            'settings.alerts.alert_below_min' => 'nullable|boolean',
            'settings.alerts.alert_above_max' => 'nullable|boolean',
            'settings.alerts.on_high' => 'nullable|boolean',
            'settings.alerts.on_low' => 'nullable|boolean',
            'settings.alerts.on_turn_on' => 'nullable|boolean',
            'settings.alerts.on_turn_off' => 'nullable|boolean',
            'settings.schedule' => 'nullable|array',
            'settings.schedule.enabled' => 'nullable|boolean',
            'settings.schedule.on' => 'nullable|string',
            'settings.schedule.duration' => 'nullable|integer|min:1',
            'settings.schedule.interval' => 'nullable|integer|min:1',
            'settings.schedule.cycle_duration' => 'nullable|integer|min:1',
            'settings.schedule.repeat_hourly' => 'nullable|boolean',
            'settings.schedule.hourly_interval' => 'nullable|integer|min:1',
            'settings.calibration' => 'nullable|array',
            'settings.calibration.4' => 'nullable|numeric',
            'settings.calibration.7' => 'nullable|numeric',
            'settings.calibration.10' => 'nullable|numeric',
            'settings.samples' => 'nullable|integer|min:1',
            'settings.interval' => 'nullable|integer|min:100',
        ]);

        \Log::info('Updating pin with data:', [
            'pin_id' => $pin->id,
            'type' => $validated['type'],
            'settings' => $validated['settings'] ?? [],
            'calibration' => $validated['settings']['calibration'] ?? 'No calibration data'
        ]);

        // Clean up schedule settings if not enabled
        if (!isset($validated['settings']['schedule']['enabled']) || !$validated['settings']['schedule']['enabled']) {
            if (isset($validated['settings']['schedule'])) {
                $validated['settings']['schedule'] = ['enabled' => false];
            }
        }

        // If repeat hourly is not enabled, remove hourly interval
        if (!isset($validated['settings']['schedule']['repeat_hourly']) || !$validated['settings']['schedule']['repeat_hourly']) {
            if (isset($validated['settings']['schedule']['hourly_interval'])) {
                unset($validated['settings']['schedule']['hourly_interval']);
            }
        }

        // Clean up alert settings if not enabled
        if (!isset($validated['settings']['alerts']['enabled']) || !$validated['settings']['alerts']['enabled']) {
            if (isset($validated['settings']['alerts'])) {
                $validated['settings']['alerts'] = ['enabled' => false];
            }
        }

        $pin->update($validated);

        // Jika pin adalah pH sensor, kirim konfigurasi baru ke ESP32
        if ($pin->type === 'ph_sensor' && $device->is_online) {
            try {
                $webSocketHandler = app(\App\WebSocket\WebSocketHandler::class);
                
                // Get calibration values from validated data
                $calibration = [
                    '4' => floatval($validated['settings']['calibration']['4'] ?? 3300),
                    '7' => floatval($validated['settings']['calibration']['7'] ?? 2048),
                    '10' => floatval($validated['settings']['calibration']['10'] ?? 1024)
                ];
                
                \Log::info("pH Sensor Calibration Values for update:", $calibration);
                
                $configMessage = [
                    'type' => 'pin_config',
                    'pin' => $pin->pin_number,
                    'settings' => [
                        'type' => 'ph_sensor',
                        'samples' => intval($validated['settings']['samples'] ?? 10),
                        'interval' => intval($validated['settings']['interval'] ?? 1000),
                        'calibration' => $calibration
                    ]
                ];
                
                \Log::info("Sending updated pH sensor config:", $configMessage);
                $webSocketHandler->sendToDevice($device, json_encode($configMessage));
            } catch (\Exception $e) {
                \Log::error("Error sending pH sensor config: " . $e->getMessage());
            }
        }

        return redirect()->route('devices.show', $device)
            ->with('success', 'Pin updated successfully.');
    }

    public function destroy(Device $device, Pin $pin)
    {
        try {
            // Kirim pesan ke ESP32 untuk membersihkan konfigurasi pin
            if ($device->is_online) {
                $webSocketHandler = app(\App\WebSocket\WebSocketHandler::class);
                $cleanupMessage = [
                    'type' => 'cleanup_pin',
                    'pin' => $pin->pin_number
                ];
                $webSocketHandler->sendToDevice($device, json_encode($cleanupMessage));
            }

            // Hapus pin dari database
            $pin->delete();

            return redirect()->route('devices.show', $device)
                ->with('success', 'Pin deleted successfully and cleaned up from ESP32.');
        } catch (\Exception $e) {
            \Log::error('Error deleting pin: ' . $e->getMessage());
            return redirect()->route('devices.show', $device)
                ->with('error', 'Failed to delete pin. Please try again.');
        }
    }

    public function updateStatus(Request $request, Device $device, Pin $pin)
    {
        $validated = $request->validate([
            'value' => 'required|numeric',
            'is_active' => 'required|boolean',
        ]);

        // Get old value before update
        $oldValue = $pin->value;

        // Update pin status
        $pin->update([
            'value' => $validated['value'],
            'is_active' => $validated['is_active'],
            'last_update' => now(),
        ]);

        // Update device status
        $device->update([
            'is_online' => true,
            'last_online' => now()
        ]);

        // Check if alerts are enabled and configured
        if (isset($pin->settings['alerts']) && 
            isset($pin->settings['alerts']['enabled']) && 
            $pin->settings['alerts']['enabled'] && 
            isset($pin->settings['alerts']['telegram_chat_id'])) {
            
            $telegramService = app(TelegramService::class);
            $chatId = $pin->settings['alerts']['telegram_chat_id'];
            
            // For digital output pins
            if ($pin->type === 'digital_output') {
                // Alert when turned ON
                if ($oldValue == 0 && $validated['value'] == 1 && 
                    isset($pin->settings['alerts']['on_turn_on']) && 
                    $pin->settings['alerts']['on_turn_on']) {
                    
                    $message = $this->formatAlertMessage($pin, $device, 'ON');
                    $telegramService->sendMessage($chatId, $message);
                }
                
                // Alert when turned OFF
                if ($oldValue == 1 && $validated['value'] == 0 && 
                    isset($pin->settings['alerts']['on_turn_off']) && 
                    $pin->settings['alerts']['on_turn_off']) {
                    
                    $message = $this->formatAlertMessage($pin, $device, 'OFF');
                    $telegramService->sendMessage($chatId, $message);
                }
            }
            // For analog input pins (including pH sensor)
            else if ($pin->type === 'analog_input' || $pin->type === 'ph_sensor') {
                $value = $validated['value'];
                
                // Check min threshold
                if (isset($pin->settings['alerts']['min_threshold']) && 
                    isset($pin->settings['alerts']['alert_below_min']) && 
                    $pin->settings['alerts']['alert_below_min']) {
                    
                    $minThreshold = $pin->settings['alerts']['min_threshold'];
                    if ($oldValue >= $minThreshold && $value < $minThreshold) {
                        $message = $this->formatAlertMessage($pin, $device, "BELOW MINIMUM", [
                            'value' => $value,
                            'threshold' => $minThreshold
                        ]);
                        $telegramService->sendMessage($chatId, $message);
                    }
                }
                
                // Check max threshold
                if (isset($pin->settings['alerts']['max_threshold']) && 
                    isset($pin->settings['alerts']['alert_above_max']) && 
                    $pin->settings['alerts']['alert_above_max']) {
                    
                    $maxThreshold = $pin->settings['alerts']['max_threshold'];
                    if ($oldValue <= $maxThreshold && $value > $maxThreshold) {
                        $message = $this->formatAlertMessage($pin, $device, "ABOVE MAXIMUM", [
                            'value' => $value,
                            'threshold' => $maxThreshold
                        ]);
                        $telegramService->sendMessage($chatId, $message);
                    }
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pin status updated successfully',
            'data' => $pin
        ]);
    }

    private function formatAlertMessage($pin, $device, $status, $data = [])
    {
        $template = $pin->settings['alerts']['message_template'] ?? null;
        
        if (!$template) {
            // Default templates based on pin type
            if ($pin->type === 'digital_output') {
                $template = "🔔 {device_name}\n📍 {pin_name} is now {status}\n⏰ {time}";
            } else {
                $template = "⚠️ Alert: {pin_name}\n" .
                           "📍 Device: {device_name}\n" .
                           "📊 Value: {value}\n" .
                           "🎯 Threshold: {threshold}\n" .
                           "⏰ Time: {time}\n" .
                           "📅 Date: {date}";
            }
        }

        // Set timezone to Asia/Tokyo to match user's laptop
        $time = now()->setTimezone('Asia/Tokyo');

        $replacements = array_merge([
            'device_name' => $device->name,
            'pin_name' => $pin->name,
            'status' => $status,
            'time' => $time->format('H:i'),
            'date' => $time->format('Y-m-d')
        ], $data);

        return str_replace(
            array_map(fn($key) => '{' . $key . '}', array_keys($replacements)),
            array_values($replacements),
            $template
        );
    }

    public function chart(Device $device, Pin $pin)
    {
        // This method will be used to get chart data for a specific pin
        $range = request('range', 'day');
        
        // Sample data - replace with actual data from your database
        $data = [
            'timestamps' => [],
            'values' => [],
            'stats' => [
                'avg' => 0,
                'min' => 0,
                'max' => 0
            ]
        ];

        return response()->json($data);
    }

    public function getChartData(Request $request, Pin $pin)
    {
        try {
            if (!$pin || !$pin->exists) {
                return response()->json([
                    'error' => 'Pin not found',
                    'message' => 'The requested pin does not exist'
                ], 404);
            }

            \Log::info('Getting chart data for pin', [
                'pin_id' => $pin->id,
                'range' => $request->input('range'),
                'pin_type' => $pin->type,
                'pin_exists' => $pin->exists
            ]);

            $range = $request->input('range', 'day');
            
            $startDate = match($range) {
                'day' => now()->subDay(),
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                default => now()->subDay()
            };

            $logs = $pin->logs()
                ->where('created_at', '>=', $startDate)
                ->orderBy('created_at')
                ->get();

            \Log::info('Retrieved logs for pin', [
                'pin_id' => $pin->id,
                'log_count' => $logs->count(),
                'start_date' => $startDate->format('Y-m-d H:i:s')
            ]);

            if ($logs->isEmpty()) {
                return response()->json([
                    'timestamps' => [],
                    'values' => [],
                    'stats' => [
                        'avg' => 0,
                        'min' => 0,
                        'max' => 0
                    ]
                ]);
            }

            $data = [
                'timestamps' => $logs->pluck('created_at')->map(fn($date) => $date->format('Y-m-d H:i:s')),
                'values' => $logs->pluck('value'),
                'stats' => [
                    'avg' => round($logs->avg('value') ?? 0, 2),
                    'min' => round($logs->min('value') ?? 0, 2),
                    'max' => round($logs->max('value') ?? 0, 2)
                ]
            ];

            return response()->json($data);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Pin not found', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Pin not found',
                'message' => 'The requested pin does not exist'
            ], 404);
            
        } catch (\Exception $e) {
            \Log::error('Error getting chart data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to get chart data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 