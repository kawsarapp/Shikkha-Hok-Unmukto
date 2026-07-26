<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ReadingProgressController;
use App\Http\Controllers\AiChatbotController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\QuizBattleController;
use App\Http\Controllers\CoinStoreController;
use App\Http\Controllers\AnalyticsController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes (Single-Device Session Middleware Enforced)
Route::middleware(['auth', \App\Http\Middleware\EnforceSingleDeviceSession::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Chapter Reading Material (Mastery Lock Middleware Enforced)
    Route::get('/chapter/{chapter}', [DashboardController::class, 'readChapter'])
        ->middleware(\App\Http\Middleware\CheckChapterUnlock::class)
        ->name('chapter.read');

    // Flashcards & Certificate Routes
    Route::get('/chapter/{chapter}/flashcards', [FlashcardController::class, 'show'])->name('chapter.flashcards');
    Route::get('/certificate/{course}', [CertificateController::class, 'show'])->name('course.certificate');

    // 1v1 Quiz Battle Routes
    Route::get('/battle', [QuizBattleController::class, 'show'])->name('quiz.battle');
    Route::post('/battle/submit', [QuizBattleController::class, 'submit']);

    // Coin Store Routes
    Route::get('/store', [CoinStoreController::class, 'index'])->name('coin.store');
    Route::post('/store/redeem', [CoinStoreController::class, 'redeem']);

    // Subject Weakness Analytics Route
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.performance');

    // Live Exam Routes
    Route::get('/exam/{exam}', [ExamController::class, 'show'])->name('exam.show');
    Route::post('/exam/{exam}/submit', [ExamController::class, 'submit'])->name('exam.submit');
    Route::get('/exam/result/{attempt}', [ExamController::class, 'result'])->name('exam.result');

    // API Endpoints
    Route::post('/api/reading-progress/ping', [ReadingProgressController::class, 'ping']);
    Route::post('/api/exam/autosave', [ExamController::class, 'autoSave']);
    Route::post('/api/ai/chat', [AiChatbotController::class, 'ask']);

    // Admin & Super Admin Management Routes
    Route::prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::post('/settings', [AdminController::class, 'updateSettings']);
        Route::post('/ads/{adSlot}', [AdminController::class, 'updateAdSlot']);
        Route::post('/courses', [AdminController::class, 'storeCourse']);
        Route::delete('/courses/{course}', [AdminController::class, 'deleteCourse']);
        Route::post('/chapters', [AdminController::class, 'storeChapter']);
        Route::post('/subchapters', [AdminController::class, 'storeSubChapter']);
        Route::post('/chapters/{chapter}/toggle-publish', [AdminController::class, 'togglePublish']);
        Route::post('/chapters/{chapter}/update-importance', [AdminController::class, 'updateImportance']);
        Route::post('/questions/{question}/update-importance', [AdminController::class, 'updateQuestionImportance']);
        Route::delete('/chapters/{chapter}', [AdminController::class, 'deleteChapter']);
        Route::post('/study-material', [AdminController::class, 'storeStudyMaterial']);
        Route::post('/questions', [AdminController::class, 'storeQuestion']);
        Route::post('/questions/{question}', [AdminController::class, 'updateQuestion']);
        Route::delete('/questions/{question}', [AdminController::class, 'deleteQuestion']);
        Route::post('/chapters/{chapter}/generate-ai-qa', [AdminController::class, 'generateAiQuestions']);

        // Super Admin User & Permission Routes
        Route::get('/users', [SuperAdminController::class, 'index'])->name('admin.users');
        Route::post('/users', [SuperAdminController::class, 'storeUser']);
        Route::post('/users/{user}', [SuperAdminController::class, 'updateUser']);
        Route::delete('/users/{user}', [SuperAdminController::class, 'deleteUser']);
    });
});
