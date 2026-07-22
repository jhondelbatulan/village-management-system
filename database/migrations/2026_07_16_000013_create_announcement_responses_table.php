<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcement_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('household_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('response', ['going', 'maybe', 'not_going']);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['announcement_id', 'resident_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_responses');
    }
};
