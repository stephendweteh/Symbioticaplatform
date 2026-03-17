<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    protected $fillable = [
        'title',
        'image_path',
        'description',
        'order_number',
        'is_active',
    ];
}
