<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlideSubcategory extends Model
{
    protected $fillable = [
        'slide_set_id',
        'title',
        'description',
        'thumbnail_path',
        'order_number',
        'is_active',
    ];

    public function slideSet(): BelongsTo
    {
        return $this->belongsTo(SlideSet::class);
    }

    public function slides(): HasMany
    {
        return $this->hasMany(Slide::class, 'slide_subcategory_id');
    }
}

