<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pin;
use App\Models\PinLog;
use Carbon\Carbon;

class PinLogSeeder extends Seeder
{
    public function run(): void
    {
        // Get all pins
        $pins = Pin::all();

        foreach ($pins as $pin) {
            // Generate data for the last 30 days
            $startDate = Carbon::now()->subDays(30);
            $currentDate = Carbon::now();

            while ($startDate <= $currentDate) {
                // Generate different patterns based on pin type
                switch ($pin->type) {
                    case 'ph_sensor':
                        // pH values typically range from 0 to 14, but for hydroponics usually 5.5-6.5
                        $value = $this->generatePhValue();
                        $raw_value = $this->mapPhToVoltage($value);
                        break;
                    
                    case 'analog_input':
                        // Generate values between 0-100 for analog sensors
                        $value = $this->generateAnalogValue();
                        $raw_value = $value * 40.95; // Map 0-100 to 0-4095
                        break;
                    
                    case 'digital_output':
                        // For digital outputs, just alternate between 0 and 1
                        $value = rand(0, 1);
                        $raw_value = $value;
                        break;
                    
                    default:
                        $value = rand(0, 100);
                        $raw_value = $value;
                }

                PinLog::create([
                    'pin_id' => $pin->id,
                    'value' => $value,
                    'raw_value' => $raw_value,
                    'created_at' => $startDate,
                    'updated_at' => $startDate,
                ]);

                // Add data points every hour
                $startDate = $startDate->addHour();
            }
        }
    }

    private function generatePhValue(): float
    {
        // Generate pH values with some realistic variation
        $baseValue = 6.0; // Center point for hydroponic pH
        $variation = (mt_rand(-10, 10) / 10); // Add variation of ±1.0
        return round($baseValue + $variation, 1);
    }

    private function mapPhToVoltage(float $ph): float
    {
        // Map pH 0-14 to voltage range (0-4095)
        // pH 7 is typically around 2048
        $voltage = 2048 + ((7 - $ph) * 292); // Approximate conversion
        return round(max(0, min(4095, $voltage)));
    }

    private function generateAnalogValue(): float
    {
        // Generate analog values with some realistic patterns
        $baseValue = 50; // Center point
        $variation = (mt_rand(-200, 200) / 10); // Add variation of ±20
        return round(max(0, min(100, $baseValue + $variation)), 1);
    }
} 