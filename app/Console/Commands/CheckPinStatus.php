<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pin;

class CheckPinStatus extends Command
{
    protected $signature = 'pin:status {pin_id?}';
    protected $description = 'Check the current status of pins';

    public function handle()
    {
        $pinId = $this->argument('pin_id');

        if ($pinId) {
            $pin = Pin::find($pinId);
            if (!$pin) {
                $this->error("Pin not found!");
                return;
            }
            $this->showPinStatus($pin);
        } else {
            $pins = Pin::where('type', 'digital_output')->get();
            foreach ($pins as $pin) {
                $this->showPinStatus($pin);
                $this->line('------------------------');
            }
        }
    }

    protected function showPinStatus($pin)
    {
        $this->info("Pin: {$pin->name}");
        $this->line("Value: {$pin->value}");
        $this->line("Type: {$pin->type}");
        $this->line("Last Update: {$pin->last_update}");
        
        if (isset($pin->settings['schedule'])) {
            $schedule = $pin->settings['schedule'];
            $this->line("\nSchedule Settings:");
            $this->line("Enabled: " . ($schedule['enabled'] ?? 'false'));
            $this->line("Start Time: " . ($schedule['on'] ?? 'Not set'));
            $this->line("Duration: " . ($schedule['duration'] ?? '0') . " minutes");
            $this->line("Interval: " . ($schedule['interval'] ?? '0') . " minutes");
            $this->line("Cycle Duration: " . ($schedule['cycle_duration'] ?? '0') . " minutes");
            $this->line("Repeat: " . (($schedule['repeat_hourly'] ?? false) ? 'Yes' : 'No'));
            if ($schedule['repeat_hourly'] ?? false) {
                $this->line("Repeat Every: " . ($schedule['hourly_interval'] ?? '60') . " minutes");
            }
        }

        if (isset($pin->settings['alerts'])) {
            $alerts = $pin->settings['alerts'];
            $this->line("\nAlert Settings:");
            $this->line("Enabled: " . ($alerts['enabled'] ?? 'false'));
            $this->line("Telegram Chat ID: " . ($alerts['telegram_chat_id'] ?? 'Not set'));
        }
    }
} 