<?php

namespace NextMigrant\MagicLinkLogin\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use NextMigrant\MagicLinkLogin\Services\Auth\AuthenticationService;

class AuthController extends Controller
{
    public function login(Request $request, string $token)
    {
        $verificationResult = AuthenticationService::verifyMagicLoginToken($token);

        if (! $request->hasValidSignature() || ! $verificationResult->success) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        Auth::login($verificationResult->user);

        $request->session()->regenerate();

        return redirect()->to(
            Filament::getPanel('admin')->getUrl()
        );
    }
}
