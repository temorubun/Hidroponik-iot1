<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Device;
use App\Models\Pin;
use App\Models\PinLog;
use App\Models\Project;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Str;

class HistoricalDataSeeder extends Seeder
{
    public function run()
    {
        // Get user Agung
        $user = User::where('email', 'agung@gmail.com')->first();
        if (!$user) return;

        // Create Project
        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Hidroponik NFT - Sayuran Daun',
            'description' => 'Proyek hidroponik NFT untuk menanam sayuran daun seperti selada, pakcoy, dan kangkung.',
            'status' => 'active',
            'created_at' => now()->subMonths(3),
            'updated_at' => now()
        ]);

        // Create Device with historical data
        $device = Device::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'name' => 'NFT System Controller',
            'description' => 'Kontroler utama sistem NFT dengan monitoring pH, TDS, suhu, dan level air',
            'device_key' => 'NFT_' . Str::random(32),
            'is_online' => true,
            'last_online' => now(),
            'protocol' => 'websocket',
            'created_at' => now()->subMonths(3),
            'updated_at' => now()
        ]);

        // Create Pins
        $pins = [
            [
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
                ],
                'value_generator' => function() {
                    // pH typically between 5.5 and 7.5 with some fluctuation
                    return round(6.5 + sin(time() / 10000) + (mt_rand(-10, 10) / 100), 2);
                }
            ],
            [
                'name' => 'TDS Sensor',
                'pin_number' => 35,
                'type' => 'analog_input',
                'is_active' => true,
                'settings' => [
                    'alerts' => [
                        'enabled' => true,
                        'min_threshold' => 500,
                        'max_threshold' => 1500
                    ]
                ],
                'value_generator' => function() {
                    // TDS typically between 800-1200 PPM
                    return round(1000 + sin(time() / 8000) * 200 + mt_rand(-50, 50));
                }
            ],
            [
                'name' => 'Water Temperature',
                'pin_number' => 32,
                'type' => 'analog_input',
                'is_active' => true,
                'settings' => [
                    'alerts' => [
                        'enabled' => true,
                        'min_threshold' => 20,
                        'max_threshold' => 30
                    ]
                ],
                'value_generator' => function() {
                    // Temperature typically between 22-28°C
                    return round(25 + sin(time() / 12000) * 3 + mt_rand(-10, 10) / 10, 1);
                }
            ]
        ];

        // Create pins and their historical data
        foreach ($pins as $pinData) {
            $valueGenerator = $pinData['value_generator'];
            unset($pinData['value_generator']);
            
            $pin = Pin::create(array_merge($pinData, [
                'device_id' => $device->id,
                'created_at' => now()->subMonths(3)
            ]));

            // Generate historical data for the last 4 days (96 entries)
            $period = CarbonPeriod::create(now()->subDays(4), '1 hour', now());
            
            $logs = [];
            foreach ($period as $date) {
                $value = $valueGenerator();
                $logs[] = [
                    'pin_id' => $pin->id,
                    'uuid' => (string) Str::uuid(),
                    'value' => $value,
                    'raw_value' => mt_rand(0, 4095), // Simulate raw ADC value
                    'metadata' => json_encode([
                        'timestamp' => $date->timestamp
                    ]),
                    'created_at' => $date,
                    'updated_at' => $date
                ];
            }

            // Insert all logs at once since it's only ~100 entries
            if (!empty($logs)) {
                PinLog::insert($logs);
            }
        }
    }
} 