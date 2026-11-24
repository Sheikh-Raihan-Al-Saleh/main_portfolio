<?php

namespace App\Services;

use App\Models\Studio;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Saves the studio form: details, brand files and social links.
 */
class StudioService
{
    /** Form fields saved to the table. */
    private const FIELDS = ['name', 'short_name', 'legal_name', 'headline', 'tagline', 'bio', 'mission', 'founded_year', 'is_accepting_projects', 'location', 'public_email', 'phone', 'website_url', 'hero_eyebrow', 'hero_title', 'hero_statement', 'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url', 'footer', 'meta_title', 'meta_description'];

    /** Uploaded brand files, each saved to its "<name>_path" column. */
    private const FILES = ['logo', 'logo_dark', 'logo_mark', 'og_image'];

    public function __construct(private FileService $files) {}

    /**
     * Save the studio details, brand files and social links. Saving the
     * studio also clears the cached brand (see Studio::booted()).
     *
     * @param  array<string, mixed>  $data  Validated form data.
     */
    public function update(Studio $studio, array $data): void
    {
        DB::transaction(function () use ($studio, $data): void {
            $studio->fill(Arr::only($data, self::FIELDS));
            $studio->slug = Str::slug($studio->name) ?: 'studio';

            foreach (self::FILES as $file) {
                $column = $file.'_path';
                $studio->{$column} = $this->files->replace(
                    $data[$file] ?? null,
                    'brand',
                    $studio->{$column},
                    (bool) ($data['remove_'.$file] ?? false),
                );
            }

            $studio->save();

            if (isset($data['socials'])) {
                $studio->syncSocialLinks($data['socials']);
            }
        });
    }
}
