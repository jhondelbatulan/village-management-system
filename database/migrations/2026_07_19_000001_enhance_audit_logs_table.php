<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('role')->nullable()->after('user_id');
            $table->foreignId('village_id')->nullable()->after('role')->constrained()->nullOnDelete();
            $table->string('record_type')->nullable()->after('module');
            $table->unsignedBigInteger('record_id')->nullable()->after('record_type');
            $table->string('browser')->nullable()->after('user_agent');
            $table->string('old_values')->nullable()->after('description');
            $table->string('new_values')->nullable()->after('old_values');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn(['role', 'village_id', 'record_type', 'record_id', 'browser', 'old_values', 'new_values']);
        });
    }
};
