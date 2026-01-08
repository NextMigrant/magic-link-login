<?php

namespace NextMigrant\MagicLinkLogin\Tests\Feature\Admin\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use NextMigrant\MagicLinkLogin\Livewire\Admin\Auth\Login;
use NextMigrant\MagicLinkLogin\Mail\Admin\MagicLoginLink;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure admin role exists
        if (class_exists(Role::class)) {
            Role::create(['name' => 'admin']);
        }

        // Clear rate limiter
        RateLimiter::clear('admin-login:'.request()->ip());
    }

    public function test_login_page_can_be_rendered(): void
    {
        $this->get(route('admin.login-page'))
            ->assertSuccessful()
            ->assertSeeLivewire(Login::class);
    }

    public function test_users_can_request_magic_link(): void
    {
        Mail::fake();

        $userModel = config('auth.providers.users.model');
        $user = $userModel::factory()->create([
            'email' => 'admin@example.com',
        ]);

        if (method_exists($user, 'assignRole')) {
            $user->assignRole('admin');
        }

        Livewire::test(Login::class)
            ->set('email', 'admin@example.com')
            ->call('login')
            ->assertHasNoErrors()
            ->assertHasNoErrors()
            ->assertSee('Check your email');

        Mail::assertQueued(MagicLoginLink::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_unauthorized_users_cannot_login(): void
    {
        Mail::fake();

        $userModel = config('auth.providers.users.model');
        $user = $userModel::factory()->create([
            'email' => 'user@example.com',
        ]);

        // Ensure user does NOT have admin role

        Livewire::test(Login::class)
            ->set('email', 'user@example.com')
            ->call('login')
            ->assertDispatched('login-failed');

        Mail::assertNothingQueued();
    }

    public function test_non_existent_users_cannot_login(): void
    {
        Mail::fake();

        Livewire::test(Login::class)
            ->set('email', 'nonexistent@example.com')
            ->call('login')
            ->assertDispatched('login-failed');

        Mail::assertNothingQueued();
    }
}
