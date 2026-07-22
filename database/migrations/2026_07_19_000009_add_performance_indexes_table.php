<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addAuditLogIndexes();
        $this->addActivityLogIndexes();
        $this->addHouseholdIndexes();
        $this->addResidentIndexes();
        $this->addHouseholdBillingIndexes();
        $this->addHouseholdPaymentIndexes();
        $this->addAnnouncementIndexes();
        $this->addEventIndexes();
        $this->addLoginHistoryIndexes();
        $this->addFailedLoginIndexes();
        $this->addSystemSettingIndexes();
        $this->addPaymentConfigurationIndexes();
    }

    public function down(): void
    {
        Schema::table('audit_logs', function ($table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex('action');
            $table->dropIndex('module');
            $table->dropIndex(['action', 'created_at']);
        });

        Schema::table('activity_logs', function ($table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex('action');
            $table->dropIndex(['subject_type', 'subject_id']);
            $table->dropIndex('module');
        });

        Schema::table('households', function ($table) {
            $table->dropIndex('village_id');
            $table->dropIndex('approval_status');
            $table->dropIndex(['village_id', 'approval_status']);
            $table->dropIndex('status');
            $table->dropIndex('household_number');
        });

        Schema::table('residents', function ($table) {
            $table->dropIndex('village_id');
            $table->dropIndex('household_id');
            $table->dropIndex('approval_status');
            $table->dropIndex(['village_id', 'approval_status']);
            $table->dropIndex('status');
        });

        Schema::table('household_billings', function ($table) {
            $table->dropIndex('household_id');
            $table->dropIndex('village_id');
            $table->dropIndex('payment_type_id');
            $table->dropIndex('status');
            $table->dropIndex(['village_id', 'status']);
            $table->dropIndex('due_date');
            $table->dropIndex('billing_period');
        });

        Schema::table('household_payments', function ($table) {
            $table->dropIndex('household_id');
            $table->dropIndex('village_id');
            $table->dropIndex('billing_id');
            $table->dropIndex('payment_type_id');
            $table->dropIndex('payment_status');
            $table->dropIndex('payment_date');
        });

        Schema::table('announcements', function ($table) {
            $table->dropIndex('village_id');
            $table->dropIndex('status');
            $table->dropIndex(['village_id', 'status']);
            $table->dropIndex('created_by');
        });

        Schema::table('events', function ($table) {
            $table->dropIndex('village_id');
            $table->dropIndex('status');
            $table->dropIndex('event_date');
        });

        Schema::table('login_history', function ($table) {
            $table->dropIndex('user_id');
            $table->dropIndex(['user_id', 'login_at']);
            $table->dropIndex('status');
        });

        Schema::table('failed_logins', function ($table) {
            $table->dropIndex('username');
            $table->dropIndex('ip_address');
        });

        Schema::table('system_settings', function ($table) {
            $table->dropIndex('key');
            $table->dropIndex('group');
        });

        Schema::table('payment_configurations', function ($table) {
            $table->dropIndex('village_id');
        });
    }

    protected function addAuditLogIndexes(): void
    {
        if (!Schema::hasTable('audit_logs')) {
            return;
        }

        Schema::table('audit_logs', function ($table) {
            $this->safeIndex($table, 'audit_logs', ['user_id', 'created_at']);
            $this->safeIndex($table, 'audit_logs', 'action');
            $this->safeIndex($table, 'audit_logs', 'module');
            $this->safeIndex($table, 'audit_logs', ['action', 'created_at']);
        });
    }

    protected function addActivityLogIndexes(): void
    {
        if (!Schema::hasTable('activity_logs')) {
            return;
        }

        Schema::table('activity_logs', function ($table) {
            $this->safeIndex($table, 'activity_logs', ['user_id', 'created_at']);
            $this->safeIndex($table, 'activity_logs', 'action');
            $this->safeIndex($table, 'activity_logs', ['subject_type', 'subject_id']);
            $this->safeIndex($table, 'activity_logs', 'module');
        });
    }

    protected function addHouseholdIndexes(): void
    {
        if (!Schema::hasTable('households')) {
            return;
        }

        Schema::table('households', function ($table) {
            $this->safeIndex($table, 'households', 'village_id');
            $this->safeIndex($table, 'households', 'approval_status');
            $this->safeIndex($table, 'households', ['village_id', 'approval_status']);
            $this->safeIndex($table, 'households', 'status');
            $this->safeIndex($table, 'households', 'household_number');
        });
    }

    protected function addResidentIndexes(): void
    {
        if (!Schema::hasTable('residents')) {
            return;
        }

        Schema::table('residents', function ($table) {
            $this->safeIndex($table, 'residents', 'village_id');
            $this->safeIndex($table, 'residents', 'household_id');
            $this->safeIndex($table, 'residents', 'approval_status');
            $this->safeIndex($table, 'residents', ['village_id', 'approval_status']);
            $this->safeIndex($table, 'residents', 'status');
        });
    }

    protected function addHouseholdBillingIndexes(): void
    {
        if (!Schema::hasTable('household_billings')) {
            return;
        }

        Schema::table('household_billings', function ($table) {
            $this->safeIndex($table, 'household_billings', 'household_id');
            $this->safeIndex($table, 'household_billings', 'village_id');
            $this->safeIndex($table, 'household_billings', 'payment_type_id');
            $this->safeIndex($table, 'household_billings', 'status');
            $this->safeIndex($table, 'household_billings', ['village_id', 'status']);
            $this->safeIndex($table, 'household_billings', 'due_date');
            $this->safeIndex($table, 'household_billings', 'billing_period');
        });
    }

    protected function addHouseholdPaymentIndexes(): void
    {
        if (!Schema::hasTable('household_payments')) {
            return;
        }

        Schema::table('household_payments', function ($table) {
            $this->safeIndex($table, 'household_payments', 'household_id');
            $this->safeIndex($table, 'household_payments', 'village_id');
            $this->safeIndex($table, 'household_payments', 'billing_id');
            $this->safeIndex($table, 'household_payments', 'payment_type_id');
            $this->safeIndex($table, 'household_payments', 'payment_status');
            $this->safeIndex($table, 'household_payments', 'payment_date');
        });
    }

    protected function addAnnouncementIndexes(): void
    {
        if (!Schema::hasTable('announcements')) {
            return;
        }

        Schema::table('announcements', function ($table) {
            $this->safeIndex($table, 'announcements', 'village_id');
            $this->safeIndex($table, 'announcements', 'status');
            $this->safeIndex($table, 'announcements', ['village_id', 'status']);
            $this->safeIndex($table, 'announcements', 'created_by');
        });
    }

    protected function addEventIndexes(): void
    {
        if (!Schema::hasTable('events')) {
            return;
        }

        Schema::table('events', function ($table) {
            $this->safeIndex($table, 'events', 'village_id');
            $this->safeIndex($table, 'events', 'status');
            $this->safeIndex($table, 'events', 'event_date');
        });
    }

    protected function addLoginHistoryIndexes(): void
    {
        if (!Schema::hasTable('login_history')) {
            return;
        }

        Schema::table('login_history', function ($table) {
            $this->safeIndex($table, 'login_history', 'user_id');
            $this->safeIndex($table, 'login_history', ['user_id', 'login_at']);
            $this->safeIndex($table, 'login_history', 'status');
        });
    }

    protected function addFailedLoginIndexes(): void
    {
        if (!Schema::hasTable('failed_logins')) {
            return;
        }

        Schema::table('failed_logins', function ($table) {
            $this->safeIndex($table, 'failed_logins', 'username');
            $this->safeIndex($table, 'failed_logins', 'ip_address');
        });
    }

    protected function addSystemSettingIndexes(): void
    {
        if (!Schema::hasTable('system_settings')) {
            return;
        }

        Schema::table('system_settings', function ($table) {
            $columns = Schema::getColumns('system_settings');
            $existingIndexes = Schema::getIndexes('system_settings');

            $hasKeyUnique = collect($existingIndexes)->contains(function ($index) {
                return $index['columns'] === ['key'] && $index['unique'];
            });

            if (!$hasKeyUnique && isset($columns['key'])) {
                $table->unique('key');
            }

            $this->safeIndex($table, 'system_settings', 'group');
        });
    }

    protected function addPaymentConfigurationIndexes(): void
    {
        if (!Schema::hasTable('payment_configurations')) {
            return;
        }

        Schema::table('payment_configurations', function ($table) {
            $this->safeIndex($table, 'payment_configurations', 'village_id');
        });
    }

    protected function safeIndex($table, string $tableName, $columns): void
    {
        $indexName = is_array($columns)
            ? $tableName . '_' . implode('_', $columns) . '_index'
            : $tableName . '_' . $columns . '_index';

        $existingIndexes = Schema::getIndexes($tableName);

        $alreadyExists = collect($existingIndexes)->contains(function ($index) use ($columns) {
            return $index['columns'] === (array) $columns;
        });

        if (!$alreadyExists) {
            $table->index($columns);
        }
    }
};
