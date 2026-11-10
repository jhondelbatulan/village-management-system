<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use App\Support\RoleRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ConfirmPasswordController extends Controller
{
    /**
     * Fallback page for the confirmation prompt. Normally the in-page modal
     * handles it; this page is used when a protected GET link (e.g. a report
     * download) is opened without JavaScript.
     */
    public function show(Request $request): View
    {
        return view('auth.confirm-password', [
            'backUrl' => $request->headers->get('referer')
                ?: $this->homeUrl($request),
            'actionMessage' => session('password_action_message'),
        ]);
    }

    /**
     * Verify the current user's password and stamp the confirmation so the
     * pending action may run.
     */
    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->input('password'), $user->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The password you entered is incorrect.',
                ], 422);
            }

            return back()->withErrors(['password' => 'The password you entered is incorrect.']);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        try {
            AuditLogService::log('Confirm Password', 'Authentication', 'Password confirmed for a sensitive action.');
        } catch (\Throwable $e) {
            report($e);
        }

        if ($request->expectsJson()) {
            return response()->json(['confirmed' => true]);
        }

        return redirect()->intended($this->homeUrl($request));
    }

    private function homeUrl(Request $request): string
    {
        $routeName = RoleRedirect::routeName($request->user()?->role?->role_slug);

        return $routeName !== null ? route($routeName) : url('/');
    }
}
