<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;
use App\Models\Pin;

class PhSensorSeeder extends Seeder
{
    public function run()
    {
        $user = \App\Models\User::first();
        if (!$user) {
            $user = \App\Models\User::create([
                'name' => 'Agung',
                'email' => 'agung@gmail.com',
                'password' => bcrypt('agung123')
            ]);
        }

        $device = Device::firstOrCreate(
            ['device_key' => '3xx0Ek7JFezzW6FrDNpfLv0oh0zdTMvK'],
            [
                'user_id' => $user->id,
                'name' => 'ESP32',
                'description' => 'ESP32 Development Board',
                'is_online' => true,
                'last_online' => now()
            ]
        );

        Pin::create([
            'device_id' => $device->id,
            'name' => 'pH Sensor',
            'pin_number' => 33,
            'type' => 'ph_sensor',
            'is_active' => true,
            'settings' => [
                'alerts' => [
                    'enabled' => true,
                    'min_threshold' => 6.0,
                    'max_threshold' => 8.0,
                    'alert_below_min' => true,
                    'alert_above_max' => true
                ],
                'calibration' => [
                    '4' => 4090,
                    '7' => 3140,
                    '10' => 2350
                ],
                'samples' => 10,
                'interval' => 1000
            ]
        ]);
    }
} 