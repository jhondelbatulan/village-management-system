<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('village_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')->constrained()->onDelete('cascade');
            $table->boolean('payment_enabled')->default(false);
            $table->string('payment_manager_role')->nullable()->comment('treasurer or super_admin');
            $table->decimal('monthly_rental_amount', 10, 2)->default(0);
            $table->decimal('monthly_dues_amount', 10, 2)->default(0);
            $table->decimal('building_permit_amount', 10, 2)->default(0);
            $table->decimal('penalty_rate', 5, 2)->default(0)->comment('percentage per month');
            $table->integer('grace_period_days')->default(0)->comment('days after due date before penalty');
            $table->string('receipt_prefix', 10)->default('RCPT');
            $table->text('payment_notes')->nullable();
            $table->timestamps();

            $table->unique('village_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('village_payment_settings');
    }
};
