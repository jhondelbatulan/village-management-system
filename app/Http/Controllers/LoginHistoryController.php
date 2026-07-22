<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use App\Models\User;
use App\Services\PasswordService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginHistoryController extends Controller
{
    public function __construct(protected PasswordService $passwordService) {}

    public function index(Request $request): View
    {
        $query = LoginHistory::with('user.role');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('login_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('login_at', '<=', $request->date_to);
        }

        $loginHistory = $query->orderByDesc('login_at')->paginate(25)->withQueryString();
        $users = User::where('status', 'active')->orderBy('username')->get();

        return view('system-admin.login-history', compact('loginHistory', 'users'));
    }

    public function failedLogins(Request $request): View
    {
        $failedLogins = $this->passwordService->getFailedLogins(25);
        $securityStats = $this->passwordService->getSecurityStats();

        return view('system-admin.failed-logins', compact('failedLogins', 'securityStats'));
    }
}
