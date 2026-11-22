<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Cache;

/**
 * For tables that hold exactly one row (the founder profile, the company).
 *
 * A bare firstOrCreate([]) lets two first-ever requests both see an empty
 * table and both insert, leaving two rows and an arbitrary "current" one. The
 * fast path is a plain read; only the first-ever creation takes a lock, and
 * re-checks inside it.
 */
trait ResolvesSingleton
{
    /**
     * @param  array<string, mixed>  $defaults
     */
    protected static function resolveSingleton(array $defaults): static
    {
        $existing = static::query()->oldest('id')->first();

        if ($existing !== null) {
            return $existing;
        }

        return Cache::lock('singleton:'.static::query()->getModel()->getTable(), 10)
            ->block(5, fn (): static => static::query()->oldest('id')->first()
                ?? static::query()->create($defaults));
    }
}
