<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountSetupRequest;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\PasswordService;
use App\Support\RoleRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountSetupController extends Controller
{
    public function __construct(private PasswordService $passwordService) {}

    /**
     * One-time setup screen shown to freshly created accounts before they are
     * allowed to open their dashboard.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->must_setup) {
            return redirect()->to($this->dashboardUrl($user));
        }

        return view('auth.setup-account', [
            'user' => $user,
            'requirements' => $this->passwordService->passwordRequirements(),
        ]);
    }

    /**
     * Save the personal information, email, contact number and the new password,
     * then release the account to its dashboard.
     */
    public function store(AccountSetupRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->must_setup) {
            return redirect()->to($this->dashboardUrl($user));
        }

        $data = $request->validated();

        // The chosen password must satisfy the configured password policy and
        // may not reuse the temporary password the account was created with.
        $this->passwordService->enforcePolicy($data['password'], $user);

        $user->update([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'suffix' => $data['suffix'] ?? null,
            'email' => $data['email'],
            'contact_number' => $data['contact_number'],
            'password' => bcrypt($data['password']),
            'must_setup' => false,
        ]);

        $this->passwordService->recordPassword($user, $data['password']);

        try {
            AuditLogService::log('Complete Account Setup', 'Authentication', "Completed initial account setup for {$user->username}.");
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->to($this->dashboardUrl($user))
            ->with('success', 'Your account has been set up successfully. Welcome!');
    }

    private function dashboardUrl(User $user): string
    {
        $routeName = RoleRedirect::routeName($user->role?->role_slug);

        return $routeName !== null ? route($routeName) : url('/');
    }
}
