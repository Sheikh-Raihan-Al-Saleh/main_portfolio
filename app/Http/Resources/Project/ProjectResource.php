<?php

namespace App\Http\Resources\Project;

use App\Models\Project;
use App\Models\ProjectImage;
use App\Services\FileService;
use Illuminate\Http\Request;

/**
 * A project's detail page: the card fields plus the long-form content and the
 * gallery. Expects `images` and `technologies` loaded.
 *
 * @mixin Project
 */
class ProjectResource extends ProjectCardResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'description' => $this->description,
            'started_at' => $this->started_at?->toDateString(),
            'completed_at' => $this->completed_at?->toDateString(),
            'images' => $this->images->map(fn (ProjectImage $image): array => [
                'id' => $image->id,
                'url' => FileService::url($image->path),
                'alt' => $image->alt ?: $this->title,
                'caption' => $image->caption,
            ])->values()->all(),
        ];
    }
}
