<?php

namespace App\Providers;

use App\Http\Requests\Concerns\PasswordValidationRules;
use App\Models\Project;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

/**
 * Fortify setup: the password reset action, the auth page views and the login rate limits.
 */
class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions: after a reset link is used, validate the new
     * password and save it (the model's "hashed" cast hashes it).
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(fn (): ResetsUserPasswords => new class implements ResetsUserPasswords
        {
            use PasswordValidationRules;

            /**
             * Validate and reset the user's forgotten password.
             *
             * @param  array<string, string>  $input
             */
            public function reset(User $user, array $input): void
            {
                Validator::make($input, ['password' => $this->passwordRules()])->validate();

                $user->forceFill(['password' => $input['password']])->save();
            }
        });
    }

    /**
     * Published project covers for the sign-in pages; the first one is shown
     * as the "featured work" card beside the founder photo.
     *
     * @return array<int, array{title: string, cover: string}>
     */
    private function showcase(): array
    {
        return Project::loginShowcase();
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn (Request $request) => Inertia::render('auth/Login', [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'status' => $request->session()->get('status'),
            'showcase' => $this->showcase(),
        ]));

        Fortify::resetPasswordView(fn (Request $request) => Inertia::render('auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
            'showcase' => $this->showcase(),
        ]));

        Fortify::requestPasswordResetLinkView(fn (Request $request) => Inertia::render('auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
            'showcase' => $this->showcase(),
        ]));

        Fortify::verifyEmailView(fn (Request $request) => Inertia::render('auth/VerifyEmail', [
            'status' => $request->session()->get('status'),
            'showcase' => $this->showcase(),
        ]));

        Fortify::twoFactorChallengeView(fn () => Inertia::render('auth/TwoFactorChallenge', [
            'showcase' => $this->showcase(),
        ]));

    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

    }
}
