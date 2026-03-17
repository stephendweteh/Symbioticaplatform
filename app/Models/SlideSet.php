<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlideSet extends Model
{
    protected $fillable = [
        'title',
        'description',
        'thumbnail_path',
        'order_number',
        'is_active',
    ];

    public function slides(): HasMany
    {
        return $this->hasMany(Slide::class);
    }
}

