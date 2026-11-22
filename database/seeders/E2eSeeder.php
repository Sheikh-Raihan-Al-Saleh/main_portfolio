<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use App\Services\FounderService;
use App\Services\StudioService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Fixture data for the Playwright suite (tests/e2e).
 *
 * The normal portfolio content, plus everything the image-driven parts of the
 * site need to be exercised: a company logo, an avatar, a cover on every
 * project and a published landing page with all three hero slots filled.
 * Images are generated with GD into uploads/e2e/, so no binaries are
 * committed.
 *
 * It refuses to run against any database whose name does not end in "_e2e",
 * so it can never touch real content.
 */
class E2eSeeder extends Seeder
{
    /**
     * One admin per Playwright project (desktop, mobile), so their parallel
     * sign-ins never trip the login rate limit (5 per minute per account).
     */
    public const ADMIN_EMAILS = ['e2e-admin@example.test', 'e2e-admin-mobile@example.test'];

    public const ADMIN_PASSWORD = 'e2e-password-123';

    public function run(): void
    {
        $database = (string) DB::connection()->getDatabaseName();

        if (! str_ends_with($database, '_e2e')) {
            throw new RuntimeException("E2eSeeder only runs against an *_e2e database, not [{$database}].");
        }

        $this->call(PortfolioSeeder::class);

        foreach (self::ADMIN_EMAILS as $email) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'E2E Admin',
                    'password' => self::ADMIN_PASSWORD,
                    'is_admin' => true,
                    'email_verified_at' => now(),
                ],
            );
        }

        $studios = app(StudioService::class);
        $studios->current()->forceFill([
            'logo_path' => $this->image('e2e/logo.png', 480, 120, [17, 17, 17], 'LOGO'),
            'logo_mark_path' => $this->image('e2e/logo-mark.png', 128, 128, [17, 17, 17], 'MARK'),
        ])->save();
        $studios->forgetBrand();

        app(FounderService::class)->current()->forceFill([
            'avatar_path' => $this->image('e2e/avatar.png', 600, 750, [16, 185, 129], 'AVATAR'),
        ])->save();

        /** @var list<array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>}> $palette */
        $palette = [[79, 70, 229], [14, 165, 233], [168, 85, 247], [234, 88, 12], [16, 185, 129], [225, 29, 72]];

        Project::query()->orderBy('id')->get()->each(function (Project $project, int $i) use ($palette): void {
            $project->update([
                'cover_image_path' => $this->image("e2e/cover-{$project->id}.png", 1280, 800, $palette[$i % count($palette)], $project->title),
            ]);
        });

        $landingProject = Project::query()->published()->where('owner_type', 'studio')->orderBy('id')->firstOrFail();

        $landingPage = $landingProject->landingPage()->updateOrCreate([], [
            'headline' => "{$landingProject->title}, end to end",
            'subheadline' => 'A case study rendered from e2e fixtures.',
            'is_published' => true,
        ]);

        foreach ([
            0 => $this->image('e2e/hero-desktop.png', 1920, 1080, [30, 41, 59], 'DESKTOP'),
            1 => $this->image('e2e/hero-tablet.png', 768, 576, [51, 65, 85], 'TABLET'),
            2 => $this->image('e2e/hero-phone.png', 375, 667, [71, 85, 105], 'PHONE'),
        ] as $slot => $path) {
            $landingPage->heroMedia()->updateOrCreate(['slot' => $slot], ['path' => $path]);
        }
    }

    /**
     * Write a solid PNG with a label onto the uploads disk and return its path.
     *
     * @param  int<1, max>  $width
     * @param  int<1, max>  $height
     * @param  array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>}  $rgb
     */
    private function image(string $path, int $width, int $height, array $rgb, string $label): string
    {
        $image = imagecreatetruecolor($width, $height);
        $fill = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
        $ink = imagecolorallocate($image, 255, 255, 255);

        if ($fill === false || $ink === false) {
            throw new RuntimeException('GD could not allocate colours.');
        }

        imagefilledrectangle($image, 0, 0, $width, $height, $fill);
        imagestring($image, 5, 24, 24, substr($label, 0, 40), $ink);

        ob_start();
        imagepng($image);
        $png = (string) ob_get_clean();
        imagedestroy($image);

        Storage::disk('uploads')->put($path, $png);

        return $path;
    }
}
