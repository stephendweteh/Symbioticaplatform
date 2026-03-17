<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'gender',
        'organization',
        'role',
        'county',
        'unique_code',
        'additional_data',
    ];

    protected $casts = [
        'additional_data' => 'array',
    ];

    public function engagements(): HasMany
    {
        return $this->hasMany(Engagement::class);
    }

    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }
}
