<?php

namespace App\Http\Resources\Studio;

use App\Models\SocialLink;
use App\Models\Studio;
use App\Services\Media\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The studio as the public site sees it. Expects `socialLinks` loaded.
 *
 * @mixin Studio
 */
class StudioResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'short_name' => $this->short_name ?: $this->name,
            'legal_name' => $this->legal_name,
            'headline' => $this->headline,
            'tagline' => $this->tagline,
            'bio' => $this->bio,
            'mission' => $this->mission,
            'founded_year' => $this->founded_year,
            'years_in_business' => $this->founded_year && $this->founded_year <= now()->year
                ? now()->year - $this->founded_year
                : null,
            'is_accepting_projects' => $this->is_accepting_projects,
            'location' => $this->location,
            'public_email' => $this->public_email,
            'phone' => $this->phone,
            'website_url' => $this->website_url,
            'logo_url' => MediaService::url($this->logo_path),
            'logo_dark_url' => MediaService::url($this->logo_dark_path),
            'logo_mark_url' => MediaService::url($this->logo_mark_path),
            'og_image_url' => MediaService::url($this->og_image_path),
            'hero' => [
                'eyebrow' => $this->hero_eyebrow,
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
}
