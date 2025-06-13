<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUuid;

class Pin extends Model
{
    use HasUuid;

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected $fillable = [
        'name',
        'pin_number',
        'type',
        'description',
        'icon',
        'settings',
        'device_id',
        'is_active',
        'value'
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'value' => 'float'
    ];

    public static function types()
    {
        return [
            'digital_output' => 'Digital Output',
            'digital_input' => 'Digital Input',
            'analog_input' => 'Analog Input',
            'ph_sensor' => 'pH Sensor'
        ];
    }

    public static function validationRules()
    {
        return [
            'name' => 'required|string|max:255',
            'pin_number' => 'required|integer|min:0|max:39',
            'type' => 'required|string|in:' . implode(',', array_keys(self::types())),
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:50',
            'settings' => 'nullable|array',
            'settings.calibration' => 'required_if:type,ph_sensor|array',
            'settings.calibration.4' => 'nullable|numeric',
            'settings.calibration.7' => 'nullable|numeric',
            'settings.calibration.10' => 'nullable|numeric',
            'settings.samples' => 'required_if:type,ph_sensor|integer|min:1|max:100',
            'settings.interval' => 'required_if:type,ph_sensor|integer|min:100',
            'device_id' => 'required|exists:devices,id',
            'is_active' => 'boolean',
            'value' => 'nullable|numeric'
        ];
    }

    public function getConfigurationAttribute()
    {
        $config = [
            'pin' => $this->pin_number,
            'type' => $this->type,
            'settings' => $this->settings ?? []
        ];

        if ($this->type === 'ph_sensor') {
            $config['settings'] = array_merge([
                'samples' => 10,
                'interval' => 1000,
                'calibration' => [
                    '4' => null,
                    '7' => null,
                    '10' => null
                ]
            ], $config['settings'] ?? []);
        }

        return $config;
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PinLog::class);
    }
} 