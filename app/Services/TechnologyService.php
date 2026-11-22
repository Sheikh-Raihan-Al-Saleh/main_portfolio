<?php

namespace App\Services;

use App\Models\Technology;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * The shared technology vocabulary.
 */
class TechnologyService
{
    /**
     * @return Collection<int, Technology>
     */
    public function all(): Collection
    {
        return Technology::query()->withCount('projects')->alphabetical()->get();
    }

    /**
     * Technology IDs for a list of names, creating any that are new.
     * Matching is by slug, so "Vue.js" and "vue js" do not become two rows.
     *
     * @param  array<int, string>  $names
     * @return array<int, int> IDs in the same order as the names.
     */
    public function resolveIds(array $names): array
    {
        $bySlug = [];

        foreach ($names as $name) {
            $name = trim($name);
            $slug = Str::slug($name);

            if ($slug !== '' && ! isset($bySlug[$slug])) {
                $bySlug[$slug] = $name;
            }
        }

        if ($bySlug === []) {
            return [];
        }

        $existing = Technology::query()
            ->whereIn('slug', array_keys($bySlug))
            ->pluck('id', 'slug');

        $ids = [];

        foreach ($bySlug as $slug => $name) {
            $ids[] = $existing[$slug] ?? Technology::query()->create(['name' => $name, 'slug' => $slug])->id;
        }

        return $ids;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Technology
    {
        return Technology::query()->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'icon' => $data['icon'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Technology $technology, array $data): Technology
    {
        $technology->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'icon' => $data['icon'] ?? null,
        ]);

        return $technology;
    }

    public function delete(Technology $technology): void
    {
        $technology->delete();
    }
}
