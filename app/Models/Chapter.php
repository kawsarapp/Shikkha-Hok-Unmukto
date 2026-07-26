<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'parent_id',
        'subject',
        'order_index',
        'order_position',
        'title',
        'min_reading_time_seconds',
        'passing_score_percentage',
        'importance_percentage',
        'is_published',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'order_position' => 'integer',
        'min_reading_time_seconds' => 'integer',
        'passing_score_percentage' => 'integer',
        'importance_percentage' => 'integer',
        'is_published' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'parent_id');
    }

    public function subChapters(): HasMany
    {
        return $this->hasMany(Chapter::class, 'parent_id')->with(['studyMaterial', 'questions'])->orderBy('order_position', 'asc');
    }

    public function studyMaterial(): HasOne
    {
        return $this->hasOne(StudyMaterial::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order_position', 'asc');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }
}
