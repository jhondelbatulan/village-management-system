<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->foreignId('village_id')->nullable()->change();
        });

        Schema::table('announcements', function (Blueprint $table): void {
            $table->foreignId('village_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Restore NOT NULL: rows pinned to "All Villages" have no village to
        // fall back to, so they are removed before the constraint returns.
        DB::table('events')->whereNull('village_id')->delete();
        DB::table('announcements')->whereNull('village_id')->delete();

        Schema::table('events', function (Blueprint $table): void {
            $table->foreignId('village_id')->nullable(false)->change();
        });

        Schema::table('announcements', function (Blueprint $table): void {
            $table->foreignId('village_id')->nullable(false)->change();
        });
    }
};
