<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\FailedLogin;
use App\Models\LoginHistory;
use App\Services\AuditLogService;
use App\Services\LoginLockoutService;
use App\Services\PasswordService;
use App\Support\RoleRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        private LoginLockoutService $lockout
    ) {}

    public function showLoginForm(): View|RedirectResponse
    {
        if (auth()->check()) {
            return $this->redirectByRole();
        }

        $username = request('username') ?? old('username');
        $lockSeconds = is_string($username) && $username !== ''
            ? $this->lockout->remainingLockSeconds($username)
            : 0;

        return view('auth.login', ['lockSeconds' => $lockSeconds]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('username', 'password');
        $remember = $request->boolean('remember');
        $username = $credentials['username'];

        $lockedFor = $this->lockout->remainingLockSeconds($username);
        if ($lockedFor > 0) {
            return $this->loginRedirect($username, $this->lockedMessage($lockedFor));
        }

        if (!Auth::attempt($credentials, $remember)) {
            $this->recordLoginFailure($username, $request);

            $lockSeconds = $this->lockout->recordFailure($username);

            if ($lockSeconds > 0) {
                return $this->loginRedirect($username, $this->lockedMessage($lockSeconds));
            }

            return $this->loginRedirect($username, 'Invalid username or password. Please try again.');
        }

        $user = Auth::user();

        if ($user->status !== 'active') {
            Auth::logout();

            $this->recordLoginFailure($username, $request, 'Account deactivated');

            return $this->loginRedirect($username, 'Your account has been deactivated. Please contact an administrator.');
        }

        if ($user->role?->role_slug === 'resident' && !$user->household) {
            Auth::logout();

            $this->recordLoginFailure($username, $request, 'Household removed');

            return $this->loginRedirect($username, 'Invalid username or password. Please try again.');
        }

        // An active account must still wait for its assigned household to be activated.
        if ($user->household && $user->household->status !== 'active') {
            Auth::logout();

            $this->recordLoginFailure($username, $request, 'Household inactive');

            return $this->loginRedirect($username, 'Your assigned household is not yet activated. Please wait until your household is activated before logging in.');
        }

        $this->lockout->clear($username);

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

    private function loginRedirect(string $username, string $message): RedirectResponse
    {
        return redirect()
            ->route('login', ['username' => $username])
            ->withErrors(['username' => $message])
            ->withInput(['username' => $username]);
    }

    private function lockedMessage(int $seconds): string
    {
        return 'Too many failed login attempts. Please try again in '
            . $seconds . ' second' . ($seconds === 1 ? '' : 's') . '.';
    }

    private function redirectByRole(): RedirectResponse
    {
        $user = auth()->user();
        $routeName = RoleRedirect::routeName($user?->role?->role_slug);

        if ($routeName === null) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'username' => 'Your account is not linked to a valid role. Please contact an administrator.',
            ]);
        }

        // A brand-new account must finish its one-time setup first.
        if ($user?->must_setup) {
            return redirect()->route('account.setup.show');
        }

        return redirect()->route($routeName);
    }
}
