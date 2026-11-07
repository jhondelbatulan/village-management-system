<?php

namespace App\Services;

use App\Models\PasswordHistory;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\FailedLogin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PasswordService
{
    /**
     * Validate a password against the configured policy (length, character
     * classes) and, when a user is given, the recent-password history.
     *
     * @return array<int, string> list of human-readable error messages (empty = valid)
     */
    public function validatePassword(string $password, ?User $user = null): array
    {
        $errors = [];
        $minLength = (int) SystemSetting::getValue('password_min_length', '8');
        $requireUppercase = SystemSetting::getValue('password_require_uppercase', '1') === '1';
        $requireLowercase = SystemSetting::getValue('password_require_lowercase', '1') === '1';
        $requireNumber = SystemSetting::getValue('password_require_number', '1') === '1';
        $requireSpecial = SystemSetting::getValue('password_require_special', '0') === '1';

        if (strlen($password) < $minLength) {
            $errors[] = "Password must be at least {$minLength} characters.";
        }
        if ($requireUppercase && !preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }
        if ($requireLowercase && !preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        }
        if ($requireNumber && !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }
        if ($requireSpecial && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character.';
        }

        $historyLimit = (int) SystemSetting::getValue('password_history_limit', '5');
        if ($user && $historyLimit > 0) {
            $recentPasswords = PasswordHistory::where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->limit($historyLimit)
                ->get();

            foreach ($recentPasswords as $record) {
                if (Hash::check($password, $record->password)) {
                    $errors[] = "Cannot reuse the last {$historyLimit} passwords.";
                    break;
                }
            }
        }

        return $errors;
    }

    /**
     * Enforce the configured password policy, throwing a ValidationException
     * (keyed to $field) when the password does not comply. Used by every
     * password-change flow so the admin-configured policy is always applied.
     *
     * @throws ValidationException
     */
    public function enforcePolicy(string $password, ?User $user = null, string $field = 'password'): void
    {
        $errors = $this->validatePassword($password, $user);

        if (! empty($errors)) {
            throw ValidationException::withMessages([$field => $errors]);
        }
    }

    /**
     * Human-readable summary of the active password policy (for display on
     * password-change forms).
     *
     * @return array<int, string>
     */
    public function passwordRequirements(): array
    {
        $minLength = (int) SystemSetting::getValue('password_min_length', '8');
        $requirements = ["At least {$minLength} characters"];

        if (SystemSetting::getValue('password_require_uppercase', '1') === '1') {
            $requirements[] = 'One uppercase letter';
        }
        if (SystemSetting::getValue('password_require_lowercase', '1') === '1') {
            $requirements[] = 'One lowercase letter';
        }
        if (SystemSetting::getValue('password_require_number', '1') === '1') {
            $requirements[] = 'One number';
        }
        if (SystemSetting::getValue('password_require_special', '0') === '1') {
            $requirements[] = 'One special character';
        }

        $historyLimit = (int) SystemSetting::getValue('password_history_limit', '5');
        if ($historyLimit > 0) {
            $requirements[] = "Cannot reuse the last {$historyLimit} passwords";
        }

        return $requirements;
    }

    public function recordPassword(User $user, string $password): void
    {
        PasswordHistory::create([
            'user_id' => $user->id,
            'password' => Hash::make($password),
        ]);

        $historyLimit = (int) SystemSetting::getValue('password_history_limit', '5');
        if ($historyLimit > 0) {
            $keepIds = PasswordHistory::where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->limit($historyLimit)
                ->pluck('id');

            PasswordHistory::where('user_id', $user->id)
                ->whereNotIn('id', $keepIds)
                ->delete();
        }
    }

    public function isAccountLocked(User $user): bool
    {
        if (!$user->locked_at) return false;

        $lockDuration = (int) SystemSetting::getValue('account_lockout_duration', '30');
        $unlockTime = $user->locked_at->copy()->addMinutes($lockDuration);

        if ($unlockTime->isFuture()) {
            return true;
        }

        $user->update(['locked_at' => null]);
        return false;
    }

    public function recordFailedLogin(string $username, string $reason = 'Invalid credentials'): FailedLogin
    {
        $ip = request()->ip();
        $ua = request()->userAgent();

        $failed = FailedLogin::create([
            'username' => $username,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'browser' => AuditLogService::parseBrowser($ua),
            'os' => self::parseOs($ua),
            'reason' => $reason,
        ]);

        $maxAttempts = max(1, (int) SystemSetting::getValue('account_lockout_attempts', '5'));
        $recentFailures = FailedLogin::where('username', $username)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();

        if ($recentFailures >= $maxAttempts) {
            $user = User::where('username', $username)->first();
            if ($user && !$user->locked_at) {
                $user->update(['locked_at' => now()]);
                $failed->update(['is_flagged' => true]);

                AuditLogService::log(
                    action: 'Account Locked',
                    module: 'Security',
                    description: "Account locked after {$recentFailures} failed attempts: {$username}",
                );
            }
        } elseif ($recentFailures >= 3 && !$failed->is_flagged) {
            $failed->update(['is_flagged' => true]);
        }

        return $failed;
    }

    public function unlockAccount(User $user): void
    {
        $user->update(['locked_at' => null]);

        AuditLogService::log(
            action: 'Account Unlocked',
            module: 'Security',
            description: "Account unlocked: {$user->username}",
            recordType: 'User',
            recordId: $user->id,
        );
    }

    public function getFailedLogins(int $limit = 50)
    {
        return FailedLogin::latest()->paginate($limit);
    }

    public function getSecurityStats(): array
    {
        return [
            'locked_accounts' => User::whereNotNull('locked_at')
                ->where('locked_at', '>', now()->subHours(24))
                ->count(),
            'failed_logins_today' => FailedLogin::whereDate('created_at', today())->count(),
            'failed_logins_week' => FailedLogin::where('created_at', '>=', now()->subWeek())->count(),
            'flagged_attempts' => FailedLogin::where('is_flagged', true)
                ->where('created_at', '>=', now()->subWeek())
                ->count(),
            'unique_ips_today' => FailedLogin::whereDate('created_at', today())
                ->distinct('ip_address')
                ->count(),
        ];
    }

    public static function parseOs(?string $ua): ?string
    {
        if (!$ua) return null;
        $ua = strtolower($ua);
        return match(true) {
            str_contains($ua, 'windows') => 'Windows',
            str_contains($ua, 'mac os') => 'macOS',
            str_contains($ua, 'linux') => 'Linux',
            str_contains($ua, 'android') => 'Android',
            str_contains($ua, 'iphone') || str_contains($ua, 'ipad') => 'iOS',
            default => 'Other',
        };
    }
}
