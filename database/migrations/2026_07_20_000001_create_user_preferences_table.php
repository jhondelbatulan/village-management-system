<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('theme', 20)->default('light');
            $table->string('accent_color', 20)->default('blue');
            $table->string('font_size', 20)->default('medium');
            $table->string('sidebar_style', 20)->default('expanded');
            $table->string('dashboard_layout', 20)->default('default');
            $table->json('dashboard_widgets')->nullable();
            $table->boolean('notification_sound')->default(true);
            $table->boolean('browser_notifications')->default(false);
            $table->string('animation_level', 20)->default('full');
            $table->boolean('compact_mode')->default(false);
            $table->string('language', 10)->default('en');
            $table->string('display_name')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
