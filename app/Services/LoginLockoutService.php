<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Seconds-based escalating login lockout.
 *
 * Consecutive failed logins for a username are counted in the cache:
 *   3, 4, 5 failures  -> 15 second lockout
 *   6, 7, 8 failures  -> 30 second lockout
 *   9, 10, 11 failures -> 60 second lockout
 *   ... and so on (the lock duration doubles every 3 failures).
 *
 * The counter resets when the user logs in successfully, and both the
 * counter and the lock are stored server-side so the timer survives
 * page refreshes.
 */
class LoginLockoutService
{
    public const BASE_ATTEMPTS = 3;

    public const BASE_SECONDS = 15;

    public const COUNTER_TTL_SECONDS = 86400;

    public function attempts(string $username): int
    {
        return (int) Cache::get($this->attemptsKey($username), 0);
    }

    public function lockSecondsFor(int $attempts): int
    {
        if ($attempts < self::BASE_ATTEMPTS) {
            return 0;
        }

        $tier = intdiv($attempts - self::BASE_ATTEMPTS, self::BASE_ATTEMPTS);

        return self::BASE_SECONDS * (2 ** $tier);
    }

    public function remainingLockSeconds(string $username): int
    {
        $unlockAt = (int) Cache::get($this->lockKey($username), 0);

        if ($unlockAt <= 0) {
            return 0;
        }

        $remaining = $unlockAt - now()->timestamp;

        return $remaining > 0 ? $remaining : 0;
    }

    /**
     * Record a failed login attempt and return the number of seconds the
     * username is now locked for (0 when no lock applies yet).
     */
    public function recordFailure(string $username): int
    {
        if ($this->remainingLockSeconds($username) > 0) {
            return $this->remainingLockSeconds($username);
        }

        $attempts = $this->attempts($username) + 1;
        Cache::put($this->attemptsKey($username), $attempts, self::COUNTER_TTL_SECONDS);

        $lockSeconds = $this->lockSecondsFor($attempts);

        if ($lockSeconds > 0) {
            Cache::put(
                $this->lockKey($username),
                now()->addSeconds($lockSeconds)->timestamp,
                $lockSeconds
            );
        }

        return $lockSeconds;
    }

    public function clear(string $username): void
    {
        Cache::forget($this->attemptsKey($username));
        Cache::forget($this->lockKey($username));
    }

    private function attemptsKey(string $username): string
    {
        return 'login_fail:' . Str::lower(trim($username));
    }

    private function lockKey(string $username): string
    {
        return 'login_lock:' . Str::lower(trim($username));
    }
}
