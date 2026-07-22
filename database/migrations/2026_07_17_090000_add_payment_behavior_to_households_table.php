<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->string('payment_behavior')->nullable()->after('status');
            $table->decimal('payment_reliability_score', 5, 2)->default(50.00)->after('payment_behavior');
            $table->decimal('prediction_confidence', 5, 2)->default(0.00)->after('payment_reliability_score');
            $table->text('prediction_reasons')->nullable()->after('prediction_confidence');
            $table->timestamp('last_prediction_at')->nullable()->after('prediction_reasons');

            $table->index('payment_behavior');
            $table->index('payment_reliability_score');
        });
    }

    public function down(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->dropIndex(['payment_behavior']);
            $table->dropIndex(['payment_reliability_score']);
            $table->dropColumn([
                'payment_behavior',
                'payment_reliability_score',
                'prediction_confidence',
                'prediction_reasons',
                'last_prediction_at',
            ]);
        });
    }
};
