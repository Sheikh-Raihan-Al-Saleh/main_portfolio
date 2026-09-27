<?php

namespace App\Actions;

use App\Enums\LandingSectionType;
use App\Models\LandingSection;
use Illuminate\Support\Str;

/**
 * Shapes a landing section for the client.
 *
 * Both the company home page and a project's landing page are composed of these
 * blocks, so the presentation lives here rather than in either controller.
 */
class PresentsLandingSection
{
    /**
     * Markdown is rendered here rather than in the browser so no raw HTML is
     * shipped to `v-html` unsanitised.
     *
     * @return array<string, mixed>
     */
    public function __invoke(LandingSection $section): array
    {
        $payload = $section->only([
            'id', 'eyebrow', 'heading', 'subheading', 'body', 'data', 'is_visible',
        ]);

        $payload['type'] = $section->type->value;

        if ($section->type === LandingSectionType::RichText) {
            $payload['body_html'] = Str::markdown(
                (string) data_get($section->data, 'markdown', ''),
                [
                    'html_input' => 'strip',
                    'allow_unsafe_links' => false,
                ],
            );
        }

        return $payload;
    }
}
