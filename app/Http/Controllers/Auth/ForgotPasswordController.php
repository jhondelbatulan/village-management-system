<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\LoginCredentialsMail;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\LoginLockoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Please enter your account email address.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if ($user) {
            try {
                $plainPassword = Str::password(12, true, true, false, false);

                $user->forceFill([
                    'password' => $plainPassword,
                    'remember_token' => Str::random(60),
                ])->save();

                // Make sure the freshly issued credentials can be used right away.
                app(LoginLockoutService::class)->clear($user->username);

                if ($user->locked_at) {
                    $user->forceFill(['locked_at' => null])->save();
                }

                Mail::to($user->email)->send(new LoginCredentialsMail($user, $plainPassword));

                AuditLogService::log(
                    'Forgot Password',
                    'Authentication',
                    "Login credentials reissued for user {$user->username} ({$user->email})."
                );
            } catch (\Throwable $e) {
                report($e);

                return back()
                    ->withInput(['email' => $request->input('email')])
                    ->with('error', 'We could not send the credentials email. Please contact your administrator.');
            }
        }

        return back()->with('success', 'If an account matches that email, your login credentials have been sent. Please check your inbox.');
    }
}
