<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\Pin;
use App\Models\Project;
use Illuminate\Database\Seeder;

class PinTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Get first project from database
        $project = Project::first();
        
        if (!$project) {
            \Log::warning('No project found for PinTemplateSeeder');
            return;
        }

        $this->createHydroponicSystem('Hidroponik NFT', 'Sistem NFT untuk sayuran daun', $project);
        $this->createHydroponicSystem('DFT System', 'Deep Flow Technique untuk tomat dan paprika', $project);
        $this->createAquaponicSystem('Aquaponik-1', 'Sistem aquaponik untuk ikan lele', $project);
    }

    private function createHydroponicSystem($name, $description, $project)
    {
        $device = Device::create([
            'user_id' => $project->user_id,
            'project_id' => $project->id,
            'name' => $name,
            'description' => $description,
            'device_key' => 'template_' . \Illuminate\Support\Str::random(32),
            'is_online' => true,
            'last_online' => now(),
        ]);

        // Grow Light
        Pin::create([
            'device_id' => $device->id,
            'name' => 'Grow Light',
            'pin_number' => 4,
            'type' => 'digital_output',
            'is_active' => true,
            'value' => 1,
            'settings' => [
                'icon' => 'lightbulb',
                'description' => 'LED Grow Light 50W',
                'schedule' => [
                    'on' => '06:00',
                    'off' => '18:00'
                ],
                'power' => '50W',
                'coverage' => '1m²'
            ]
        ]);

        // Water Pump
        Pin::create([
            'device_id' => $device->id,
            'name' => 'Water Pump',
            'pin_number' => 5,
            'type' => 'digital_output',
            'is_active' => true,
            'value' => 1,
            'settings' => [
                'icon' => 'water',
                'description' => 'Pompa Air 25W',
                'schedule' => [
                    'interval' => 15,
                    'duration' => 5
                ],
                'flow_rate' => '1500L/h',
                'power' => '25W'
            ]
        ]);

        // pH Sensor
        Pin::create([
            'device_id' => $device->id,
            'name' => 'pH Sensor',
            'pin_number' => 34,
            'type' => 'analog_input',
            'is_active' => true,
            'value' => 6.5,
            'settings' => [
                'icon' => 'vial',
                'description' => 'Sensor pH Meter',
                'alerts' => [
                    [
                        'condition' => 'below',
                        'threshold' => 5.5,
                        'message' => 'pH air terlalu asam!',
                        'telegram_chat_id' => '123456789'
                    ],
                    [
                        'condition' => 'above',
                        'threshold' => 7.5,
                        'message' => 'pH air terlalu basa!',
                        'telegram_chat_id' => '123456789'
                    ]
                ],
                'calibration' => [
                    'min_voltage' => 0,
                    'max_voltage' => 3.3,
                    'min_ph' => 0,
                    'max_ph' => 14
                ]
            ]
        ]);

        // TDS Sensor
        Pin::create([
            'device_id' => $device->id,
            'name' => 'TDS Sensor',
            'pin_number' => 35,
            'type' => 'analog_input',
            'is_active' => true,
            'value' => 850,
            'settings' => [
                'icon' => 'flask',
                'description' => 'Sensor PPM/EC',
                'alerts' => [
                    [
                        'condition' => 'below',
                        'threshold' => 500,
                        'message' => 'Kadar nutrisi terlalu rendah!',
                        'telegram_chat_id' => '123456789'
                    ],
                    [
                        'condition' => 'above',
                        'threshold' => 1500,
                        'message' => 'Kadar nutrisi terlalu tinggi!',
                        'telegram_chat_id' => '123456789'
                    ]
                ],
                'measurement_unit' => 'PPM',
                'conversion_factor' => 0.5
            ]
        ]);

        // Water Level Sensor
        Pin::create([
            'device_id' => $device->id,
            'name' => 'Water Level',
            'pin_number' => 32,
            'type' => 'analog_input',
            'is_active' => true,
            'value' => 75,
            'settings' => [
                'icon' => 'water',
                'description' => 'Sensor Level Air',
                'alerts' => [
                    [
                        'condition' => 'below',
                        'threshold' => 20,
                        'message' => 'Level air rendah!',
                        'telegram_chat_id' => '123456789'
                    ]
                ],
                'measurement_unit' => '%',
                'tank_capacity' => '50L'
            ]
        ]);

        // Temperature Sensor
        Pin::create([
            'device_id' => $device->id,
            'name' => 'Temperature',
            'pin_number' => 33,
            'type' => 'analog_input',
            'is_active' => true,
            'value' => 25.5,
            'settings' => [
                'icon' => 'temperature-half',
                'description' => 'Sensor Suhu Air',
                'alerts' => [
                    [
                        'condition' => 'above',
                        'threshold' => 30,
                        'message' => 'Suhu air terlalu tinggi!',
                        'telegram_chat_id' => '123456789'
                    ],
                    [
                        'condition' => 'below',
                        'threshold' => 20,
                        'message' => 'Suhu air terlalu rendah!',
                        'telegram_chat_id' => '123456789'
                    ]
                ],
                'measurement_unit' => '°C'
            ]
        ]);
    }

    private function createAquaponicSystem($name, $description, $project)
    {
        $device = Device::create([
            'user_id' => $project->user_id,
            'project_id' => $project->id,
            'name' => $name,
            'description' => $description,
            'device_key' => 'template_' . \Illuminate\Support\Str::random(32),
            'is_online' => true,
            'last_online' => now(),
        ]);

        // Air Pump
        Pin::create([
            'device_id' => $device->id,
            'name' => 'Air Pump',
            'pin_number' => 4,
            'type' => 'digital_output',
            'is_active' => true,
            'value' => 1,
            'settings' => [
                'icon' => 'wind',
                'description' => 'Pompa Udara 35W',
                'schedule' => [
                    'always_on' => true
                ],
                'air_flow' => '3000cc/min',
                'power' => '35W'
            ]
        ]);

        // Water Pump
        Pin::create([
            'device_id' => $device->id,
            'name' => 'Water Pump',
            'pin_number' => 5,
            'type' => 'digital_output',
            'is_active' => true,
            'value' => 1,
            'settings' => [
                'icon' => 'water',
                'description' => 'Pompa Air 40W',
                'schedule' => [
                    'interval' => 30,
                    'duration' => 15
                ],
                'flow_rate' => '2000L/h',
                'power' => '40W'
            ]
        ]);

        // Grow Light
        Pin::create([
            'device_id' => $device->id,
            'name' => 'Grow Light',
            'pin_number' => 16,
            'type' => 'digital_output',
            'is_active' => true,
            'value' => 1,
            'settings' => [
                'icon' => 'lightbulb',
                'description' => 'LED Grow Light 100W',
                'schedule' => [
                    'on' => '06:00',
                    'off' => '18:00'
                ],
                'power' => '100W',
                'coverage' => '2m²'
            ]
        ]);

        // Dissolved Oxygen Sensor
        Pin::create([
            'device_id' => $device->id,
            'name' => 'DO Sensor',
            'pin_number' => 34,
            'type' => 'analog_input',
            'is_active' => true,
            'value' => 6.8,
            'settings' => [
                'icon' => 'wind',
                'description' => 'Sensor Oksigen Terlarut',
                'alerts' => [
                    [
                        'condition' => 'below',
                        'threshold' => 5.0,
                        'message' => 'Kadar oksigen rendah!',
                        'telegram_chat_id' => '123456789'
                    ]
                ],
                'measurement_unit' => 'mg/L'
            ]
        ]);

        // pH Sensor
        Pin::create([
            'device_id' => $device->id,
            'name' => 'pH Sensor',
            'pin_number' => 35,
            'type' => 'analog_input',
            'is_active' => true,
            'value' => 7.0,
            'settings' => [
                'icon' => 'vial',
                'description' => 'Sensor pH Meter',
                'alerts' => [
                    [
                        'condition' => 'below',
                        'threshold' => 6.0,
                        'message' => 'pH air terlalu asam!',
                        'telegram_chat_id' => '123456789'
                    ],
                    [
                        'condition' => 'above',
                        'threshold' => 8.0,
                        'message' => 'pH air terlalu basa!',
                        'telegram_chat_id' => '123456789'
                    ]
                ]
            ]
        ]);

        // Temperature Sensor
        Pin::create([
            'device_id' => $device->id,
            'name' => 'Temperature',
            'pin_number' => 32,
            'type' => 'analog_input',
            'is_active' => true,
            'value' => 28.0,
            'settings' => [
                'icon' => 'temperature-half',
                'description' => 'Sensor Suhu Air',
                'alerts' => [
                    [
                        'condition' => 'above',
                        'threshold' => 32,
                        'message' => 'Suhu air terlalu tinggi!',
                        'telegram_chat_id' => '123456789'
                    ],
                    [
                        'condition' => 'below',
                        'threshold' => 25,
                        'message' => 'Suhu air terlalu rendah!',
                        'telegram_chat_id' => '123456789'
                    ]
                ],
                'measurement_unit' => '°C'
            ]
        ]);

        // Ammonia Sensor
        Pin::create([
            'device_id' => $device->id,
            'name' => 'Ammonia',
            'pin_number' => 33,
            'type' => 'analog_input',
            'is_active' => true,
            'value' => 0.25,
            'settings' => [
                'icon' => 'flask',
                'description' => 'Sensor Amonia',
                'alerts' => [
                    [
                        'condition' => 'above',
                        'threshold' => 1.0,
                        'message' => 'Kadar amonia tinggi!',
                        'telegram_chat_id' => '123456789'
                    ]
                ],
                'measurement_unit' => 'mg/L'
            ]
        ]);
    }
} 