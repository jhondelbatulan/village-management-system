<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SessionManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SwitchController extends Controller
{
    public function __construct(
        private SessionManager $sessionManager
    ) {}

    public function index(): View
    {
        $ownerId = auth()->id();
        $pinnedSessions = $this->sessionManager->getPinnedSessions($ownerId);
        $availableAccounts = $this->sessionManager->getAvailableAccounts($ownerId);
        $isSwitched = $this->sessionManager->isSwitched();

        return view('sessions.index', compact('pinnedSessions', 'availableAccounts', 'isSwitched'));
    }

    public function switch(Request $request, int $userId): RedirectResponse
    {
        if ($userId === auth()->id()) {
            return back()->with('error', 'You are already logged in as this user.');
        }

        $success = $this->sessionManager->switchToUser($userId);

        if ($success) {
            $user = User::find($userId);
            if (!$user) {
                return back()->with('error', 'User not found.');
            }
            return redirect()->route($this->getDashboardRoute($user))
                ->with('success', 'Switched to ' . $user->full_name . ' (' . ($user->role?->role_slug ?? 'unknown') . ')');
        }

        return back()->with('error', 'Failed to switch account. User may be inactive.');
    }

    public function back(): RedirectResponse
    {
        $success = $this->sessionManager->switchBack();

        if ($success) {
            $user = auth()->user();
            if (!$user) {
                return redirect()->route('login');
            }
            return redirect()->route($this->getDashboardRoute($user))
                ->with('success', 'Switched back to ' . $user->full_name);
        }

        return back()->with('error', 'No original account to switch back to.');
    }

    public function pin(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'session_name' => 'nullable|string|max:50',
        ]);

        $this->sessionManager->pinAccount(
            auth()->id(),
            $request->user_id,
            $request->session_name
        );

        return back()->with('success', 'Account pinned for quick access.');
    }

    public function unpin(int $userId): RedirectResponse
    {
        $this->sessionManager->unpinAccount(auth()->id(), $userId);

        return back()->with('success', 'Account unpinned.');
    }

    public function quickSwitch(): View
    {
        $ownerId = auth()->id();
        $pinnedSessions = $this->sessionManager->getPinnedSessions($ownerId);
        $isSwitched = $this->sessionManager->isSwitched();

        return view('sessions.quick-switch', compact('pinnedSessions', 'isSwitched'));
    }

    private function getDashboardRoute(User $user): string
    {
        return match ($user->role?->role_slug) {
            'super_admin' => 'dashboard.super_admin',
            'admin' => 'dashboard.admin',
            'secretary' => 'dashboard.secretary',
            'treasurer' => 'dashboard.treasurer',
            'resident' => 'dashboard.resident',
            default => 'dashboard.super_admin',
        };
    }
}
