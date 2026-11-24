<?php

namespace App\Rules;

use Closure;
use DOMDocument;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Accepts an SVG upload only if it is plain vector artwork. Other file types
 * pass through untouched.
 *
 * Uploads are served from this origin, and an SVG opened directly is a
 * document that can run script. A pattern blacklist is easy to slip past
 * (entity-encoded "javascript&colon;", <animate> rewriting an href...), so
 * the file is parsed and only known drawing elements and attributes pass.
 */
class SafeSvg implements ValidationRule
{
    /** SVG drawing, structure and filter elements a logo can reasonably use. */
    private const ALLOWED_ELEMENTS = [
        'svg', 'g', 'defs', 'symbol', 'use', 'title', 'desc', 'metadata',
        'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon',
        'text', 'tspan', 'textpath',
        'lineargradient', 'radialgradient', 'stop', 'pattern', 'clippath', 'mask',
        'filter', 'feblend', 'fecolormatrix', 'fecomposite', 'feflood',
        'fegaussianblur', 'femerge', 'femergenode', 'feoffset', 'fedropshadow',
    ];

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || $value->getMimeType() !== 'image/svg+xml') {
            return;
        }

        if (! self::isSafe((string) file_get_contents($value->getRealPath()))) {
            $fail('The :attribute may only contain plain vector shapes, not scripts, links or embedded content.');
        }
    }

    /**
     * Whether an SVG has no DOCTYPE and contains only allowed elements and
     * attributes: no event handlers, no outside links, no script URLs.
     */
    public static function isSafe(string $svg): bool
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
            if (! in_array(strtolower($element->localName), self::ALLOWED_ELEMENTS, true)) {
                return false;
            }

            foreach ($element->attributes ?? [] as $svgAttribute) {
                if (! self::isSafeAttribute(strtolower($svgAttribute->localName), $svgAttribute->value)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Whether one attribute is harmless: not an event handler, and any link
     * or url() only points inside the file.
     */
    private static function isSafeAttribute(string $name, string $rawValue): bool
    {
        // The DOM has already decoded entities, so encoded payloads show here.
        $value = strtolower(preg_replace('/\s+/', '', $rawValue) ?? '');

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

        return true;
    }
}
