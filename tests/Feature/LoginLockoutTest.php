<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoginLockoutTest extends TestCase
{
    use RefreshDatabase;

    /** Long enough to wait out any lock tier exercised by these tests (max 60s). */
    private const LOCK_WAIT = 65;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $role = Role::where('role_slug', 'super_admin')->firstOrFail();

        $this->user = User::create([
            'role_id' => $role->id,
            'username' => 'locktester',
            'email' => 'locktester@gmail.com',
            'password' => 'CorrectPassword1',
            'first_name' => 'Lock',
            'last_name' => 'Tester',
            'status' => 'active',
        ]);
    }

    private function attempt(string $username, string $password = 'wrong-password')
    {
        return $this->post(route('login.submit'), [
            'username' => $username,
            'password' => $password,
        ]);
    }

    private function lastError(): string
    {
        return session('errors')->first();
    }

    private function failTimes(string $username, int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $this->attempt($username);

            if ($i < $count) {
                $this->travel(self::LOCK_WAIT)->seconds();
            }
        }
    }

    public function test_three_consecutive_failures_lock_login_for_15_seconds(): void
    {
        $this->attempt($this->user->username);
        $this->attempt($this->user->username);
        $this->assertSame(
            'Invalid username or password. Please try again.',
            $this->lastError()
        );

        $this->attempt($this->user->username);

        $this->assertStringContainsString('15 second', $this->lastError());

        // Even the correct password is rejected while the timer runs.
        $this->attempt($this->user->username, 'CorrectPassword1');

        $this->assertGuest();
        $this->assertStringContainsString('15 second', $this->lastError());
    }

    public function test_lockout_redirects_to_login_with_username_so_the_timer_survives_a_refresh(): void
    {
        $this->failTimes($this->user->username, 3);

        $first = $this->get(route('login', ['username' => $this->user->username]));
        $first->assertOk()
            ->assertSee('id="lockoutTimer"', false)
            ->assertSee('data-until=', false);
        $this->assertMatchesRegularExpression('/id="loginBtn"\s+disabled/', $first->getContent());

        // A second (refreshed) request still shows the countdown — the state
        // lives server-side, not in the flash session.
        $second = $this->get(route('login', ['username' => $this->user->username]));
        $second->assertOk()
            ->assertSee('id="lockoutTimer"', false);
        $this->assertMatchesRegularExpression('/id="loginBtn"\s+disabled/', $second->getContent());
    }

    public function test_six_consecutive_failures_escalate_to_30_seconds(): void
    {
        $this->failTimes($this->user->username, 6);

        $this->assertStringContainsString('30 second', $this->lastError());

        $this->attempt($this->user->username, 'CorrectPassword1');

        $this->assertGuest();
        $this->assertStringContainsString('30 second', $this->lastError());
    }

    public function test_nine_consecutive_failures_escalate_to_60_seconds(): void
    {
        $this->failTimes($this->user->username, 9);

        $this->assertStringContainsString('60 second', $this->lastError());

        $this->attempt($this->user->username, 'CorrectPassword1');

        $this->assertGuest();
        $this->assertStringContainsString('60 second', $this->lastError());
    }

    public function test_successful_login_resets_the_failure_counter(): void
    {
        $this->attempt($this->user->username);
        $this->attempt($this->user->username);

        $this->attempt($this->user->username, 'CorrectPassword1')
            ->assertRedirect(route('dashboard.super_admin'));

        $this->assertAuthenticated();

        auth()->logout();

        // Counter must have been cleared: two more failures are still below
        // the 3-attempt threshold, so the correct password still works.
        $this->attempt($this->user->username);
        $this->attempt($this->user->username);
        $this->assertSame(
            'Invalid username or password. Please try again.',
            $this->lastError()
        );

        $this->attempt($this->user->username, 'CorrectPassword1')
            ->assertRedirect(route('dashboard.super_admin'));
    }

    public function test_lockout_is_tracked_per_username(): void
    {
        $this->failTimes('ghost-user-that-does-not-exist', 3);

        $this->get(route('login', ['username' => 'ghost-user-that-does-not-exist']))
            ->assertOk()
            ->assertSee('id="lockoutTimer"', false);

        // A different account can still log in during someone else's lockout.
        $this->attempt($this->user->username, 'CorrectPassword1')
            ->assertRedirect(route('dashboard.super_admin'));
    }

    public function test_fresh_login_page_has_no_lockout_timer(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('id="lockoutTimer"', false);
    }

    public function test_lock_is_not_affected_by_old_account_lock_system(): void
    {
        // Legacy users.locked_at (minutes-based) must not block login anymore;
        // only the new seconds-based timer governs the lockout.
        $this->user->forceFill(['locked_at' => now()])->save();

        $this->attempt($this->user->username, 'CorrectPassword1')
            ->assertRedirect(route('dashboard.super_admin'));
    }
}
