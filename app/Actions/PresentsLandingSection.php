<?php

namespace App\Actions;

use App\Concerns\ResolvesMediaUrls;
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
    use ResolvesMediaUrls;

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
        $payload['data'] = $this->withoutMissingMedia($section->type, $section->data ?? []);

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

    /**
     * Blank out media paths whose file is gone, so a deleted upload falls back
     * to the section's placeholder instead of rendering a broken image.
     *
     * Handles the three shapes LandingSectionType::mediaPaths() declares:
     * `key` (a single path), `list.*` (a list of paths) and `list.*.key`
     * (a list of rows, each holding a path).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withoutMissingMedia(LandingSectionType $type, array $data): array
    {
        foreach ($type->mediaPaths() as $pattern) {
            [$list, $key] = array_pad(explode('.*', $pattern, 2), 2, null);

            if ($key === null) {
                if (isset($data[$list]) && ! $this->mediaExists($data[$list])) {
                    $data[$list] = null;
                }

                continue;
            }

            if (! is_array($data[$list] ?? null)) {
                continue;
            }

            if ($key === '') {
                $data[$list] = array_values(array_filter(
                    $data[$list],
                    fn (mixed $path): bool => is_string($path) && $this->mediaExists($path),
                ));

                continue;
            }

            $key = ltrim($key, '.');

            foreach ($data[$list] as $index => $row) {
                if (is_array($row) && isset($row[$key]) && ! $this->mediaExists($row[$key])) {
                    $data[$list][$index][$key] = null;
                }
            }
        }

        return $data;
    }
}
