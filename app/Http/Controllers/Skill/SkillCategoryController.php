<?php

namespace App\Http\Controllers\Skill;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderRequest;
use App\Http\Requests\Skill\StoreSkillCategoryRequest;
use App\Http\Requests\Skill\UpdateSkillCategoryRequest;
use App\Models\Founder;
use App\Models\SkillCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Admin: skill categories, managed inline on the skills page.
 */
class SkillCategoryController extends Controller
{
    /**
     * Save a new category at the end of the list.
     */
    public function store(StoreSkillCategoryRequest $request): RedirectResponse
    {
        $name = $request->validated('name');
        $founder = Founder::current();

        $founder->skillCategories()->create([
            'name' => $name,
            'slug' => $this->uniqueSlug($name),
            'sort_order' => (int) $founder->skillCategories()->max('sort_order') + 1,
        ]);

        $this->toast(__('Category added.'));

        return back();
    }

    /**
     * Rename a category; its slug follows the new name.
     */
    public function update(UpdateSkillCategoryRequest $request, SkillCategory $skillCategory): RedirectResponse
    {
        $name = $request->validated('name');

        $skillCategory->update([
            'name' => $name,
            'slug' => $this->uniqueSlug($name, $skillCategory->id),
        ]);

        $this->toast(__('Category renamed.'));

        return back();
    }

    /**
     * Delete a category. Only an empty one can go, so no skill is lost.
     */
    public function destroy(SkillCategory $skillCategory): RedirectResponse
    {
        if ($skillCategory->skills()->exists()) {
            throw ValidationException::withMessages(['category' => __('Move or delete this category\'s skills first.')]);
        }

        $skillCategory->delete();

        $this->toast(__('Category deleted.'), 'error');

        return back();
    }

    /**
     * Save the new display order of the skill categories.
     */
    public function reorder(ReorderRequest $request): RedirectResponse
    {
        $this->saveSortOrder(Founder::current()->skillCategories(), $request->validated('ids'));

        return back();
    }

    /**
     * A URL-safe slug for a category name, with a numeric suffix until it is
     * unique among the founder's categories.
     */
    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $suffix = 2;

        while ($this->slugIsTaken($slug, $ignoreId)) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * Whether another of the founder's categories already uses this slug.
     */
    private function slugIsTaken(string $slug, ?int $ignoreId): bool
    {
        $query = Founder::current()->skillCategories()->where('slug', $slug);

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }
}
