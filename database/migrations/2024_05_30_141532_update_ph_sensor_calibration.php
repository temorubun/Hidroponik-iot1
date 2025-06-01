<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Pin;

return new class extends Migration
{
    public function up()
    {
        $pin = Pin::where('type', 'ph_sensor')->first();
        if ($pin) {
            $settings = [
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
            ];
            $pin->settings = $settings;
            $pin->save();
        }
    }

    public function down()
    {
        $pin = Pin::where('type', 'ph_sensor')->first();
        if ($pin) {
            $pin->settings = [
                'calibration' => [
                    '4' => 100,
                    '7' => 200,
                    '10' => 300
                ],
                'samples' => 10,
                'interval' => 1000
            ];
            $pin->save();
        }
    }
}; 