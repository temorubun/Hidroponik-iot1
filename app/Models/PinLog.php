<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;

class PinLog extends Model
{
    use HasUuid;

    protected $fillable = [
        'pin_id',
        'value',
        'raw_value'
    ];

    protected $casts = [
        'value' => 'float',
        'raw_value' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function pin(): BelongsTo
    {
        return $this->belongsTo(Pin::class);
    }
} 