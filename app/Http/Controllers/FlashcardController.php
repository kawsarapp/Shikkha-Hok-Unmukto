<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Question;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FlashcardController extends Controller
{
    public function show(Chapter $chapter): Response
    {
        $chapter->load('questions');
        return Inertia::render('Study/Flashcards', [
            'chapter' => $chapter,
            'questions' => $chapter->questions,
        ]);
    }
}
