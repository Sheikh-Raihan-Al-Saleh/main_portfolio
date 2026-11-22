<?php

namespace App\Services\Technology;

use App\Models\Technology;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Technology module: the shared tech-stack vocabulary.
 */
class TechnologyService
{
    /** Form fields saved to the table. */
    private const FIELDS = ['name', 'icon'];

    /**
     * All technologies, alphabetically, with how many projects use each.
     *
     * @return Collection<int, Technology>
     */
    public function list(): Collection
    {
        return Technology::query()->withCount('projects')->alphabetical()->get();
    }

    /**
     * Just the names, for the project form's suggestions.
     *
     * @return array<int, string>
     */
    public function names(): array
    {
        return Technology::query()->alphabetical()->pluck('name')->all();
    }

    /**
     * IDs for a list of names, in the same order, creating names that are new.
     * Names match by slug, so "Vue.js" and "vue.js" are the same technology.
     *
     * @param  array<int, string>  $names
     * @return array<int, int>
     */
    public function idsFor(array $names): array
    {
        $ids = [];

        foreach ($names as $name) {
            $name = trim($name);

            if ($name !== '' && Str::slug($name) !== '') {
                $ids[] = Technology::query()->firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Technology
    {
        return Technology::query()->create([...Arr::only($data, self::FIELDS), 'slug' => Str::slug($data['name'])]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Technology $technology, array $data): Technology
    {
        $technology->update([...Arr::only($data, self::FIELDS), 'slug' => Str::slug($data['name'])]);

        return $technology;
    }

    /**
     * Delete a technology; it is detached from every project (pivot cascades).
     */
    public function delete(Technology $technology): void
    {
        $technology->delete();
    }
}
