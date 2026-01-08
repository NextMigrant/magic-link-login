<?php

namespace NextMigrant\MagicLinkLogin\Services\Auth;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use NextMigrant\MagicLinkLogin\Models\LoginToken;

class AuthenticationService
{
    public static function generateEmailVerificationUrl($user): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(Config::get('auth.verification.expire', 24 * 60)),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );
    }

    public static function generateMagicLoginUrl($user): string
    {
        $tokenInserted = false;
        $tries = 0;
        $plaintextToken = null;
        $expiresAt = now()->addMinutes(15);

        do {
            $tries++;
            try {
                $plaintextToken = Str::random(32);

                $user->loginTokens()->create([
                    'token' => hash('sha256', $plaintextToken),
                    'expires_at' => $expiresAt,
                    'user_id' => $user->id,
                ]);
                $tokenInserted = true;
            } catch (\Exception $e) {
                if ($tries >= 3) {
                    throw new \RuntimeException('Failed to generate login token after 3 attempts');
                }
            }
        } while (! $tokenInserted && $tries < 3);

        return URL::temporarySignedRoute(
            'verify-login',
            $expiresAt,
            [
                'token' => $plaintextToken,
            ]
        );
    }

    public static function verifyMagicLoginToken(string $token): object
    {
        $token = LoginToken::whereToken(hash('sha256', $token))->first();

        if (! $token || ! $token->isValid()) {
            return (object) ['success' => false];
        }

        $token->consume();

        return (object) [
            'success' => true,
            'user' => $token->user,
        ];
    }
}
