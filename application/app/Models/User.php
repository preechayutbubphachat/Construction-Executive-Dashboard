<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'mfa_enabled',
    'mfa_secret',
    'mfa_recovery_codes',
    'mfa_confirmed_at',
])]
#[Hidden(['password', 'remember_token', 'mfa_secret', 'mfa_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'mfa_enabled' => 'boolean',
            'mfa_secret' => 'encrypted',
            'mfa_recovery_codes' => 'encrypted:array',
            'mfa_confirmed_at' => 'datetime',
        ];
    }

    public function hasMfaEnabled(): bool
    {
        return $this->mfa_enabled && $this->mfa_confirmed_at !== null && ! empty($this->mfa_secret);
    }

    public function enableMfa(string $secret, array $recoveryCodes): void
    {
        $this->mfa_secret = $secret;
        $this->mfa_recovery_codes = $recoveryCodes;
        $this->mfa_confirmed_at = now();
        $this->mfa_enabled = true;
        $this->save();
    }

    public function disableMfa(): void
    {
        $this->mfa_enabled = false;
        $this->mfa_secret = null;
        $this->mfa_recovery_codes = null;
        $this->mfa_confirmed_at = null;
        $this->save();
    }
}
