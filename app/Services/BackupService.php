<?php

namespace App\Services;

use App\Models\BackupRecord;
use Exception;
use Illuminate\Support\Facades\File;

class BackupService
{
    private string $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('backups');
        if (!File::isDirectory($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    public function createBackup(): BackupRecord
    {
        $filename = 'backup-' . now()->format('Y-m-d-His') . '.sqlite';
        $filepath = $this->backupPath . '/' . $filename;

        $record = BackupRecord::create([
            'user_id' => auth()->id(),
            'filename' => $filename,
            'path' => $filepath,
            'size_bytes' => 0,
            'status' => 'processing',
            'type' => 'database',
        ]);

        try {
            $dbPath = database_path('database.sqlite');
            if (!file_exists($dbPath)) {
                throw new Exception('SQLite database file not found.');
            }

            copy($dbPath, $filepath);

            $size = filesize($filepath);
            $record->update([
                'size_bytes' => $size,
                'status' => 'completed',
            ]);

            AuditLogService::log(
                action: 'Backup Created',
                module: 'System Administration',
                description: "Database backup created: {$filename} (" . number_format($size) . " bytes)",
            );

        } catch (Exception $e) {
            $record->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            AuditLogService::log(
                action: 'Backup Failed',
                module: 'System Administration',
                description: "Backup failed: " . $e->getMessage(),
            );
        }

        return $record->fresh();
    }

    public function downloadBackup(BackupRecord $record): ?\Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (!file_exists($record->path)) {
            return null;
        }

        AuditLogService::log(
            action: 'Backup Downloaded',
            module: 'System Administration',
            description: "Backup downloaded: {$record->filename}",
        );

        return response()->streamDownload(function () use ($record) {
            echo file_get_contents($record->path);
        }, $record->filename, [
            'Content-Type' => 'application/octet-stream',
            'Content-Length' => $record->size_bytes,
        ]);
    }

    public function restoreBackup(BackupRecord $record): bool
    {
        if (!file_exists($record->path)) {
            return false;
        }

        try {
            $dbPath = database_path('database.sqlite');
            copy($record->path, $dbPath);

            AuditLogService::log(
                action: 'Backup Restored',
                module: 'System Administration',
                description: "Database restored from: {$record->filename}",
            );

            return true;
        } catch (Exception $e) {
            AuditLogService::log(
                action: 'Restore Failed',
                module: 'System Administration',
                description: "Restore failed: " . $e->getMessage(),
            );
            return false;
        }
    }

    public function deleteBackup(BackupRecord $record): bool
    {
        if (file_exists($record->path)) {
            unlink($record->path);
        }

        $record->delete();

        AuditLogService::log(
            action: 'Backup Deleted',
            module: 'System Administration',
            description: "Backup deleted: {$record->filename}",
        );

        return true;
    }

    public function getBackups(int $limit = 20)
    {
        return BackupRecord::with('user')
            ->latest()
            ->paginate($limit);
    }

    public function getBackupStats(): array
    {
        $backups = BackupRecord::where('status', 'completed')->get();

        return [
            'total_backups' => BackupRecord::count(),
            'successful_backups' => BackupRecord::where('status', 'completed')->count(),
            'failed_backups' => BackupRecord::where('status', 'failed')->count(),
            'total_size_bytes' => $backups->sum('size_bytes'),
            'latest_backup' => BackupRecord::latest()->first(),
        ];
    }

    public function getTotalBackupSize(): int
    {
        $files = glob($this->backupPath . '/*');
        $total = 0;
        foreach ($files as $file) {
            if (is_file($file)) {
                $total += filesize($file);
            }
        }
        return $total;
    }
}
