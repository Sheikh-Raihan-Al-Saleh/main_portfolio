<?php

namespace App\Http\Resources\Experience;

use App\Models\Experience;
use App\Services\Media\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A role in the founder's work history: every column (and the highlights,
 * when loaded) plus the logo URL and whether the role is the current one.
 *
 * @mixin Experience
 */
class ExperienceResource extends JsonResource
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
            'logo_url' => MediaService::url($this->logo_path),
            // A role without an end date is the one the founder holds now.
            'is_current' => $this->end_date === null,
        ];
    }
}
