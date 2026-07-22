<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->enum('response', ['going', 'maybe', 'not_going'])->default('going');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'resident_id']);

            $table->index(['event_id', 'response']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_responses');
    }
};
