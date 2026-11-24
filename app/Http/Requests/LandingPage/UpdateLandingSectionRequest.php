<?php

namespace App\Http\Requests\LandingPage;

use App\Enums\LandingSectionType;
use App\Models\LandingSection;

/**
 * Editing a content block. Its type and page are fixed at creation, so only
 * the block's fields are validated, against the block's existing type.
 */
class UpdateLandingSectionRequest extends StoreLandingSectionRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->blockRules();
    }

    /**
     * The type of the block being edited (the type never changes after creation).
     */
    public function sectionType(): ?LandingSectionType
    {
        $section = $this->route('landingSection');

        return $section instanceof LandingSection ? LandingSectionType::tryFrom($section->type) : null;
    }
}
