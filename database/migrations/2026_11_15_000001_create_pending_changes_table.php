<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staged household/resident update and delete requests.
 *
 * Nothing is written to the target record until the Admin approves the
 * request (stage 1) and the Super Admin gives final approval (stage 2).
 * A rejected request is never applied — the staged payload is discarded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_changes', function (Blueprint $table): void {
            $table->id();
            $table->string('target_type'); // 'household' | 'resident'
            $table->unsignedBigInteger('target_id');
            $table->string('target_label')->nullable(); // snapshot for queues/history
            $table->unsignedBigInteger('village_id')->nullable();
            $table->string('action')->default('update'); // 'update' | 'delete'
            $table->json('payload')->nullable(); // staged field values for updates
            $table->unsignedBigInteger('submitted_by');
            $table->string('submitter_role')->nullable(); // 'resident' | 'secretary'
            $table->string('approval_status')->default('pending'); // pending | admin_approved | approved | rejected
            $table->unsignedBigInteger('admin_approved_by')->nullable();
            $table->timestamp('admin_approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['target_type', 'target_id']);
            $table->index(['action', 'approval_status']);
            $table->index('village_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_changes');
    }
};
