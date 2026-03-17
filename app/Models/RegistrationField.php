<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationField extends Model
{
    protected $fillable = [
        'label',
        'field_key',
        'field_type',
        'options',
        'is_system',
        'is_required',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_system' => 'boolean',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];
}
