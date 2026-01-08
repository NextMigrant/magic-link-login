<?php

namespace NextMigrant\MagicLinkLogin;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use NextMigrant\MagicLinkLogin\Livewire\Admin\Auth\Login;

class MagicLinkLoginServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'magic-link-login');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Livewire::component('magic-link-login::admin.auth.login', Login::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallCommand::class,
            ]);
        }
    }
}
