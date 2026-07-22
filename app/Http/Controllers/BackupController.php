<?php

namespace App\Http\Controllers;

use App\Models\BackupRecord;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

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

    public function download(BackupRecord $record): StreamedResponse|RedirectResponse
    {
        $response = $this->backupService->downloadBackup($record);
        return $response ?? back()->with('error', 'Backup file not found.');
    }

    public function restore(BackupRecord $record): RedirectResponse
    {
        $success = $this->backupService->restoreBackup($record);
        return $success
            ? back()->with('success', 'Database restored successfully from: ' . $record->filename)
            : back()->with('error', 'Restore failed.');
    }

    public function destroy(BackupRecord $record): RedirectResponse
    {
        $this->backupService->deleteBackup($record);
        return back()->with('success', 'Backup deleted successfully.');
    }

    public function resetApplicationData(): RedirectResponse
    {
        $tables = [
            // Phase 1 — Leaf tables (no tables depend on these)
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
                $counts[$table] = DB::table($table)->count();
                DB::table($table)->delete();
            }
            DB::commit();

            \Illuminate\Support\Facades\Cache::flush();

            $total = array_sum($counts);
            \App\Services\AuditLogService::log(
                'Reset Application Data',
                'System Administration',
                "Reset {$total} records across " . count($tables) . " tables (households, residents, billings, payments, announcements, events)"
            );

            return back()->with('success', "Application data has been reset. {$total} records deleted across " . count($tables) . " tables.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reset application data: ' . $e->getMessage());
        }
    }
}
