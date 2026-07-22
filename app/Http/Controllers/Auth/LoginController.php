<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\FailedLogin;
use App\Models\LoginHistory;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\PasswordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (auth()->check()) {
            return $this->redirectByRole();
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('username', 'password');
        $remember = $request->boolean('remember');
        $username = $credentials['username'];

        $user = User::where('username', $username)->first();

        if ($user && app(PasswordService::class)->isAccountLocked($user)) {
            $lockDuration = (int) SystemSetting::getValue('account_lockout_duration', '30');

            return back()
                ->withErrors([
                    'username' => "Your account is temporarily locked due to too many failed login attempts. Please try again in {$lockDuration} minutes or contact an administrator.",
                ])
                ->onlyInput('username');
        }

        if (!Auth::attempt($credentials, $remember)) {
            $failed = $this->recordLoginFailure($username, $request);

            $remainingAttempts = $this->getRemainingAttempts($username);

            if ($remainingAttempts !== null && $remainingAttempts <= 2 && $remainingAttempts > 0) {
                return back()
                    ->withErrors([
                        'username' => "Invalid username or password. WARNING: Your account will be locked after {$remainingAttempts} more failed attempt(s).",
                    ])
                    ->onlyInput('username');
            }

            if ($failed && $failed->is_flagged && $remainingAttempts !== null && $remainingAttempts <= 0) {
                $lockDuration = (int) SystemSetting::getValue('account_lockout_duration', '30');

                return back()
                    ->withErrors([
                        'username' => "Too many failed attempts. Your account has been locked for {$lockDuration} minutes.",
                    ])
                    ->onlyInput('username');
            }

            return back()
                ->withErrors(['username' => 'Invalid username or password. Please try again.'])
                ->onlyInput('username');
        }

        $user = Auth::user();

        if ($user->status !== 'active') {
            Auth::logout();

            $this->recordLoginFailure($username, $request, 'Account deactivated');

            return back()
                ->withErrors(['username' => 'Your account has been deactivated. Please contact an administrator.'])
                ->onlyInput('username');
        }

        if ($user->role?->role_slug === 'resident' && !$user->household) {
            Auth::logout();

            $this->recordLoginFailure($username, $request, 'Household removed');

            return back()
                ->withErrors(['username' => 'Invalid username or password. Please try again.'])
                ->onlyInput('username');
        }

        $user->update(['last_login' => now(), 'locked_at' => null]);

        try {
            $ua = $request->userAgent();
            LoginHistory::create([
                'user_id' => $user->id,
                'username' => $username,
                'login_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $ua,
                'browser' => AuditLogService::parseBrowser($ua),
                'status' => 'success',
            ]);

            AuditLogService::log('Login', 'Authentication', 'User logged in successfully.');
        } catch (\Throwable $e) {
            report($e);
        }

        $request->session()->regenerate();

        return $this->redirectByRole();
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user) {
            try {
                $lastLogin = LoginHistory::where('user_id', $user->id)
                    ->whereNull('logout_at')
                    ->latest('login_at')
                    ->first();

                if ($lastLogin) {
                    $duration = $lastLogin->login_at->diffInMinutes(now());
                    $lastLogin->update([
                        'logout_at' => now(),
                        'session_duration_minutes' => $duration,
                    ]);
                }

                AuditLogService::log('Logout', 'Authentication', 'User logged out.');
            } catch (\Throwable $e) {
                report($e);
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }

    private function recordLoginFailure(string $username, Request $request, string $reason = 'Invalid credentials'): ?FailedLogin
    {
        try {
            $failed = app(PasswordService::class)->recordFailedLogin($username, $reason);
        } catch (\Throwable $e) {
            report($e);
            $failed = null;
        }

        try {
            LoginHistory::create([
                'username' => $username,
                'login_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'failed',
                'failure_reason' => $reason,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            AuditLogService::log(
                action: 'Failed Login',
                module: 'Authentication',
                description: "Failed login attempt for username: {$username}",
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return $failed;
    }

    private function getRemainingAttempts(string $username): ?int
    {
        try {
            $maxAttempts = (int) SystemSetting::getValue('account_lockout_attempts', '5');
            $recentFailures = FailedLogin::where('username', $username)
                ->where('created_at', '>=', now()->subMinutes(15))
                ->count();

            $remaining = $maxAttempts - $recentFailures;
            return $remaining > 0 ? $remaining : 0;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function redirectByRole(): RedirectResponse
    {
        $role = auth()->user()->role?->role_slug;

        return match ($role) {
            'super_admin' => redirect()->route('dashboard.super_admin'),
            'admin' => redirect()->route('dashboard.admin'),
            'secretary' => redirect()->route('dashboard.secretary'),
            'treasurer' => redirect()->route('dashboard.treasurer'),
            'resident' => redirect()->route('dashboard.resident'),
            default => redirect()->route('login'),
        };
    }
}
