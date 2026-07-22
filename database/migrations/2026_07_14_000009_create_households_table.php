<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')->constrained()->cascadeOnDelete();
            $table->string('household_number');
            $table->unsignedBigInteger('household_head_id')->nullable();
            $table->string('purok')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('contact_number')->nullable();
            $table->string('household_type')->nullable();
            $table->string('house_ownership')->nullable();
            $table->string('house_status')->default('active');
            $table->date('registration_date')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['village_id', 'household_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('households');
    }
};
