<?php

namespace App\Http\Controllers;

use App\Models\BackupRecord;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function __construct(protected BackupService $backupService) {}

    public function index(): View
    {
        $backups = $this->backupService->getBackups(20);
        $backupStats = $this->backupService->getBackupStats();

        return view('system-admin.backups', compact('backups', 'backupStats'));
    }

    public function create(): RedirectResponse
    {
        $record = $this->backupService->createBackup();

        if ($record->status === 'completed') {
            return back()->with('success', "Backup created successfully: {$record->filename}");
        }

        return back()->with('error', 'Backup creation failed: ' . ($record->error_message ?? 'Unknown error'));
    }

    public function download(BackupRecord $backupRecord): BinaryFileResponse|RedirectResponse
    {
        $response = $this->backupService->downloadBackup($backupRecord);

        return $response ?? back()->with('error', 'Backup file not found.');
    }

    public function restore(BackupRecord $backupRecord): RedirectResponse
    {
        $success = $this->backupService->restoreBackup($backupRecord);

        return $success
            ? back()->with('success', 'Database restored successfully from: ' . $backupRecord->filename)
            : back()->with('error', 'Restore failed: ' . ($backupRecord->error_message ?? 'The backup file could not be applied.'));
    }

    public function destroy(BackupRecord $backupRecord): RedirectResponse
    {
        $this->backupService->deleteBackup($backupRecord);

        return back()->with('success', 'Backup deleted successfully.');
    }

    public function resetApplicationData(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password'],
        ], [
            'password.required' => 'Please enter your Super Admin password to confirm.',
            'password.current_password' => 'The password you entered does not match your Super Admin account.',
        ]);

        $tables = [
            // Phase 1 — Leaf tables (no tables depend on these)
            'online_payments',
            'event_responses',
            'announcement_responses',
            'participations',
            'payment_receipts',
            'household_payments',
            'payment_penalties',
            'household_payment_amounts',

            // Phase 2 — Mid-level tables
            'household_billings',
            'events',
            'announcements',

            // Phase 3 — Core entity tables
            'residents',
            'households',
        ];

        $counts = [];

        DB::beginTransaction();
        try {
            foreach ($tables as $table) {
                if (! DB::getSchemaBuilder()->hasTable($table)) {
                    continue;
                }
                $counts[$table] = DB::table($table)->count();
                DB::table($table)->delete();
            }
            DB::commit();

            \Illuminate\Support\Facades\Cache::flush();

            $total = array_sum($counts);
            \App\Services\AuditLogService::log(
                'Reset Application Data',
                'System Administration',
                "Reset {$total} records across " . count($counts) . " tables (households, residents, billings, payments, online payments, announcements, events)"
            );

            return back()->with('success', "Application data has been reset. {$total} records deleted across " . count($counts) . ' tables.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to reset application data: ' . $e->getMessage());
        }
    }
}
