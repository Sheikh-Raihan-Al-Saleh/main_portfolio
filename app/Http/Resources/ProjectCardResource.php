<?php

namespace App\Http\Resources;

use App\Models\Project;
use App\Models\Technology;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A project in a list (cards, grids, marquees). Expects `technologies` loaded
 * and the `has_landing_page` exists-flag selected (see ProjectService).
 *
 * @mixin Project
 */
class ProjectCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'owner' => $this->owner_type,
            'role' => $this->role,
            'cover_image_url' => $this->cover_image_url,
            'is_featured' => $this->is_featured,
            'year' => ($this->completed_at ?? $this->started_at)?->year,
            'technologies' => $this->technologies->map(fn (Technology $technology): array => [
                'name' => $technology->name,
                'slug' => $technology->slug,
            ])->values()->all(),
            'url' => route('projects.show', $this->slug, absolute: false),
            'landing_url' => $this->has_landing_page
                ? route('landing.show', $this->slug, absolute: false)
                : null,
            'live_url' => $this->live_url,
            'repo_url' => $this->repo_url,
        ];
    }
}
