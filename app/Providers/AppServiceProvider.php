<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->usePublicPathOfFrontController();
        $this->raiseLocalServerUploadLimits();
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
     * Give `php artisan serve` upload limits large enough for the admin forms.
     *
     * The dev server runs in a child PHP process, so its php.ini limits cannot
     * be changed from PHP code. Herd's default upload_max_filesize is 2M, and
     * PHP silently drops any larger file, so most screenshots never reached
     * the app. Adding php-ini/ to PHP_INI_SCAN_DIR makes the child process
     * load php-ini/uploads.ini, however the server was started (the launcher
     * script, `composer run dev` or by hand). The leading path separator keeps
     * PHP's own scan directory, so no extension .ini files are lost.
     *
     * Symfony Process only passes on variables that also exist in $_SERVER,
     * so the value is written there as well as to the process environment.
     */
    protected function raiseLocalServerUploadLimits(): void
    {
        if (! $this->app->runningInConsole() || ($_SERVER['argv'][1] ?? null) !== 'serve') {
            return;
        }

        $current = getenv('PHP_INI_SCAN_DIR');
        $ours = base_path('php-ini');

        if ($current !== false && str_contains($current, $ours)) {
            return;
        }

        $value = ($current === false ? '' : $current).PATH_SEPARATOR.$ours;

        putenv('PHP_INI_SCAN_DIR='.$value);
        $_SERVER['PHP_INI_SCAN_DIR'] = $value;
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
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
