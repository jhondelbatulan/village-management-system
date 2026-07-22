<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->string('block_number')->after('village_id');
            $table->string('lot_number')->after('block_number');
            $table->string('approval_status')->default('draft')->after('status');
            $table->foreignId('submitted_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('submitted_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('remarks')->nullable()->after('approved_at');

            $table->unique(['village_id', 'block_number', 'lot_number']);
        });
    }

    public function down(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->dropForeign(['submitted_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'block_number', 'lot_number', 'approval_status',
                'submitted_by', 'approved_by', 'approved_at', 'remarks',
            ]);
        });
    }
};
