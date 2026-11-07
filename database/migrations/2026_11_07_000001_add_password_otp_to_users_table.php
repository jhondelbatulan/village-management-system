<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('password_otp')->nullable()->after('last_login');
            $table->dateTime('password_otp_expires_at')->nullable()->after('password_otp');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['password_otp', 'password_otp_expires_at']);
        });
    }
};
