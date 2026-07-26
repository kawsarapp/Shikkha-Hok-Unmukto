<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->nullable()->constrained('chapters')->onDelete('cascade');
            $table->enum('type', ['normal', 'srs'])->default('normal');
            $table->text('question_text');
            $table->json('options'); // Array of 4 options
            $table->integer('correct_option_index')->default(0); // 0..3
            $table->text('explanation')->nullable();
            $table->timestamps();
        });

        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->nullable()->constrained('chapters')->onDelete('cascade');
            $table->string('title');
            $table->boolean('is_live')->default(false);
            $table->dateTime('start_time')->nullable();
            $table->integer('duration_minutes')->default(15);
            $table->decimal('negative_mark_value', 5, 2)->default(0.25);
            $table->boolean('sudden_death_mode')->default(false); // 3 wrong answers = instant fail
            $table->timestamps();
        });

        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->decimal('score', 8, 2)->default(0.00);
            $table->integer('correct_count')->default(0);
            $table->integer('wrong_count')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('exam_attempts')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->integer('selected_option_index')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        Schema::create('course_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('chapter_id')->constrained('chapters')->onDelete('cascade');
            $table->boolean('is_unlocked')->default(false);
            $table->decimal('highest_score', 8, 2)->default(0.00);
            $table->integer('time_spent_reading_seconds')->default(0);
            $table->integer('consecutive_failures')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'chapter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_progress');
        Schema::dropIfExists('user_answers');
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('questions');
    }
};
