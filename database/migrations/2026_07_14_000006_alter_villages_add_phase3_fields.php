<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('villages', function (Blueprint $table) {
            $table->string('village_code')->unique()->after('village_name');
            $table->string('contact_number')->nullable()->after('address');
            $table->string('email')->nullable()->after('contact_number');
            $table->decimal('total_area', 10, 2)->nullable()->after('total_population');
            $table->string('cover_photo')->nullable()->after('logo');
            $table->string('status')->default('active')->after('cover_photo');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('villages', function (Blueprint $table) {
            $table->dropColumn([
                'village_code', 'contact_number', 'email',
                'total_area', 'cover_photo', 'status',
            ]);
            $table->dropSoftDeletes();
        });
    }
};
