<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('village_id')->constrained()->cascadeOnDelete();
            $table->foreignId('billing_id')->constrained('household_billings')->cascadeOnDelete();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resident_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('transaction_number', 100);
            $table->string('proof_path');
            $table->enum('status', ['pending', 'approved', 'declined'])->default('pending');
            $table->text('declined_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('payment_id')->nullable()->constrained('household_payments')->nullOnDelete();
            $table->timestamps();

            $table->index(['village_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_payments');
    }
};
