<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Slide extends Model
{
    protected $fillable = [
        'slide_set_id',
        'title',
        'image_path',
        'description',
        'order_number',
        'is_active',
    ];

    public function slideSet(): BelongsTo
    {
        return $this->belongsTo(SlideSet::class);
    }
}
