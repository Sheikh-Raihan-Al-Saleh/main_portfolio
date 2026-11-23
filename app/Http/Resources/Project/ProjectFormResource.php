<?php

namespace App\Http\Resources\Project;

use App\Models\Project;
use App\Models\ProjectImage;
use App\Services\Media\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A project as the admin list and the edit form see it: every column and
 * loaded relation, plus the public URLs of the cover and gallery images.
 *
 * @mixin Project
 */
class ProjectFormResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Every column, then every loaded relation (what Model::toArray() returns).
            ...$this->attributesToArray(),
            ...$this->relationsToArray(),
            'cover_image_url' => MediaService::url($this->cover_image_path),
            // The gallery is only loaded for the edit form, not for the list.
            'images' => $this->when(
                $this->resource->relationLoaded('images'),
                fn (): array => $this->images->map(fn (ProjectImage $image): array => [
                    ...$image->toArray(),
                    'url' => MediaService::url($image->path),
                ])->all(),
            ),
        ];
    }
}
