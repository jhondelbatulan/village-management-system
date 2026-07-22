<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PasswordService;
use App\Services\SystemSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PasswordSecurityController extends Controller
{
    public function __construct(protected PasswordService $passwordService) {}

    public function index(): View
    {
        $settings = SystemSettingService::getSecuritySettings();
        $lockedUsers = User::whereNotNull('locked_at')
            ->where('locked_at', '>', now()->subHours(24))
            ->with('role')
            ->get();
        $securityStats = $this->passwordService->getSecurityStats();

        return view('system-admin.password-security', compact('settings', 'lockedUsers', 'securityStats'));
    }

    public function updateSettings(\Illuminate\Http\Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password_min_length' => 'required|integer|min:4|max:64',
            'password_require_uppercase' => 'required|in:0,1',
            'password_require_lowercase' => 'required|in:0,1',
            'password_require_number' => 'required|in:0,1',
            'password_require_special' => 'required|in:0,1',
            'password_history_limit' => 'required|integer|min:0|max:20',
            'account_lockout_attempts' => 'required|integer|min:1|max:20',
            'account_lockout_duration' => 'required|integer|min:1|max:1440',
        ]);

        foreach ($validated as $key => $value) {
            SystemSettingService::set($key, $value);
        }

        return back()->with('success', 'Password security settings updated.');
    }

    public function unlock(User $user): RedirectResponse
    {
        $this->passwordService->unlockAccount($user);
        return back()->with('success', "Account {$user->username} unlocked.");
    }
}
