<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class SessionManagementService
{
    public function getActiveSessions(): \Illuminate\Support\Collection
    {
        if (config('session.driver') !== 'database') {
            return collect();
        }

        $threshold = now()->subMinutes(config('session.lifetime', 120))->timestamp;

        $sessions = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $threshold)
            ->get();

        $result = collect();
        foreach ($sessions as $session) {
            $user = User::with('role', 'village')->find($session->user_id);
            if (!$user) continue;

            $result->push((object) [
                'id' => $session->id,
                'user' => $user,
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
                'last_activity' => Carbon::createFromTimestamp($session->last_activity),
                'browser' => AuditLogService::parseBrowser($session->user_agent),
            ]);
        }

        return $result;
    }

    public function terminateSession(string $sessionId): bool
    {
        if (config('session.driver') !== 'database') {
            return false;
        }

        $deleted = DB::table('sessions')->where('id', $sessionId)->delete();

        if ($deleted) {
            AuditLogService::log(
                action: 'Session Terminated',
                module: 'Security',
                description: "Session terminated: {$sessionId}",
            );
        }

        return $deleted > 0;
    }

    public function terminateAllUserSessions(int $userId): int
    {
        $user = User::find($userId);
        if (!$user) return 0;

        if (config('session.driver') !== 'database') {
            return 0;
        }

        $count = DB::table('sessions')
            ->where('user_id', $userId)
            ->delete();

        AuditLogService::log(
            action: 'All Sessions Terminated',
            module: 'Security',
            description: "All sessions terminated for user: {$user->username}",
            recordType: 'User',
            recordId: $userId,
        );

        return $count;
    }

    public function getSessionCount(): int
    {
        if (config('session.driver') !== 'database') {
            return 0;
        }

        return DB::table('sessions')
            ->where('last_activity', '>=', now()->subMinutes(config('session.lifetime', 120))->timestamp)
            ->count();
    }

    public function forceLogoutAllSessions(): int
    {
        if (config('session.driver') !== 'database') {
            return 0;
        }

        $count = DB::table('sessions')->delete();

        if ($count > 0) {
            AuditLogService::log(
                action: 'All Sessions Terminated',
                module: 'Security',
                description: "Force logout: all {$count} active session(s) terminated.",
            );
        }

        return $count;
    }
}
