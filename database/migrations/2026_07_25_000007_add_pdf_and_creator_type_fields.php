<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('study_materials', function (Blueprint $table) {
            $table->string('pdf_file_path')->nullable()->after('audio_url');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->enum('created_by_type', ['human', 'ai'])->default('human')->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('study_materials', function (Blueprint $table) {
            $table->dropColumn('pdf_file_path');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('created_by_type');
        });
    }
};
