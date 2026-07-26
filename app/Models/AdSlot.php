<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'slot_name',
        'title',
        'ad_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
