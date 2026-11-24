<?php

namespace App\Http\Resources\Founder;

use App\Models\Founder;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The founder as the admin "Founder" form edits it: every column plus the
 * public URLs of the uploaded files, so the form can preview them.
 *
 * @mixin Founder
 */
class FounderFormResource extends JsonResource
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
            'avatar_url' => FileService::url($this->avatar_path),
            'logo_url' => FileService::url($this->logo_path),
            'resume_url' => FounderResource::resumeUrl($this->resource),
            'og_image_url' => FileService::url($this->og_image_path),
        ];
    }
}
