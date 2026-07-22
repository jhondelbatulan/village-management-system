<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SessionManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function __construct(protected SessionManagementService $sessionService) {}

    public function index(Request $request): View
    {
        $sessions = $this->sessionService->getActiveSessions();
        $users = User::where('status', 'active')->orderBy('username')->get();

        return view('system-admin.sessions', compact('sessions', 'users'));
    }

    public function terminate(string $sessionId): RedirectResponse
    {
        $this->sessionService->terminateSession($sessionId);
        return back()->with('success', 'Session terminated successfully.');
    }

    public function terminateAll(int $userId): RedirectResponse
    {
        $count = $this->sessionService->terminateAllUserSessions($userId);
        return back()->with('success', "{$count} session(s) terminated for the user.");
    }

    public function forceLogoutAll(): RedirectResponse
    {
        $count = $this->sessionService->forceLogoutAllSessions();
        return back()->with('success', "{$count} session(s) terminated successfully.");
    }
}
