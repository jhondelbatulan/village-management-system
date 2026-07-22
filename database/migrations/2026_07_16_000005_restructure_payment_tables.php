<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('penalties');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_types');
        Schema::dropIfExists('village_payment_settings');

        Schema::create('payment_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')->constrained()->cascadeOnDelete();
            $table->boolean('payment_enabled')->default(false);
            $table->string('payment_manager_role')->nullable()->comment('treasurer or super_admin');
            $table->decimal('monthly_rental_amount', 10, 2)->default(0);
            $table->decimal('monthly_dues_amount', 10, 2)->default(0);
            $table->decimal('building_permit_amount', 10, 2)->default(0);
            $table->decimal('penalty_rate', 5, 2)->default(0)->comment('percentage per month');
            $table->integer('grace_period_days')->default(0)->comment('days after due date before penalty');
            $table->string('billing_prefix', 10)->default('BILL');
            $table->string('receipt_prefix', 10)->default('RCPT');
            $table->string('permit_prefix', 10)->default('PERMIT');
            $table->text('payment_notes')->nullable();
            $table->timestamps();

            $table->unique('village_id');
        });

        Schema::create('payment_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')->constrained()->cascadeOnDelete();
            $table->string('payment_name');
            $table->text('description')->nullable();
            $table->enum('frequency', ['monthly', 'annual', 'one_time'])->default('monthly');
            $table->decimal('default_amount', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['village_id', 'payment_name']);
        });

        Schema::create('household_billings', function (Blueprint $table) {
            $table->id();
            $table->string('billing_number')->unique();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('village_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_type_id')->constrained()->cascadeOnDelete();
            $table->string('billing_period')->comment('e.g. 2026-07 or 2026');
            $table->date('due_date');
            $table->decimal('amount', 10, 2);
            $table->decimal('penalty', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['draft', 'pending', 'paid', 'partially_paid', 'overdue', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['village_id', 'status']);
            $table->index(['household_id', 'status']);
            $table->index(['billing_period', 'village_id']);
        });

        Schema::create('household_payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('billing_id')->constrained('household_billings')->cascadeOnDelete();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('village_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->comment('Amount being paid in this transaction');
            $table->decimal('penalty', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_paid', 10, 2)->comment('Amount actually paid');
            $table->date('payment_date');
            $table->date('due_date');
            $table->enum('payment_method', ['cash', 'gcash', 'maya', 'bank_transfer'])->default('cash');
            $table->string('reference_number')->nullable()->comment('For non-cash payments');
            $table->enum('payment_status', ['pending', 'paid', 'partially_paid', 'overdue', 'cancelled'])->default('pending');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['village_id', 'payment_status']);
            $table->index(['household_id', 'payment_status']);
            $table->index(['billing_id', 'payment_status']);
        });

        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('payment_id')->constrained('household_payments')->cascadeOnDelete();
            $table->foreignId('billing_id')->constrained('household_billings')->cascadeOnDelete();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('village_id')->constrained()->cascadeOnDelete();
            $table->string('receipt_type')->default('payment')->comment('payment or permit');
            $table->decimal('amount_paid', 10, 2);
            $table->string('payment_method');
            $table->string('received_from');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('receipt_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['village_id', 'receipt_date']);
        });

        Schema::create('payment_penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_id')->constrained('household_billings')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->text('reason')->nullable();
            $table->date('imposed_date');
            $table->foreignId('imposed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['active', 'waived', 'paid'])->default('active');
            $table->timestamps();

            $table->index(['billing_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_penalties');
        Schema::dropIfExists('payment_receipts');
        Schema::dropIfExists('household_payments');
        Schema::dropIfExists('household_billings');
        Schema::dropIfExists('payment_types');
        Schema::dropIfExists('payment_configurations');

        Schema::create('village_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')->constrained()->onDelete('cascade');
            $table->boolean('payment_enabled')->default(false);
            $table->string('payment_manager_role')->nullable();
            $table->decimal('monthly_rental_amount', 10, 2)->default(0);
            $table->decimal('monthly_dues_amount', 10, 2)->default(0);
            $table->decimal('building_permit_amount', 10, 2)->default(0);
            $table->decimal('penalty_rate', 5, 2)->default(0);
            $table->integer('grace_period_days')->default(0);
            $table->string('receipt_prefix', 10)->default('RCPT');
            $table->text('payment_notes')->nullable();
            $table->timestamps();
            $table->unique('village_id');
        });

        Schema::create('payment_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')->constrained()->cascadeOnDelete();
            $table->string('payment_name');
            $table->text('description')->nullable();
            $table->string('frequency')->default('monthly');
            $table->decimal('default_amount', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['village_id', 'payment_name']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('village_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_type_id')->constrained()->cascadeOnDelete();
            $table->string('billing_period');
            $table->decimal('amount', 10, 2);
            $table->decimal('penalty', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_status', ['pending', 'paid', 'partially_paid', 'overdue', 'cancelled'])->default('pending');
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['village_id', 'payment_status']);
            $table->index(['household_id', 'payment_status']);
        });

        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->text('reason')->nullable();
            $table->date('imposed_date');
            $table->foreignId('imposed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['active', 'waived', 'paid'])->default('active');
            $table->timestamps();
            $table->index(['payment_id', 'status']);
        });
    }
};
