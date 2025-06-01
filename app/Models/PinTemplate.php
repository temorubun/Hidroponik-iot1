<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PinTemplate extends Model
{
    protected $fillable = [
        'name',
        'type',
        'icon',
        'supported_pins',
        'settings'
    ];

    protected $casts = [
        'supported_pins' => 'array',
        'settings' => 'array'
    ];

    public static function getAvailableTemplates()
    {
        return [
            [
                'id' => 0,
                'name' => 'LED Control',
                'type' => 'digital_output',
                'icon' => 'lightbulb',
                'supported_pins' => [2, 4, 5, 12, 13, 14, 15, 16, 17, 18, 19, 21, 22, 23, 25, 26, 27, 32, 33],
                'settings' => [
                    'default_value' => 0
                ]
            ],
            [
                'id' => 1,
                'name' => 'Relay Control',
                'type' => 'digital_output',
                'icon' => 'power-off',
                'supported_pins' => [2, 4, 5, 12, 13, 14, 15, 16, 17, 18, 19, 21, 22, 23, 25, 26, 27, 32, 33],
                'settings' => [
                    'default_value' => 0
                ]
            ],
            [
                'id' => 2,
                'name' => 'Push Button',
                'type' => 'digital_input',
                'icon' => 'circle',
                'supported_pins' => [2, 4, 5, 12, 13, 14, 15, 16, 17, 18, 19, 21, 22, 23, 25, 26, 27, 32, 33, 34, 35, 36, 39],
                'settings' => [
                    'pull_mode' => 'pullup'
                ]
            ],
            [
                'id' => 3,
                'name' => 'DHT11/DHT22 Temperature',
                'type' => 'digital_input',
                'icon' => 'temperature-half',
                'supported_pins' => [2, 4, 5, 12, 13, 14, 15, 16, 17, 18, 19, 21, 22, 23, 25, 26, 27, 32, 33],
                'settings' => [
                    'sensor_type' => 'dht11'
                ]
            ],
            [
                'id' => 4,
                'name' => 'Soil Moisture',
                'type' => 'analog_input',
                'icon' => 'droplet',
                'supported_pins' => [32, 33, 34, 35, 36, 39],
                'settings' => [
                    'calibration' => [
                        'dry' => 4095,
                        'wet' => 2048
                    ]
                ]
            ],
            [
                'id' => 5,
                'name' => 'LDR Light Sensor',
                'type' => 'analog_input',
                'icon' => 'sun',
                'supported_pins' => [32, 33, 34, 35, 36, 39],
                'settings' => [
                    'threshold' => 2048
                ]
            ]
        ];
    }
} 