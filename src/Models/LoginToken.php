<?php

namespace NextMigrant\MagicLinkLogin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginToken extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime:Y-m-d',
            'consumed_at' => 'datetime:Y-m-d',
        ];
    }

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function isValid(): bool
    {
        return ! $this->isExpired() && ! $this->isConsumed();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isBefore(now());
    }

    public function isConsumed(): bool
    {
        return $this->consumed_at !== null;
    }

    public function consume(): void
    {
        $this->consumed_at = now();
        $this->save();
    }
}
