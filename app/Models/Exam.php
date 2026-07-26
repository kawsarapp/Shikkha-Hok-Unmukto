<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'title',
        'is_live',
        'start_time',
        'duration_minutes',
        'negative_mark_value',
        'sudden_death_mode',
    ];

    protected $casts = [
        'is_live' => 'boolean',
        'sudden_death_mode' => 'boolean',
        'start_time' => 'datetime',
        'duration_minutes' => 'integer',
        'negative_mark_value' => 'float',
    ];

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }
}
