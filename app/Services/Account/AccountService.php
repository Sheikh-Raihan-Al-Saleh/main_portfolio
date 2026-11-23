<?php

namespace App\Services\Account;

use App\Models\User;
use Illuminate\Support\Arr;

/**
 * Account module: the signed-in admin's own profile and password.
 */
class AccountService
{
    /** Form fields saved to the table. */
    private const FIELDS = ['name', 'email'];

    /**
     * Update name and email. Changing the email clears its verification.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->fill(Arr::only($data, self::FIELDS));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user;
    }

    /**
     * Save a new password for the user.
     */
    public function updatePassword(User $user, string $password): void
    {
        // The model's "hashed" cast hashes the value on save.
        $user->update(['password' => $password]);
    }

    /**
     * Delete the user's account.
     */
    public function delete(User $user): void
    {
        $user->delete();
    }
}
