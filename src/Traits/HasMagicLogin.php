<?php

namespace NextMigrant\MagicLinkLogin\Traits;

use NextMigrant\MagicLinkLogin\Models\LoginToken;

trait HasMagicLogin
{
    /**
     * Get the login tokens for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function loginTokens()
    {
        return $this->hasMany(LoginToken::class);
    }
}
