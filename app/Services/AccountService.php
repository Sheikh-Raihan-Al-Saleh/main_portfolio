<?php

namespace App\Services;

use App\Models\User;

/**
 * The signed-in admin's own account (Settings pages).
 */
class AccountService
{
    /**
     * Update name and email. Changing the email clears its verification.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user;
    }

    public function updatePassword(User $user, string $password): void
    {
        // The model's "hashed" cast hashes the value on save.
        $user->update(['password' => $password]);
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
