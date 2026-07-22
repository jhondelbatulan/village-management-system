<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\FailedLogin;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\BackupService;
use App\Services\PasswordService;
use App\Services\SessionManagementService;
use App\Services\SystemHealthService;
use Illuminate\View\View;

class SystemAdministrationController extends Controller
{
    public function __construct(
        protected SystemHealthService $healthService,
        protected SessionManagementService $sessionService,
        protected BackupService $backupService,
        protected PasswordService $passwordService,
    ) {}

    public function dashboard(): View
    {
        $stats = [
            'active_users' => User::where('status', 'active')->count(),
            'active_sessions' => $this->sessionService->getSessionCount(),
            'failed_logins_today' => FailedLogin::whereDate('created_at', today())->count(),
            'locked_accounts' => User::whereNotNull('locked_at')
                ->where('locked_at', '>', now()->subHours(24))->count(),
            'total_audit_logs' => AuditLog::count(),
            'audit_logs_today' => AuditLog::whereDate('created_at', today())->count(),
        ];

        $recentAuditLogs = AuditLogService::getRecent(10);
        $health = $this->healthService->getHealthData();
        $backupStats = $this->backupService->getBackupStats();
        $securityStats = $this->passwordService->getSecurityStats();

        return view('system-admin.dashboard', compact(
            'stats', 'recentAuditLogs', 'health', 'backupStats', 'securityStats'
        ));
    }

    public function health(): View
    {
        $health = $this->healthService->getHealthData();
        return view('system-admin.health', compact('health'));
    }

    public function about(): View
    {
        return view('system-admin.about');
    }
}
