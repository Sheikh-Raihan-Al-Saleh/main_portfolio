<?php

namespace App\Support\Defaults;

/**
 * Merges stored, admin-edited copy over code defaults.
 */
class ContentMerger
{
    /**
     * Union-merge at every level, stored values winning.
     *
     * Scalars and lists from storage replace the default wholesale. A list is
     * never merged element by element, or removed items would come back.
     *
     * @param  array<string, mixed>  $defaults
     * @param  array<string, mixed>|null  $stored
     * @return array<string, mixed>
     */
    public static function merge(array $defaults, ?array $stored): array
    {
        if ($stored === null || $stored === []) {
            return $defaults;
        }

        $merged = $defaults;

        foreach ($stored as $key => $value) {
            if (is_array($value) && isset($defaults[$key]) && is_array($defaults[$key]) && ! array_is_list($value)) {
                $merged[$key] = self::merge($defaults[$key], $value);

                continue;
            }

            $merged[$key] = $value;
        }

        return $merged;
    }
}
