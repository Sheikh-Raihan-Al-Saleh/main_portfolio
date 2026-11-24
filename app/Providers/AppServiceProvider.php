<?php

namespace App\Providers;

use App\Models\Founder;
use App\Models\ProjectLandingPage;
use App\Models\Studio;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

/**
 * Application services: the public path on shared hosting and model defaults.
 *
 * Upload limits are PHP settings, so they are set where PHP is configured:
 * the local php.ini, and public/.user.ini / public/.htaccess in production.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->usePublicPathOfFrontController();
    }

    /**
     * Point public_path() at the directory that actually serves the site.
     *
     * On shared hosting (cPanel) the contents of public/ are usually copied
     * into public_html, with index.php edited to require the app from
     * elsewhere. Laravel would still treat <project>/public as the public
     * directory, so uploads landed in <project>/public/uploads -- a folder the
     * web server never serves -- and every uploaded image returned 404.
     *
     * The front controller is the first index.php PHP loaded for this
     * request, so its directory is the real web root. Console commands have
     * no front controller and keep the default. The uploads disk root is set
     * here at runtime rather than read from config, so a cached config (built
     * by artisan, which only knows <project>/public) cannot point it back.
     */
    protected function usePublicPathOfFrontController(): void
    {
        if (! $this->app->runningInConsole()) {
            foreach (get_included_files() as $file) {
                if (basename($file) !== 'index.php') {
                    continue;
                }

                $webRoot = dirname($file);

                if (realpath($webRoot) !== realpath($this->app->publicPath())) {
                    $this->app->usePublicPath($webRoot);
                }

                break;
            }
        }

        config(['filesystems.disks.uploads.root' => $this->app->publicPath('uploads')]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureModels();
    }

    /**
     * Eloquent rules shared by every model.
     */
    protected function configureModels(): void
    {
        // Polymorphic columns store these short, stable aliases instead of
        // class names, so renaming a model never breaks stored rows.
        Relation::enforceMorphMap([
            'studio' => Studio::class,
            'founder' => Founder::class,
            'landing_page' => ProjectLandingPage::class,
        ]);

        // A relation used without with() throws outside production, so an
        // N+1 query is caught in development and tests instead of shipping.
        Model::preventLazyLoading(! app()->isProduction());
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
