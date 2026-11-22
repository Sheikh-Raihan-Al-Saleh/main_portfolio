<?php

namespace App\Services\Skill;

use App\Models\Skill;
use App\Models\SkillCategory;
use App\Services\Founder\FounderService;
use App\Support\SortOrder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Skill module: the founder's skills and the categories they are grouped in.
 */
class SkillService
{
    /** Form fields saved to the table. */
    private const FIELDS = ['name', 'proficiency', 'icon', 'is_featured'];

    public function __construct(
        private readonly FounderService $founderService,
    ) {}

    // ── Skills ────────────────────────────────────────────────────

    /**
     * Categories in order, each with its skills in order.
     *
     * @return Collection<int, SkillCategory>
     */
    public function categoriesWithSkills(): Collection
    {
        return $this->founderService->current()->skillCategories()
            ->ordered()
            ->with(['skills' => fn ($query) => $query->ordered()])
            ->get();
    }

    /**
     * Category choices for the skill form: [{ value: id, label: name }].
     *
     * @return array<int, array{value: int, label: string}>
     */
    public function categoryOptions(): array
    {
        return $this->founderService->current()->skillCategories()
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (SkillCategory $category): array => ['value' => $category->id, 'label' => $category->name])
            ->all();
    }

    /**
     * Add a skill at the end of its category.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Skill
    {
        $category = $this->findCategory((int) $data['skill_category_id']);

        return $category->skills()->create([
            ...Arr::only($data, self::FIELDS),
            'sort_order' => (int) $category->skills()->max('sort_order') + 1,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Skill $skill, array $data): Skill
    {
        $this->findCategory((int) $data['skill_category_id']);

        $skill->update([...Arr::only($data, self::FIELDS), 'skill_category_id' => (int) $data['skill_category_id']]);

        return $skill;
    }

    public function delete(Skill $skill): void
    {
        $skill->delete();
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function reorder(array $ids): void
    {
        $categoryIds = $this->founderService->current()->skillCategories()->select('id');

        SortOrder::apply(Skill::query()->whereIn('skill_category_id', $categoryIds), $ids);
    }

    // ── Categories ────────────────────────────────────────────────

    /**
     * Add a category at the end of the list.
     */
    public function createCategory(string $name): SkillCategory
    {
        $founder = $this->founderService->current();

        return $founder->skillCategories()->create([
            'name' => $name,
            'slug' => $this->uniqueCategorySlug($name),
            'sort_order' => (int) $founder->skillCategories()->max('sort_order') + 1,
        ]);
    }

    public function updateCategory(SkillCategory $category, string $name): SkillCategory
    {
        $category->update([
            'name' => $name,
            'slug' => $this->uniqueCategorySlug($name, $category->id),
        ]);

        return $category;
    }

    /**
     * Delete a category. Only an empty one can go, so no skill is lost.
     */
    public function deleteCategory(SkillCategory $category): void
    {
        if ($category->skills()->exists()) {
            throw ValidationException::withMessages(['category' => __('Move or delete this category\'s skills first.')]);
        }

        $category->delete();
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function reorderCategories(array $ids): void
    {
        SortOrder::apply($this->founderService->current()->skillCategories(), $ids);
    }

    // ── Helpers ───────────────────────────────────────────────────

    /**
     * One of the founder's categories, or a validation error.
     */
    private function findCategory(int $id): SkillCategory
    {
        return $this->founderService->current()->skillCategories()->find($id)
            ?? throw ValidationException::withMessages(['skill_category_id' => __('Choose a valid category.')]);
    }

    private function uniqueCategorySlug(string $name, ?int $ignoreId = null): string
    {
        $categories = $this->founderService->current()->skillCategories();
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $suffix = 2;

        while ((clone $categories)->where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
