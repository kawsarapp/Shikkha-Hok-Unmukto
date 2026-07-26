<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key_name')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, text, integer, boolean, json
            $table->string('group')->default('general'); // ai, exam, progression, branding, ads
            $table->string('label')->nullable();
            $table->timestamps();
        });

        Schema::create('ad_slots', function (Blueprint $table) {
            $table->id();
            $table->string('slot_name')->unique(); // e.g. sidebar_ad, header_banner, in_content_ad
            $table->string('title')->nullable();
            $table->text('ad_code')->nullable(); // AdSense script tag or HTML banner code
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_slots');
        Schema::dropIfExists('settings');
    }
};
