<?php

namespace NextMigrant\MagicLinkLogin\Livewire\Admin\Auth;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;
use NextMigrant\MagicLinkLogin\Mail\Admin\MagicLoginLink;
use NextMigrant\MagicLinkLogin\Services\Auth\AuthenticationService;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    public string $email = '';

    public string $loginFailMessage = '';

    public function login()
    {
        $executed = RateLimiter::attempt(
            'admin-login:'.request()->ip(),
            2,
            $this->handleLogin(...),
            60 * 10
        );

        if (! $executed) {
            $this->loginFailMessage = 'Too many login attempts. Your access has been disabled for some time.';
            $this->dispatch('login-failed');

            return;
        }
    }

    private function handleLogin()
    {
        $validated = $this->validate();

        $userModel = config('auth.providers.users.model');
        $user = $userModel::where('email', $validated['email'])->first();

        if (! $user) {
            $this->loginFailMessage = 'These credentials do not match our records.';
            $this->dispatch('login-failed');
            return;
        }

        if (! $user->canAccessPanel(Filament::getPanel('admin'))) {
            $this->loginFailMessage = 'There seems to be a problem with your account. Please check with your manager.';
            $this->dispatch('login-failed');

            return;
        }

        $temporaryLoginLink = AuthenticationService::generateMagicLoginUrl($user);

        Mail::to($user)->queue(new MagicLoginLink($temporaryLoginLink));

        Session::flash('status', 'login-email-sent');
    }

    public function render()
    {
        return view('magic-link-login::livewire.admin.auth.login')->layoutData([
            'title' => config('app.name') . ' HQ',
            'meta' => [
                'meta_robots' => 'noindex, nofollow',
            ],
        ]);
    }

    public function rules()
    {
        return [
            'email' => 'required|string|email',
        ];
    }
}
