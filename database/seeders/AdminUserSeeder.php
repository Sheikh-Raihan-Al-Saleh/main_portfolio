<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Creates (or refreshes) the single admin account from config/portfolio.php.
 *
 * Safe to re-run: the account is matched on email, so running it again resets
 * the password to whatever ADMIN_PASSWORD currently holds.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = (string) config('portfolio.admin.password');

        // Never let a production deploy fall back to the placeholder password.
        if (app()->isProduction() && in_array($password, ['', 'password'], true)) {
            throw new RuntimeException('Set ADMIN_PASSWORD in .env before seeding the admin account in production.');
        }

        User::updateOrCreate(
            ['email' => config('portfolio.admin.email')],
            [
                'name' => config('portfolio.admin.name'),
                'password' => $password,
                'is_admin' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}
