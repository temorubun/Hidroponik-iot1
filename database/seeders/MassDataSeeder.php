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

class MassDataSeeder extends Seeder
{
    protected $projectTypes = [
        'Hidroponik NFT' => [
            'descriptions' => [
                'Sistem NFT untuk sayuran daun',
                'NFT untuk microgreens',
                'Hidroponik NFT untuk tanaman herbal'
            ],
            'devices' => [
                'Main Controller' => [
                    'description' => 'Kontroler utama sistem NFT',
                    'pins' => [
                        ['name' => 'pH Sensor', 'type' => 'ph_sensor', 'pin_number' => 33],
                        ['name' => 'TDS Sensor', 'type' => 'analog_input', 'pin_number' => 34],
                        ['name' => 'Water Temp', 'type' => 'analog_input', 'pin_number' => 35],
                        ['name' => 'Water Level', 'type' => 'analog_input', 'pin_number' => 36],
                        ['name' => 'Pump Control', 'type' => 'digital_output', 'pin_number' => 25]
                    ]
                ],
                'Environment Controller' => [
                    'description' => 'Kontroler lingkungan greenhouse',
                    'pins' => [
                        ['name' => 'Air Temperature', 'type' => 'analog_input', 'pin_number' => 32],
                        ['name' => 'Humidity', 'type' => 'analog_input', 'pin_number' => 33],
                        ['name' => 'Light Sensor', 'type' => 'analog_input', 'pin_number' => 34],
                        ['name' => 'Fan Control', 'type' => 'digital_output', 'pin_number' => 25],
                        ['name' => 'LED Control', 'type' => 'digital_output', 'pin_number' => 26]
                    ]
                ]
            ]
        ],
        'DWC Hidroponik' => [
            'descriptions' => [
                'Sistem DWC untuk sayuran buah',
                'DWC untuk tanaman buah',
                'Deep Water Culture untuk herbal'
            ],
            'devices' => [
                'DWC Controller' => [
                    'description' => 'Kontroler utama sistem DWC',
                    'pins' => [
                        ['name' => 'pH Sensor', 'type' => 'ph_sensor', 'pin_number' => 33],
                        ['name' => 'TDS Sensor', 'type' => 'analog_input', 'pin_number' => 34],
                        ['name' => 'Water Temp', 'type' => 'analog_input', 'pin_number' => 35],
                        ['name' => 'DO Sensor', 'type' => 'analog_input', 'pin_number' => 36],
                        ['name' => 'Air Pump', 'type' => 'digital_output', 'pin_number' => 25]
                    ]
                ],
                'Climate Controller' => [
                    'description' => 'Kontroler iklim greenhouse',
                    'pins' => [
                        ['name' => 'Temperature', 'type' => 'analog_input', 'pin_number' => 32],
                        ['name' => 'Humidity', 'type' => 'analog_input', 'pin_number' => 33],
                        ['name' => 'CO2 Sensor', 'type' => 'analog_input', 'pin_number' => 34],
                        ['name' => 'Ventilation', 'type' => 'digital_output', 'pin_number' => 25],
                        ['name' => 'Heater', 'type' => 'digital_output', 'pin_number' => 26]
                    ]
                ]
            ]
        ],
        'Aquaponik' => [
            'descriptions' => [
                'Sistem aquaponik untuk ikan lele',
                'Aquaponik untuk ikan nila',
                'Integrated aquaponics system'
            ],
            'devices' => [
                'Fish Tank Controller' => [
                    'description' => 'Kontroler kolam ikan',
                    'pins' => [
                        ['name' => 'pH Sensor', 'type' => 'ph_sensor', 'pin_number' => 33],
                        ['name' => 'TDS Sensor', 'type' => 'analog_input', 'pin_number' => 34],
                        ['name' => 'Water Temp', 'type' => 'analog_input', 'pin_number' => 35],
                        ['name' => 'DO Sensor', 'type' => 'analog_input', 'pin_number' => 36],
                        ['name' => 'Water Pump', 'type' => 'digital_output', 'pin_number' => 25],
                        ['name' => 'Aerator', 'type' => 'digital_output', 'pin_number' => 26]
                    ]
                ],
                'Plant Bed Controller' => [
                    'description' => 'Kontroler bed tanaman',
                    'pins' => [
                        ['name' => 'Bed pH', 'type' => 'ph_sensor', 'pin_number' => 32],
                        ['name' => 'Moisture', 'type' => 'analog_input', 'pin_number' => 33],
                        ['name' => 'Light Level', 'type' => 'analog_input', 'pin_number' => 34],
                        ['name' => 'Bed Temp', 'type' => 'analog_input', 'pin_number' => 35],
                        ['name' => 'Water Flow', 'type' => 'digital_output', 'pin_number' => 25]
                    ]
                ]
            ]
        ]
    ];

    protected function generateValue($type, $time)
    {
        switch ($type) {
            case 'ph_sensor':
                // pH 5.5-7.5 with daily and random fluctuations
                return round(6.5 + sin($time / 86400) * 0.5 + (mt_rand(-10, 10) / 100), 2);
            
            case 'analog_input':
                // Different ranges for different sensors
                $baseValue = 2048; // Mid-point of 12-bit ADC (0-4095)
                $variation = sin($time / 43200) * 500; // 12-hour cycle
                return round($baseValue + $variation + mt_rand(-100, 100));
            
            case 'digital_output':
                // On/Off state with longer cycles
                return round(abs(sin($time / 3600))); // 1-hour cycle
            
            default:
                return 0;
        }
    }

    public function run()
    {
        // Get user Agung
        $user = User::where('email', 'agung@gmail.com')->first();
        if (!$user) return;

        // Create multiple projects for each type
        foreach ($this->projectTypes as $projectType => $projectData) {
            // Create 3 variations of each project type
            for ($i = 1; $i <= 3; $i++) {
                $project = Project::create([
                    'user_id' => $user->id,
                    'name' => $projectType . " #" . $i,
                    'description' => $projectData['descriptions'][array_rand($projectData['descriptions'])],
                    'status' => 'active',
                    'created_at' => now()->subMonths(3),
                    'updated_at' => now()
                ]);

                // Create devices for each project
                foreach ($projectData['devices'] as $deviceName => $deviceData) {
                    // Create 2 instances of each device type
                    for ($j = 1; $j <= 2; $j++) {
                        $device = Device::create([
                            'user_id' => $user->id,
                            'project_id' => $project->id,
                            'name' => $deviceName . " #" . $j,
                            'description' => $deviceData['description'],
                            'device_key' => Str::random(32),
                            'is_online' => true,
                            'last_online' => now(),
                            'protocol' => 'websocket',
                            'created_at' => now()->subMonths(3),
                            'updated_at' => now()
                        ]);

                        // Create pins for each device
                        foreach ($deviceData['pins'] as $pinData) {
                            $pin = Pin::create([
                                'device_id' => $device->id,
                                'name' => $pinData['name'],
                                'pin_number' => $pinData['pin_number'],
                                'type' => $pinData['type'],
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
                                'created_at' => now()->subMonths(3)
                            ]);

                            // Generate historical data every 1 hour for last 4 days (96 entries)
                            $period = CarbonPeriod::create(now()->subDays(4), '1 hour', now());
                            
                            $logs = [];
                            foreach ($period as $date) {
                                $value = $this->generateValue($pinData['type'], $date->timestamp);
                                $logs[] = [
                                    'pin_id' => $pin->id,
                                    'uuid' => (string) Str::uuid(),
                                    'value' => $value,
                                    'raw_value' => mt_rand(0, 4095),
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
            }
        }
    }
} 