<?php

namespace App\Auth;

use App\Models\TblUser;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployerUserProvider implements UserProvider
{
    /**
     * Cached external TblUser resolved during retrieveByCredentials,
     * used by validateCredentials to check the password.
     */
    protected ?TblUser $externalUser = null;

    /**
     * Retrieve a user by the given credentials (username lookup only).
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (empty($credentials['username'])) {
            return null;
        }

        $this->externalUser = TblUser::where('u_username', $credentials['username'])->first();

        if (! $this->externalUser) {
            return null;
        }

        return $this->syncLocalUser($this->externalUser);
    }

    /**
     * Validate the given user against the provided credentials.
     * Supports bcrypt, MD5, SHA1, and plain-text passwords from the external DB.
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        if (! $this->externalUser) {
            return false;
        }

        $plain = $credentials['password'];
        $stored = $this->externalUser->u_password;

        // bcrypt / argon (Laravel default) — may throw if hash format is unrecognised
        try {
            if (Hash::check($plain, $stored)) {
                return true;
            }
        } catch (\RuntimeException) {
            // stored hash is not bcrypt/argon — fall through to other strategies
        }

        // MD5 (common in older PHP systems)
        if (hash('md5', $plain) === strtolower($stored)) {
            return true;
        }

        // SHA1
        if (hash('sha1', $plain) === strtolower($stored)) {
            return true;
        }

        // Plain text (last resort)
        if ($plain === $stored) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        return User::find($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     */
    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        $user = User::find($identifier);

        if ($user && $user->remember_token === $token) {
            return $user;
        }

        return null;
    }

    /**
     * Update the "remember me" token for the given user.
     */
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        $user->forceFill(['remember_token' => $token])->save();
    }

    /**
     * Rehash credentials if the hashing algorithm needs upgrading.
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // Not applicable — passwords live in the external database.
    }

    /**
     * Find or create a local User that mirrors the external TblUser.
     * Assigns the "employer" role on first creation.
     */
    protected function syncLocalUser(TblUser $external): User
    {
        $externalId = $external->getKey();

        $name        = trim($external->u_lname ?? '') ?: $external->u_username;
        $companyName = trim($external->u_fname ?? '') ?: null;

        $email = $external->u_email1
            ?: $external->u_username . '@employer.clab.local';

        $localUser = User::where('external_id', $externalId)->first();

        if (! $localUser) {
            $localUser = User::create([
                'name'         => $name,
                'company_name' => $companyName,
                'email'        => $email,
                'password'     => Hash::make(Str::random(32)), // unusable local password
                'username'     => $external->u_username,
                'phone'        => $external->u_mobileno ?? $external->u_contactno ?? null,
                'external_id'  => $externalId,
            ]);

            $localUser->assignRole('employer');
        } else {
            // Keep name/email/username/phone in sync on each login
            $localUser->update([
                'name'         => $name,
                'company_name' => $companyName,
                'email'        => $email,
                'username'     => $external->u_username,
                'phone'        => $external->u_mobileno ?? $external->u_contactno ?? null,
            ]);
        }

        return $localUser;
    }
}
