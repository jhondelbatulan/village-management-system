<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\LoginCredentialsMail;
use App\Mail\PasswordOtpMail;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\LoginLockoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    private const SESSION_KEY = 'password_reset_email';

    private const OTP_MINUTES = 10;

    public function showLinkRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Step 1: look up the email. Unknown emails get an error notice; known
     * emails get a success note and a 6-digit OTP sent before any credentials.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Please enter your account email address.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        $email = $request->input('email');

        $user = User::where('email', $email)->first();

        if (! $user) {
            return back()
                ->withInput(['email' => $email])
                ->with('error', 'No account exists with that email address. Please check the address or contact your administrator.');
        }

        try {
            $this->issueOtp($user);
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput(['email' => $email])
                ->with('error', 'We could not send the verification email. Please contact your administrator.');
        }

        $request->session()->put(self::SESSION_KEY, $user->email);

        return redirect()
            ->route('password.otp.show')
            ->with('success', 'We found your account. A verification code has been sent to ' . $this->maskEmail($user->email) . '. Please check your inbox and enter the code to receive your login credentials.');
    }

    /**
     * Step 2: the OTP entry form.
     */
    public function showVerifyForm(): View|RedirectResponse
    {
        $email = session(self::SESSION_KEY);

        if (! is_string($email) || $email === '') {
            return redirect()
                ->route('password.request')
                ->with('error', 'Please request a verification code first.');
        }

        return view('auth.verify-otp', [
            'email' => $email,
            'maskedEmail' => $this->maskEmail($email),
            'minutes' => self::OTP_MINUTES,
        ]);
    }

    /**
     * Step 3: validate the OTP, then finally email the account credentials.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.required' => 'Please enter the 6-digit verification code.',
            'otp.digits' => 'The verification code must be exactly 6 digits.',
        ]);

        $email = session(self::SESSION_KEY);

        $user = is_string($email) && $email !== ''
            ? User::where('email', $email)->first()
            : null;

        if (! $user || ! $user->password_otp) {
            $request->session()->forget(self::SESSION_KEY);

            throw ValidationException::withMessages([
                'otp' => 'This verification session has expired. Please request a new code.',
            ]);
        }

        if (! $user->password_otp_expires_at || $user->password_otp_expires_at->isPast()) {
            $user->forceFill(['password_otp' => null, 'password_otp_expires_at' => null])->save();

            throw ValidationException::withMessages([
                'otp' => 'The verification code has expired. Please request a new one.',
            ]);
        }

        if (! Hash::check($request->input('otp'), $user->password_otp)) {
            throw ValidationException::withMessages([
                'otp' => 'The verification code you entered is incorrect. Please try again.',
            ]);
        }

        try {
            $plainPassword = Str::password(12, true, true, true, false);

            $user->forceFill([
                'password' => $plainPassword,
                'password_otp' => null,
                'password_otp_expires_at' => null,
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
                "OTP verified and login credentials reissued for user {$user->username} ({$user->email})."
            );
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->with('error', 'We could not send the credentials email. Please contact your administrator.');
        }

        $request->session()->forget(self::SESSION_KEY);

        return redirect()
            ->route('login')
            ->with('success', 'Verification successful. Your username and temporary password have been sent to ' . $this->maskEmail($user->email) . '. Please check your inbox.');
    }

    /**
     * Issue a fresh OTP for the account currently in the reset session.
     */
    public function resend(Request $request): RedirectResponse
    {
        $email = session(self::SESSION_KEY);

        $user = is_string($email) && $email !== ''
            ? User::where('email', $email)->first()
            : null;

        if (! $user) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()
                ->route('password.request')
                ->with('error', 'Please request a verification code first.');
        }

        try {
            $this->issueOtp($user);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'We could not resend the verification email. Please contact your administrator.');
        }

        return back()->with('success', 'A new verification code has been sent to ' . $this->maskEmail($user->email) . '. Please check your inbox.');
    }

    private function issueOtp(User $user): void
    {
        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'password_otp' => Hash::make($code),
            'password_otp_expires_at' => now()->addMinutes(self::OTP_MINUTES),
        ])->save();

        Mail::to($user->email)->send(new PasswordOtpMail($user, $code, self::OTP_MINUTES));
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);

        $visible = substr($local, 0, 2);

        return $visible . str_repeat('*', max(strlen($local) - 2, 1)) . '@' . $domain;
    }
}
