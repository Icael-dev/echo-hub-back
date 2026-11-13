<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RefreshToken extends Model
{
    protected $fillable = [
        'tokenable_id',
        'tokenable_type',
        'token',
        'expires_at',
        'ip_address',
        'user_agent',
        'revoked_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * Get the owner of the refresh token.
     */
    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if the token is still valid (not expired and not revoked).
     */
    public function isValid(): bool
    {
        return $this->revoked_at === null && $this->expires_at->isFuture();
    }

    /**
     * Revoke this token.
     */
    public function revoke(): void
    {
        $this->update(['revoked_at' => now()]);
    }

    /**
     * Scope to get only valid tokens.
     */
    public function scopeValid($query)
    {
        return $query->whereNull('revoked_at')
            ->where('expires_at', '>', now());
    }
}
