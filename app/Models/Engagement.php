<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Engagement extends Model
{
    protected $fillable = [
        'member_id',
        'started_at',
        'completed_at',
        'slides_viewed',
        'total_slides',
        'completion_percentage',
        'status',
        'star_rating',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
