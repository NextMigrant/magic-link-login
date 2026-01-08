<?php

use Illuminate\Support\Facades\Route;
use NextMigrant\MagicLinkLogin\Http\Controllers\Admin\AuthController;
use NextMigrant\MagicLinkLogin\Livewire\Admin\Auth\Login;

Route::middleware('guest')->group(function () {
    Route::get('/hq/login', Login::class)->name('admin.login-page');

    Route::get('verify-login/{token}', [AuthController::class, 'login'])
        ->middleware('throttle:5,15')
        ->name('verify-login');
});
