<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_configurations', function (Blueprint $table) {
            $table->integer('monthly_due_day')->default(15)->after('payment_notes');
            $table->integer('annual_due_month')->default(1)->after('monthly_due_day');
            $table->integer('annual_due_day')->default(15)->after('annual_due_month');
        });

        Schema::create('household_payment_amounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')->constrained()->cascadeOnDelete();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('custom_amount', 12, 2);
            $table->timestamps();

            $table->unique(['village_id', 'household_id', 'payment_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_payment_amounts');

        Schema::table('payment_configurations', function (Blueprint $table) {
            $table->dropColumn(['monthly_due_day', 'annual_due_month', 'annual_due_day']);
        });
    }
};
