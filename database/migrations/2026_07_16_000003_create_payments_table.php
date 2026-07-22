<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('household_id')->constrained()->onDelete('cascade');
            $table->foreignId('village_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_type_id')->constrained()->onDelete('cascade');
            $table->string('billing_period')->comment('e.g. 2026-07, 2026-Annual');
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('penalty', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('payment_status', ['pending', 'paid', 'partially_paid', 'overdue', 'cancelled'])->default('pending');
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['village_id', 'payment_status']);
            $table->index(['household_id', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
