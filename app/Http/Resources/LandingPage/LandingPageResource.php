<?php

namespace App\Http\Resources\LandingPage;

use App\Models\LandingPageHeroMedia;
use App\Models\ProjectLandingPage;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A project's case-study page settings. Expects `heroMedia` loaded.
 *
 * @mixin ProjectLandingPage
 */
class LandingPageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'eyebrow' => $this->eyebrow,
            'headline' => $this->headline,
            'subheadline' => $this->subheadline,
            'hero_video_url' => $this->hero_video_url,
            'primary_cta_label' => $this->primary_cta_label,
            'primary_cta_url' => $this->primary_cta_url,
            'secondary_cta_label' => $this->secondary_cta_label,
            'secondary_cta_url' => $this->secondary_cta_url,
            'accent_from' => $this->accent_from,
            'accent_to' => $this->accent_to,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'og_image_url' => FileService::url($this->og_image_path),
            'is_published' => $this->is_published,
            // Positional slots (0 desktop, 1 tablet, 2 phone); empty slots are null.
            'hero_media' => collect(array_keys(ProjectLandingPage::HERO_SLOTS))
                ->map(fn (int $slot): ?string => FileService::url($this->heroMedia
                    ->first(fn (LandingPageHeroMedia $heroMedia): bool => $heroMedia->slot === $slot)
                    ?->path))
                ->all(),
        ];
    }
}
