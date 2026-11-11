<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('household_payments', function (Blueprint $table): void {
            $table->string('billing_number_snapshot')->nullable()->after('billing_id');
            $table->decimal('billing_amount_snapshot', 10, 2)->nullable()->after('billing_number_snapshot');
            $table->string('billing_period_snapshot')->nullable()->after('billing_amount_snapshot');
            $table->decimal('billing_balance_snapshot', 10, 2)->nullable()->after('billing_period_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('household_payments', function (Blueprint $table): void {
            $table->dropColumn([
                'billing_number_snapshot',
                'billing_amount_snapshot',
                'billing_period_snapshot',
                'billing_balance_snapshot',
            ]);
        });
    }
};
