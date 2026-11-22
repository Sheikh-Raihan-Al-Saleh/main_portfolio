<?php

namespace App\Rules;

use Closure;
use DOMDocument;
use DOMElement;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Accepts an SVG only if it is plain vector artwork.
 *
 * Uploaded files are served from this origin, and an SVG opened directly is a
 * document that can run script. A pattern blacklist is easy to slip past
 * (entity-encoded "javascript&colon;", <animate> rewriting an href, and so
 * on), so this parses the file and allows only known drawing elements and
 * attributes. Anything else fails validation instead of being stored.
 */
class SafeSvg implements ValidationRule
{
    /** Drawing, structure and filter elements a logo can reasonably use. */
    private const ALLOWED_ELEMENTS = [
        'svg', 'g', 'defs', 'symbol', 'use', 'title', 'desc', 'metadata',
        'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon',
        'text', 'tspan', 'textpath',
        'lineargradient', 'radialgradient', 'stop', 'pattern', 'clippath', 'mask',
        'filter', 'feblend', 'fecolormatrix', 'fecomposite', 'feflood',
        'fegaussianblur', 'femerge', 'femergenode', 'feoffset', 'fedropshadow',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || $value->getMimeType() !== 'image/svg+xml') {
            return;
        }

        if (! $this->isSafe((string) file_get_contents($value->getRealPath()))) {
            $fail('The :attribute may only contain plain vector shapes, not scripts, links or embedded content.');
        }
    }

    public function isSafe(string $svg): bool
    {
        // A DOCTYPE is how entity-expansion and external-entity attacks get in;
        // real logo files do not need one.
        if ($svg === '' || preg_match('/<!DOCTYPE|<!ENTITY/i', $svg) === 1) {
            return false;
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadXML($svg, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded || $document->documentElement?->localName !== 'svg') {
            return false;
        }

        foreach ($document->getElementsByTagName('*') as $element) {
            if (! $this->isSafeElement($element)) {
                return false;
            }
        }

        return true;
    }

    private function isSafeElement(DOMElement $element): bool
    {
        if (! in_array(strtolower($element->localName), self::ALLOWED_ELEMENTS, true)) {
            return false;
        }

        foreach ($element->attributes ?? [] as $attr) {
            $name = strtolower($attr->localName);
            // The DOM has already decoded entities, so encoded payloads show here.
            $value = strtolower(preg_replace('/\s+/', '', $attr->value) ?? '');

            if (str_starts_with($name, 'on')) {
                return false;
            }

            // Links may only point inside the file (gradients, <use> symbols).
            if ($name === 'href' && ! str_starts_with($value, '#')) {
                return false;
            }

            if (str_contains($value, 'javascript:') || str_contains($value, 'data:')) {
                return false;
            }

            // url() may only reference an in-file id, e.g. fill="url(#grad)".
            if (preg_match_all('/url\(([^)]*)\)/', $value, $matches) > 0) {
                foreach ($matches[1] as $target) {
                    if (! str_starts_with(trim($target, '\'"'), '#')) {
                        return false;
                    }
                }
            }

            if ($name === 'style' && (str_contains($value, '@import') || str_contains($value, 'expression('))) {
                return false;
            }
        }

        return true;
    }
}
