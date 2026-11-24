<?php

namespace App\Http\Resources\Founder;

use App\Models\Founder;
use App\Models\SocialLink;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The founder as the public site sees it. Expects `socialLinks` loaded.
 *
 * @mixin Founder
 */
class FounderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'headline' => $this->headline,
            'tagline' => $this->tagline,
            'bio' => $this->bio,
            'founder_message' => $this->founder_message,
            'roles' => $this->roles ?? [],
            'is_available_for_work' => $this->is_available_for_work,
            'location' => $this->location,
            'public_email' => $this->public_email,
            'phone' => $this->phone,
            'avatar_url' => FileService::url($this->avatar_path),
            'logo_url' => FileService::url($this->logo_path),
            'resume_url' => self::resumeUrl($this->resource),
            'og_image_url' => FileService::url($this->og_image_path),
            'hero' => [
                'title' => $this->hero_title,
                'statement' => $this->hero_statement,
                'primary_cta' => ['label' => $this->primary_cta_label, 'url' => $this->primary_cta_url],
                'secondary_cta' => ['label' => $this->secondary_cta_label, 'url' => $this->secondary_cta_url],
            ],
            'socials' => $this->socialLinks->map(fn (SocialLink $link): array => [
                'platform' => $link->platform,
                'label' => $link->label,
                'url' => $link->url,
            ])->values()->all(),
            'footer' => $this->footer ?? [],
            'meta' => [
                'title' => $this->meta_title,
                'description' => $this->meta_description,
            ],
        ];
    }

    /**
     * The resume download link, or null when no resume is uploaded. The file
     * is served by a route (not the uploads URL) so it gets a readable name.
     */
    public static function resumeUrl(Founder $founder): ?string
    {
        return $founder->resume_path ? route('resume', absolute: false) : null;
    }
}
