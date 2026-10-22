<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_configurations', function (Blueprint $table) {
            $table->integer('monthly_due_day')->nullable()->change();
            $table->integer('annual_due_month')->nullable()->change();
            $table->integer('annual_due_day')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('payment_configurations', function (Blueprint $table) {
            $table->integer('monthly_due_day')->default(15)->change();
            $table->integer('annual_due_month')->default(1)->change();
            $table->integer('annual_due_day')->default(15)->change();
        });
    }
};