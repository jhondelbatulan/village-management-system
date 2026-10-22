<?php

namespace Tests\Feature;

use App\Mail\LoginCredentialsMail;
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

    public function test_forgot_password_page_renders(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Send Login Credentials');
    }

    public function test_account_email_receives_an_auto_generated_password_that_works_on_login(): void
    {
        Mail::fake();

        $this->post(route('password.email'), ['email' => $this->user->email])
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

        // The emailed credentials log the user straight in.
        $this->post(route('login.submit'), [
            'username' => $this->user->username,
            'password' => $plainPassword,
        ])->assertRedirect(route('dashboard.super_admin'));

        $this->assertAuthenticated();
    }

    public function test_credentials_email_is_sent_through_the_mail_pipeline(): void
    {
        // Not faked: renders the markdown mail and delivers to the array
        // transport. A rendering failure would surface as the error flash.
        $this->post(route('password.email'), ['email' => $this->user->email])
            ->assertSessionHas('success');
    }

    public function test_unknown_email_gets_the_same_generic_success_response(): void
    {
        Mail::fake();

        $this->post(route('password.email'), ['email' => 'nobody@example.com'])
            ->assertSessionHas('success');

        Mail::assertNothingSent(LoginCredentialsMail::class);
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

        // Forgot-password issues new credentials and clears the lockout…
        Mail::fake();
        $this->post(route('password.email'), ['email' => $this->user->email])
            ->assertSessionHas('success');

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
