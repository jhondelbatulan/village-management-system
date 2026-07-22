<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('village_id')->constrained()->cascadeOnDelete();
            $table->string('resident_code')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->string('gender');
            $table->date('birthdate')->nullable();
            $table->integer('age')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('nationality')->default('Filipino');
            $table->string('occupation')->nullable();
            $table->string('educational_attainment')->nullable();
            $table->string('religion')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('relationship_to_head')->nullable();
            $table->boolean('voter_status')->default(false);
            $table->boolean('is_household_head')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['village_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
