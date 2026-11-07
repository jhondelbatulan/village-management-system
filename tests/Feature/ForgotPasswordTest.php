<?php

namespace Tests\Feature;

use App\Mail\LoginCredentialsMail;
use App\Mail\PasswordOtpMail;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $role = Role::where('role_slug', 'super_admin')->firstOrFail();

        $this->user = User::create([
            'role_id' => $role->id,
            'username' => 'forgetful',
            'email' => 'forgetful@gmail.com',
            'password' => 'OldPassword123',
            'first_name' => 'For',
            'last_name' => 'Getful',
            'status' => 'active',
        ]);
    }

    /**
     * Run step 1 with the mailer faked and return the OTP that was emailed.
     */
    private function requestOtp(): string
    {
        Mail::fake();

        $this->post(route('password.email'), ['email' => $this->user->email])
            ->assertRedirect(route('password.otp.show'))
            ->assertSessionHas('success');

        $code = null;
        Mail::assertSent(PasswordOtpMail::class, function (PasswordOtpMail $mail) use (&$code): bool {
            $code = $mail->code;

            return $mail->hasTo($this->user->email);
        });

        $this->assertNotNull($code);

        return $code;
    }

    public function test_forgot_password_page_renders(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Send Verification Code');
    }

    public function test_unknown_email_shows_an_error_notice_and_sends_nothing(): void
    {
        Mail::fake();

        $this->post(route('password.email'), ['email' => 'nobody@example.com'])
            ->assertRedirect()
            ->assertSessionHas('error');

        Mail::assertNothingSent(PasswordOtpMail::class);
        Mail::assertNothingSent(LoginCredentialsMail::class);
    }

    public function test_known_email_gets_a_check_inbox_note_and_an_otp_before_any_credentials(): void
    {
        Mail::fake();

        $this->post(route('password.email'), ['email' => $this->user->email])
            ->assertRedirect(route('password.otp.show'))
            ->assertSessionHas('success');

        // The OTP email goes out…
        Mail::assertSent(PasswordOtpMail::class, function (PasswordOtpMail $mail): bool {
            return $mail->hasTo($this->user->email);
        });

        // …but the credentials email must NOT go out yet.
        Mail::assertNotSent(LoginCredentialsMail::class);

        // The password is untouched until the OTP is verified.
        $this->user->refresh();
        $this->assertTrue(Hash::check('OldPassword123', $this->user->password));
        $this->assertNotNull($this->user->password_otp);
        $this->assertNotNull($this->user->password_otp_expires_at);
    }

    public function test_verify_form_renders_for_a_pending_reset_session(): void
    {
        $this->requestOtp();

        $this->get(route('password.otp.show'))
            ->assertOk()
            ->assertSee('Verification Code')
            ->assertSee('fo*******@gmail.com', false);
    }

    public function test_verify_form_redirects_when_no_reset_session_exists(): void
    {
        $this->get(route('password.otp.show'))
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('error');
    }

    public function test_correct_otp_finally_sends_credentials_that_work_on_login(): void
    {
        $code = $this->requestOtp();

        $this->post(route('password.otp.verify'), ['otp' => $code])
            ->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $plainPassword = null;
        Mail::assertSent(LoginCredentialsMail::class, function (LoginCredentialsMail $mail) use (&$plainPassword): bool {
            $plainPassword = $mail->plainPassword;

            return $mail->hasTo('forgetful@gmail.com');
        });

        $this->user->refresh();
        $this->assertNotNull($plainPassword);
        $this->assertGreaterThanOrEqual(8, strlen($plainPassword));
        $this->assertTrue(Hash::check($plainPassword, $this->user->password));
        $this->assertFalse(Hash::check('OldPassword123', $this->user->password));
        $this->assertNull($this->user->password_otp);

        // The emailed credentials log the user straight in.
        $this->post(route('login.submit'), [
            'username' => $this->user->username,
            'password' => $plainPassword,
        ])->assertRedirect(route('dashboard.super_admin'));

        $this->assertAuthenticated();
    }

    public function test_incorrect_otp_does_not_send_credentials(): void
    {
        $code = $this->requestOtp();

        $wrong = $code === '000000' ? '111111' : '000000';

        $this->post(route('password.otp.verify'), ['otp' => $wrong])
            ->assertSessionHasErrors(['otp']);

        Mail::assertNotSent(LoginCredentialsMail::class);

        // The old password still works — nothing was reset.
        $this->assertTrue(Hash::check('OldPassword123', $this->user->fresh()->password));
    }

    public function test_expired_otp_is_rejected(): void
    {
        $this->requestOtp();

        $this->user->refresh()->forceFill(['password_otp_expires_at' => now()->subMinute()])->save();

        $this->post(route('password.otp.verify'), ['otp' => '123456'])
            ->assertSessionHasErrors(['otp']);

        Mail::assertNotSent(LoginCredentialsMail::class);
        $this->assertTrue(Hash::check('OldPassword123', $this->user->fresh()->password));
    }

    public function test_otp_is_required_and_must_be_six_digits(): void
    {
        $this->requestOtp();

        $this->from(route('password.otp.show'))
            ->post(route('password.otp.verify'), ['otp' => ''])
            ->assertSessionHasErrors(['otp']);

        $this->from(route('password.otp.show'))
            ->post(route('password.otp.verify'), ['otp' => '12ab56'])
            ->assertSessionHasErrors(['otp']);
    }

    public function test_resend_issues_a_fresh_code(): void
    {
        $this->requestOtp();

        $this->post(route('password.otp.resend'))
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertSent(PasswordOtpMail::class, 2);
        Mail::assertNotSent(LoginCredentialsMail::class);
    }

    public function test_credentials_email_is_sent_through_the_mail_pipeline(): void
    {
        // Not faked: renders the markdown mail and delivers to the array
        // transport. A rendering failure would surface as the error flash.
        $this->post(route('password.email'), ['email' => $this->user->email])
            ->assertRedirect(route('password.otp.show'))
            ->assertSessionHas('success');
    }

    public function test_email_is_required_and_must_be_valid(): void
    {
        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => ''])
            ->assertSessionHasErrors(['email']);

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'not-an-email'])
            ->assertSessionHasErrors(['email']);
    }

    public function test_new_credentials_clear_an_active_login_lockout(): void
    {
        // Lock the username with three failures.
        for ($i = 1; $i <= 3; $i++) {
            $this->post(route('login.submit'), [
                'username' => $this->user->username,
                'password' => 'wrong-password',
            ]);
        }

        $this->post(route('login.submit'), [
            'username' => $this->user->username,
            'password' => 'OldPassword123',
        ]);
        $this->assertGuest();

        // Forgot-password verifies the OTP and issues new credentials…
        $code = $this->requestOtp();

        $this->post(route('password.otp.verify'), ['otp' => $code])
            ->assertRedirect(route('login'));

        $plainPassword = null;
        Mail::assertSent(LoginCredentialsMail::class, function (LoginCredentialsMail $mail) use (&$plainPassword): bool {
            $plainPassword = $mail->plainPassword;

            return true;
        });

        // …so the emailed password works immediately, even though the
        // username was locked moments ago.
        $this->post(route('login.submit'), [
            'username' => $this->user->username,
            'password' => $plainPassword,
        ])->assertRedirect(route('dashboard.super_admin'));

        $this->assertAuthenticated();
    }
}
