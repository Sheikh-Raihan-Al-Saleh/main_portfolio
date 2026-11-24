<?php

namespace App\Http\Resources\Studio;

use App\Models\Studio;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The studio as the admin "Studio & brand" form edits it: every column plus
 * the public URLs of the logos and the social image, so the form can preview
 * them.
 *
 * @mixin Studio
 */
class StudioFormResource extends JsonResource
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
            'logo_url' => FileService::url($this->logo_path),
            'logo_dark_url' => FileService::url($this->logo_dark_path),
            'logo_mark_url' => FileService::url($this->logo_mark_path),
            'og_image_url' => FileService::url($this->og_image_path),
        ];
    }
}
