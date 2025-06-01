<?php

namespace App\Models;

class EspPin
{
    public static function getAvailablePins()
    {
        return [
            // GPIO pins that can be used as both INPUT and OUTPUT
            ['number' => 2, 'capabilities' => ['digital_input', 'digital_output', 'touch_sensor']],
            ['number' => 4, 'capabilities' => ['digital_input', 'digital_output']],
            ['number' => 5, 'capabilities' => ['digital_input', 'digital_output']],
            ['number' => 12, 'capabilities' => ['digital_input', 'digital_output', 'touch_sensor']],
            ['number' => 13, 'capabilities' => ['digital_input', 'digital_output', 'touch_sensor']],
            ['number' => 14, 'capabilities' => ['digital_input', 'digital_output', 'touch_sensor']],
            ['number' => 15, 'capabilities' => ['digital_input', 'digital_output', 'touch_sensor']],
            ['number' => 16, 'capabilities' => ['digital_input', 'digital_output']],
            ['number' => 17, 'capabilities' => ['digital_input', 'digital_output']],
            ['number' => 18, 'capabilities' => ['digital_input', 'digital_output']],
            ['number' => 19, 'capabilities' => ['digital_input', 'digital_output']],
            ['number' => 21, 'capabilities' => ['digital_input', 'digital_output']],
            ['number' => 22, 'capabilities' => ['digital_input', 'digital_output']],
            ['number' => 23, 'capabilities' => ['digital_input', 'digital_output']],
            ['number' => 25, 'capabilities' => ['digital_input', 'digital_output', 'dac']],
            ['number' => 26, 'capabilities' => ['digital_input', 'digital_output', 'dac']],
            ['number' => 27, 'capabilities' => ['digital_input', 'digital_output', 'touch_sensor']],
            ['number' => 32, 'capabilities' => ['digital_input', 'digital_output', 'analog_input', 'touch_sensor']],
            ['number' => 33, 'capabilities' => ['digital_input', 'digital_output', 'analog_input', 'touch_sensor']],
            
            // ADC/Input only pins
            ['number' => 34, 'capabilities' => ['digital_input', 'analog_input']],
            ['number' => 35, 'capabilities' => ['digital_input', 'analog_input']],
            ['number' => 36, 'capabilities' => ['digital_input', 'analog_input']],
            ['number' => 39, 'capabilities' => ['digital_input', 'analog_input']],
        ];
    }

    public static function getPinsByCapability($capability)
    {
        return array_filter(self::getAvailablePins(), function($pin) use ($capability) {
            return in_array($capability, $pin['capabilities']);
        });
    }

    public static function validatePinForCapability($pinNumber, $capability)
    {
        $pins = self::getPinsByCapability($capability);
        return collect($pins)->contains('number', $pinNumber);
    }
} 