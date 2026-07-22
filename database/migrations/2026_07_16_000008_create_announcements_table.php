<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('category');
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('attachment')->nullable();
            $table->date('start_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_pinned')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['village_id', 'status']);
            $table->index(['village_id', 'is_pinned']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
