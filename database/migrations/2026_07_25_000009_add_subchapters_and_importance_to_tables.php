<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            if (!Schema::hasColumn('chapters', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->constrained('chapters')->onDelete('cascade');
            }
            if (!Schema::hasColumn('chapters', 'importance_percentage')) {
                $table->integer('importance_percentage')->default(85);
            }
            if (!Schema::hasColumn('chapters', 'is_published')) {
                $table->boolean('is_published')->default(true);
            }
            if (!Schema::hasColumn('chapters', 'order_position')) {
                $table->integer('order_position')->default(0);
            }
        });

        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'importance_percentage')) {
                $table->integer('importance_percentage')->default(90);
            }
            if (!Schema::hasColumn('questions', 'order_position')) {
                $table->integer('order_position')->default(0);
            }
        });

        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'order_position')) {
                $table->integer('order_position')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropColumn(['parent_id', 'importance_percentage', 'is_published', 'order_position']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['importance_percentage', 'order_position']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['order_position']);
        });
    }
};
