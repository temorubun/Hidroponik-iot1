<?php

namespace App\Observers;

use App\Models\Pin;
use App\WebSocket\WebSocketHandler;

class PinObserver
{
    protected $webSocketHandler;

    public function __construct(WebSocketHandler $webSocketHandler)
    {
        $this->webSocketHandler = $webSocketHandler;
    }

    /**
     * Handle the Pin "created" event.
     */
    public function created(Pin $pin): void
    {
        // Log pin creation
        \Log::info("New pin created: {$pin->name} (Pin {$pin->pin_number})", [
            'pin_id' => $pin->id,
            'pin_type' => $pin->type,
            'device_id' => $pin->device_id,
            'settings' => $pin->settings
        ]);
        
        // Load the device relationship if not loaded
        if (!$pin->relationLoaded('device')) {
            \Log::info("Loading device relationship for pin {$pin->id}");
            $pin->load('device');
        }

        // Broadcast pin configuration to device immediately
        if ($pin->device) {
            \Log::info("Broadcasting new pin config to {$pin->device->name}", [
                'device_key' => $pin->device->device_key,
                'pin_number' => $pin->pin_number,
                'pin_type' => $pin->type
            ]);
            $this->webSocketHandler->broadcastPinConfig($pin->device, $pin);
        } else {
            \Log::error("Error: Pin created without associated device", [
                'pin_id' => $pin->id,
                'device_id' => $pin->device_id
            ]);
        }
    }

    /**
     * Handle the Pin "updated" event.
     */
    public function updated(Pin $pin): void
    {
        // Log pin update
        \Log::info("Pin updated: {$pin->name} (Pin {$pin->pin_number})");
        
        // Load the device relationship if not loaded
        if (!$pin->relationLoaded('device')) {
            $pin->load('device');
        }

        // Broadcast updated pin configuration
        if ($pin->device) {
            \Log::info("Broadcasting updated pin config to {$pin->device->name}");
            $this->webSocketHandler->broadcastPinConfig($pin->device, $pin);
        } else {
            \Log::error("Error: Pin updated without associated device");
        }
    }

    /**
     * Handle the Pin "deleted" event.
     */
    public function deleted(Pin $pin): void
    {
        // Optional: Handle pin deletion if needed
        \Log::info("Pin deleted: {$pin->name} (Pin {$pin->pin_number})");
    }
} 