<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Survey extends Model
{
    protected $fillable = [
        'member_id',
        'question_1',
        'question_2',
        'question_3',
        'question_4',
        'question_5',
        'comments',
        'additional_data',
    ];

    protected $casts = [
        'additional_data' => 'array',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
