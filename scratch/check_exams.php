<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Chapter;
use App\Models\Exam;

$chapters = Chapter::all();

echo "Total Chapters: " . $chapters->count() . "\n";
echo "Total Exams: " . Exam::count() . "\n";

foreach ($chapters as $ch) {
    $exam = Exam::firstOrCreate(
        ['chapter_id' => $ch->id],
        [
            'title' => "অধ্যায় পরীক্ষা: {$ch->title}",
            'is_live' => true,
            'duration_minutes' => 15,
            'negative_mark_value' => 0.25,
        ]
    );
    echo "Chapter ID {$ch->id} ({$ch->title}) -> Exam ID {$exam->id} ({$exam->title})\n";
}

echo "FINISHED EXAM AUDIT\n";
