<?php

namespace App\Http\Resources;

use App\Enums\LandingSectionType;
use App\Models\LandingSection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * A content block for the public renderer.
 *
 * Media inside `data` stays as stored paths; the frontend turns each into a
 * URL with mediaUrl(). Markdown is rendered here, with raw HTML stripped, so
 * nothing unsanitised reaches v-html.
 *
 * @mixin LandingSection
 */
class LandingSectionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'eyebrow' => $this->eyebrow,
            'heading' => $this->heading,
            'subheading' => $this->subheading,
            'body' => $this->body,
            'data' => $this->data ?? [],
            'is_visible' => $this->is_visible,
            'body_html' => $this->when(
                $this->type === LandingSectionType::RichText,
                fn (): string => Str::markdown((string) data_get($this->data, 'markdown', ''), [
                    'html_input' => 'strip',
                    'allow_unsafe_links' => false,
                ]),
            ),
        ];
    }
}
