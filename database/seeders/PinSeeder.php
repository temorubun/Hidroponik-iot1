<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pin;
use App\Models\Device;

class PinSeeder extends Seeder
{
    public function run(): void
    {
        // Get all devices
        $devices = Device::all();

        foreach ($devices as $device) {
            // Create pH sensor
            Pin::create([
                'device_id' => $device->id,
                'name' => 'pH Sensor',
                'pin_number' => 34,
                'type' => 'ph_sensor',
                'settings' => [
                    'samples' => 10,
                    'interval' => 1000,
                    'calibration' => [
                        '4' => 3300,
                        '7' => 2048,
                        '10' => 1024
                    ]
                ],
                'is_active' => true
            ]);

            // Create analog input
            Pin::create([
                'device_id' => $device->id,
                'name' => 'Water Level',
                'pin_number' => 35,
                'type' => 'analog_input',
                'settings' => [
                    'samples' => 10,
                    'interval' => 1000
                ],
                'is_active' => true
            ]);

            // Create digital output for pump
            Pin::create([
                'device_id' => $device->id,
                'name' => 'Water Pump',
                'pin_number' => 26,
                'type' => 'digital_output',
                'settings' => [
                    'schedule' => [
                        'enabled' => true,
                        'on' => '08:00',
                        'duration' => 30,
                        'interval' => 120
                    ]
                ],
                'is_active' => true
            ]);
        }
    }
} 