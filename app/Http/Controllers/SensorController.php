<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Device;

class SensorController extends Controller
{
    private function validateDeviceKey(Request $request)
    {
        $deviceKey = $request->header('X-Device-Key');
        if (!$deviceKey) {
            return false;
        }

        $device = Device::where('device_key', $deviceKey)->first();
        return $device ? $device->id : false;
    }

    public function sendData(Request $request)
    {
        $deviceId = $this->validateDeviceKey($request);
        if (!$deviceId) {
            return response()->json(['status' => 'error', 'message' => 'Invalid device key'], 401);
        }

        $data = [
            'temperature' => $request->input('temperature', 0),
            'humidity' => $request->input('humidity', 0),
            'timestamp' => now()->toDateTimeString()
        ];
        
        // Update sensor data dengan device ID
        Cache::put("sensor_data_{$deviceId}", $data, 3600);
        
        // Update device last seen
        $this->updateDeviceStatus($deviceId);
        
        return response()->json(['status' => 'success']);
    }

    public function heartbeat(Request $request)
    {
        $deviceId = $this->validateDeviceKey($request);
        if (!$deviceId) {
            return response()->json(['status' => 'error', 'message' => 'Invalid device key'], 401);
        }

        $this->updateDeviceStatus($deviceId);
        return response()->json(['status' => 'success']);
    }

    private function updateDeviceStatus($deviceId)
    {
        $status = [
            'last_seen' => now()->toDateTimeString(),
            'is_online' => true
        ];
        
        // Simpan status dengan timeout 30 detik
        Cache::put("device_status_{$deviceId}", $status, 30);
        
        // Update status di database
        Device::where('id', $deviceId)->update([
            'last_online' => now(),
            'is_online' => true
        ]);
    }

    public function stream(Request $request)
    {
        // Dapatkan device ID dari parameter
        $deviceId = $request->query('device_id');
        if (!$deviceId) {
            return response()->json(['status' => 'error', 'message' => 'Device ID required'], 400);
        }

        return response()->stream(function() use ($deviceId) {
            while (true) {
                // Get sensor data untuk device spesifik
                $data = Cache::get("sensor_data_{$deviceId}", []);
                
                // Get device status
                $status = Cache::get("device_status_{$deviceId}", [
                    'is_online' => false,
                    'last_seen' => null
                ]);

                // Jika tidak ada status di cache, cek database
                if (empty($status)) {
                    $device = Device::find($deviceId);
                    if ($device) {
                        $status = [
                            'is_online' => false, // Default false karena tidak ada heartbeat
                            'last_seen' => $device->last_online ? $device->last_online->toDateTimeString() : null
                        ];
                    }
                }
                
                // Combine data
                $response = array_merge($data, $status);
                
                echo "data: " . json_encode($response) . "\n\n";
                ob_flush();
                flush();
                sleep(1);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no'
        ]);
    }
} 