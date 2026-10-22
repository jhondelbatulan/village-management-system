<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gcash_configs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('village_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('gcash_number', 20);
            $table->string('gcash_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gcash_configs');
    }
};
