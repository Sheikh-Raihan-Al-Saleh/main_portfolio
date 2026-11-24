<?php

namespace App\Support;

/**
 * Merges admin-edited page copy over the default copy.
 */
class ContentMerger
{
    /**
     * Stored copy merged over the defaults, stored values winning. Lists are
     * replaced whole, never merged item by item, so a removed item does not
     * come back.
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
            $bothAreObjects = is_array($value) && ! array_is_list($value)
                && isset($defaults[$key]) && is_array($defaults[$key]);

            $merged[$key] = $bothAreObjects ? self::merge($defaults[$key], $value) : $value;
        }

        return $merged;
    }
}
