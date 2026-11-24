<?php

namespace App\Services;

use App\Models\Founder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Saves the founder profile form: details, media files and social links.
 */
class FounderService
{
    /** Form fields saved to the table. */
    private const FIELDS = ['name', 'headline', 'tagline', 'bio', 'founder_message', 'roles', 'is_available_for_work', 'location', 'public_email', 'phone', 'hero_title', 'hero_statement', 'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url', 'footer', 'content', 'meta_title', 'meta_description'];

    /** Uploaded files and their folders, each saved to its "<name>_path" column. */
    private const FILES = ['avatar' => 'founder', 'logo' => 'founder', 'og_image' => 'founder', 'resume' => 'founder/resume'];

    public function __construct(private FileService $files) {}

    /**
     * Save the founder details, media files and social links.
     *
     * @param  array<string, mixed>  $data  Validated form data.
     */
    public function update(Founder $founder, array $data): void
    {
        DB::transaction(function () use ($founder, $data): void {
            $founder->fill(Arr::only($data, self::FIELDS));
            $founder->slug = Str::slug($founder->name) ?: 'founder';

            foreach (self::FILES as $file => $folder) {
                $column = $file.'_path';
                $founder->{$column} = $this->files->replace(
                    $data[$file] ?? null,
                    $folder,
                    $founder->{$column},
                    (bool) ($data['remove_'.$file] ?? false),
                );
            }

            $founder->save();

            if (isset($data['socials'])) {
                $founder->syncSocialLinks($data['socials']);
            }
        });
    }
}
