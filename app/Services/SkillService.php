<?php

namespace App\Services;

use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * The founder's skills and skill categories.
 */
class SkillService
{
    public function __construct(
        private readonly FounderService $founders,
    ) {}

    /**
     * Categories (in order) with their skills (in order), for the admin list.
     *
     * @return Collection<int, SkillCategory>
     */
    public function categoriesWithSkills(): Collection
    {
        return $this->founders->current()->skillCategories()
            ->ordered()
            ->with(['skills' => fn ($query) => $query->ordered()])
            ->get();
    }

    /**
     * Category options for the skill form select.
     *
     * @return array<int, array{value: int, label: string}>
     */
    public function categoryOptions(): array
    {
        return $this->founders->current()->skillCategories()
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (SkillCategory $category): array => ['value' => $category->id, 'label' => $category->name])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data  Validated form data.
     */
    public function create(array $data): Skill
    {
        $category = $this->category((int) $data['skill_category_id']);

        return $category->skills()->create([
            ...$this->attributes($data),
            'sort_order' => (int) $category->skills()->max('sort_order') + 1,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data  Validated form data.
     */
    public function update(Skill $skill, array $data): Skill
    {
        $this->category((int) $data['skill_category_id']);

        $skill->update([
            ...$this->attributes($data),
            'skill_category_id' => (int) $data['skill_category_id'],
        ]);

        return $skill;
    }

    public function delete(Skill $skill): void
    {
        $skill->delete();
    }

    public function createCategory(string $name): SkillCategory
    {
        $founder = $this->founders->current();

        return $founder->skillCategories()->create([
            'name' => $name,
            'slug' => $this->uniqueCategorySlug($name),
            'sort_order' => (int) $founder->skillCategories()->max('sort_order') + 1,
        ]);
    }

    public function renameCategory(SkillCategory $category, string $name): SkillCategory
    {
        $category->update([
            'name' => $name,
            'slug' => $this->uniqueCategorySlug($name, $category->id),
        ]);

        return $category;
    }

    /**
     * Only an empty category can be deleted, so skills are never lost by accident.
     */
    public function deleteCategory(SkillCategory $category): void
    {
        if ($category->skills()->exists()) {
            throw ValidationException::withMessages([
                'category' => __('Move or delete this category\'s skills first.'),
            ]);
        }

        $category->delete();
    }

    /**
     * A category of the current founder, or a validation error.
     */
    private function category(int $id): SkillCategory
    {
        $category = $this->founders->current()->skillCategories()->find($id);

        if ($category === null) {
            throw ValidationException::withMessages(['skill_category_id' => __('Choose a valid category.')]);
        }

        return $category;
    }

    private function uniqueCategorySlug(string $name, ?int $ignoreId = null): string
    {
        $founder = $this->founders->current();
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $suffix = 2;

        while ($founder->skillCategories()->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        return collect($data)->only(['name', 'proficiency', 'icon', 'is_featured'])->all();
    }
}
