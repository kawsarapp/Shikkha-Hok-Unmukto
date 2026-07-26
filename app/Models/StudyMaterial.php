<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'content',
        'ai_summary',
        'audio_flag',
        'audio_url',
        'pdf_file_path',
    ];

    protected $casts = [
        'audio_flag' => 'boolean',
    ];

    protected $appends = ['pages'];

    public function getPagesAttribute(): array
    {
        if (!$this->content) {
            return [];
        }

        // Check explicit page marker ---page--- or ---
        if (str_contains($this->content, '---page---')) {
            return array_map('trim', explode('---page---', $this->content));
        }

        if (str_contains($this->content, "\n---\n")) {
            return array_map('trim', explode("\n---\n", $this->content));
        }

        // Auto split into ~1200 character chunks if very long text
        if (mb_strlen($this->content) > 1500) {
            $paragraphs = explode("\n\n", $this->content);
            $pages = [];
            $currentPage = '';

            foreach ($paragraphs as $p) {
                if (mb_strlen($currentPage . "\n\n" . $p) > 1200) {
                    if (!empty($currentPage)) {
                        $pages[] = trim($currentPage);
                    }
                    $currentPage = $p;
                } else {
                    $currentPage .= ($currentPage ? "\n\n" : '') . $p;
                }
            }
            if (!empty($currentPage)) {
                $pages[] = trim($currentPage);
            }
            return $pages;
        }

        return [$this->content];
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
