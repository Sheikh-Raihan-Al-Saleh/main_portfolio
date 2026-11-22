<?php

namespace App\Http\Resources\Founder;

use App\Models\Founder;
use App\Models\SocialLink;
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
            'avatar_url' => $this->avatar_url,
            'logo_url' => $this->logo_url,
            'resume_url' => $this->resume_url,
            'og_image_url' => $this->og_image_url,
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
}
