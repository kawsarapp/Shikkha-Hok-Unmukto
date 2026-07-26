<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseProgress extends Model
{
    use HasFactory;

    protected $table = 'course_progress';

    protected $fillable = [
        'user_id',
        'chapter_id',
        'is_unlocked',
        'highest_score',
        'time_spent_reading_seconds',
        'consecutive_failures',
    ];

    protected $casts = [
        'is_unlocked' => 'boolean',
        'highest_score' => 'float',
        'time_spent_reading_seconds' => 'integer',
        'consecutive_failures' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
