<?php

namespace App\Services;

use App\Models\BackupRecord;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
        $filename = 'backup-' . now()->format('Y-m-d-His') . '.sql';
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
            $sql = $this->dumpDatabase();
            file_put_contents($filepath, $sql);

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
                description: 'Backup failed: ' . $e->getMessage(),
            );
        }

        return $record->fresh();
    }

    public function downloadBackup(BackupRecord $record): ?BinaryFileResponse
    {
        if (! $record->path || ! file_exists($record->path)) {
            return null;
        }

        AuditLogService::log(
            action: 'Backup Downloaded',
            module: 'System Administration',
            description: "Backup downloaded: {$record->filename}",
        );

        return response()->download($record->path, $record->filename, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    public function restoreBackup(BackupRecord $record): bool
    {
        if (! $record->path || ! file_exists($record->path)) {
            return false;
        }

        $driver = DB::connection()->getDriverName();

        try {
            $sql = file_get_contents($record->path);
            if ($sql === false) {
                throw new Exception('Unable to read backup file.');
            }

            if ($driver === 'mysql') {
                DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
            } elseif ($driver === 'sqlite') {
                DB::unprepared('PRAGMA foreign_keys=OFF');
            }

            foreach ($this->splitSqlStatements($sql) as $statement) {
                DB::unprepared($statement);
            }

            if ($driver === 'mysql') {
                DB::unprepared('SET FOREIGN_KEY_CHECKS=1');
            } elseif ($driver === 'sqlite') {
                DB::unprepared('PRAGMA foreign_keys=ON');
            }

            Cache::flush();

            AuditLogService::log(
                action: 'Backup Restored',
                module: 'System Administration',
                description: "Database restored from: {$record->filename}",
            );

            return true;
        } catch (Exception $e) {
            // Best-effort re-enable FK checks after a failed restore.
            try {
                if ($driver === 'mysql') {
                    DB::unprepared('SET FOREIGN_KEY_CHECKS=1');
                } elseif ($driver === 'sqlite') {
                    DB::unprepared('PRAGMA foreign_keys=ON');
                }
            } catch (Exception) {
                // ignore
            }

            AuditLogService::log(
                action: 'Restore Failed',
                module: 'System Administration',
                description: 'Restore failed: ' . $e->getMessage(),
            );

            return false;
        }
    }

    public function deleteBackup(BackupRecord $record): bool
    {
        if ($record->path && file_exists($record->path)) {
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
        $latest = BackupRecord::latest('created_at')->first();

        return [
            'total_backups' => BackupRecord::count(),
            'successful_backups' => BackupRecord::where('status', 'completed')->count(),
            'failed_backups' => BackupRecord::where('status', 'failed')->count(),
            'total_size_bytes' => (int) BackupRecord::where('status', 'completed')->sum('size_bytes'),
            'total_size' => $this->formatBytes((int) BackupRecord::where('status', 'completed')->sum('size_bytes')),
            'latest_backup' => $latest,
            'last_backup' => $latest?->created_at?->diffForHumans() ?? 'Never',
            'disk_free' => $this->formatBytes((int) @disk_free_space($this->backupPath)),
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

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen((string) $bytes) - 1) / 3);

        return sprintf("%.{$precision}f %s", $bytes / (1024 ** $factor), $units[$factor]);
    }

    private function dumpDatabase(): string
    {
        $driver = DB::connection()->getDriverName();
        $tables = $this->getTableNames();

        $out = "-- Multi-Village Management System database backup\n";
        $out .= '-- Generated: ' . now()->toIso8601String() . "\n";
        $out .= "-- Driver: {$driver}\n\n";

        if ($driver === 'mysql') {
            $out .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        } elseif ($driver === 'sqlite') {
            $out .= "PRAGMA foreign_keys=OFF;\n\n";
        }

        foreach ($tables as $table) {
            $out .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $out .= $this->getCreateTableSql($table) . ";\n\n";

            $columns = DB::connection()->getSchemaBuilder()->getColumnListing($table);
            if (empty($columns)) {
                continue;
            }

            $rows = DB::table($table)->get();
            foreach ($rows as $row) {
                $out .= $this->buildInsert($table, $row, $columns) . ";\n";
            }
            $out .= "\n";
        }

        if ($driver === 'mysql') {
            $out .= "SET FOREIGN_KEY_CHECKS=1;\n";
        } elseif ($driver === 'sqlite') {
            $out .= "PRAGMA foreign_keys=ON;\n";
        }

        return $out;
    }

    private function getTableNames(): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $rows = DB::select(
                "SELECT table_name AS name FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE'"
            );
        } else {
            $rows = DB::select(
                "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"
            );
        }

        return array_map(fn ($row) => $row->name, $rows);
    }

    private function getCreateTableSql(string $table): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $row = DB::selectOne("SHOW CREATE TABLE `{$table}`");

            return $row->{'Create Table'} ?? '';
        }

        // sqlite / others
        $row = DB::selectOne(
            'SELECT sql FROM sqlite_master WHERE type = ? AND name = ?',
            ['table', $table]
        );

        return $row->sql ?? '';
    }

    private function buildInsert(string $table, object $row, array $columns): string
    {
        $pdo = DB::connection()->getPdo();
        $values = [];

        foreach ($columns as $column) {
            $value = $row->$column ?? null;

            if ($value === null) {
                $values[] = 'NULL';
            } elseif (is_bool($value)) {
                $values[] = $value ? '1' : '0';
            } elseif (is_int($value) || is_float($value)) {
                $values[] = (string) $value;
            } else {
                $values[] = $pdo->quote((string) $value);
            }
        }

        $columnList = '`' . implode('`, `', $columns) . '`';

        return "INSERT INTO `{$table}` ({$columnList}) VALUES (" . implode(', ', $values) . ')';
    }

    /**
     * Split a SQL script into individual statements, respecting quoted strings,
     * backtick identifiers, and "--" line comments.
     */
    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $inString = false;
        $inBacktick = false;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];

            if ($inString) {
                $current .= $char;
                if ($char === "'") {
                    // Handle escaped quote ('').
                    if ($i + 1 < $length && $sql[$i + 1] === "'") {
                        $current .= $sql[$i + 1];
                        $i++;
                        continue;
                    }
                    $inString = false;
                }
                continue;
            }

            if ($inBacktick) {
                $current .= $char;
                if ($char === '`') {
                    $inBacktick = false;
                }
                continue;
            }

            if ($char === "'") {
                $inString = true;
                $current .= $char;
                continue;
            }

            if ($char === '`') {
                $inBacktick = true;
                $current .= $char;
                continue;
            }

            // "--" line comment
            if ($char === '-' && $i + 1 < $length && $sql[$i + 1] === '-') {
                while ($i < $length && $sql[$i] !== "\n") {
                    $i++;
                }
                $current .= "\n";
                continue;
            }

            if ($char === ';') {
                $trimmed = trim($current);
                if ($trimmed !== '') {
                    $statements[] = $trimmed;
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }

        $trimmed = trim($current);
        if ($trimmed !== '') {
            $statements[] = $trimmed;
        }

        return $statements;
    }
}
