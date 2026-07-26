<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'type',
        'created_by_type',
        'question_text',
        'options',
        'correct_option_index',
        'explanation',
        'importance_percentage',
        'order_position',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_option_index' => 'integer',
        'importance_percentage' => 'integer',
        'order_position' => 'integer',
    ];

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
