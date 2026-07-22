<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->foreignId('admin_approved_by')->nullable()->after('remarks')->constrained('users')->nullOnDelete();
            $table->timestamp('admin_approved_at')->nullable()->after('admin_approved_by');
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->foreignId('admin_approved_by')->nullable()->after('remarks')->constrained('users')->nullOnDelete();
            $table->timestamp('admin_approved_at')->nullable()->after('admin_approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->dropForeign(['admin_approved_by']);
            $table->dropColumn(['admin_approved_by', 'admin_approved_at']);
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->dropForeign(['admin_approved_by']);
            $table->dropColumn(['admin_approved_by', 'admin_approved_at']);
        });
    }
};
