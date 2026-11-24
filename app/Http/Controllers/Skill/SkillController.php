<?php

namespace App\Http\Controllers\Skill;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderRequest;
use App\Http\Requests\Skill\StoreSkillRequest;
use App\Http\Requests\Skill\UpdateSkillRequest;
use App\Models\Founder;
use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the founder's skills.
 */
class SkillController extends Controller
{
    /** Form fields saved to the table (the category is set separately). */
    private const FIELDS = ['name', 'proficiency', 'icon', 'is_featured'];

    /**
     * Show the categories in order, each with its skills in order.
     */
    public function index(): Response
    {
        $categories = Founder::current()->skillCategories()->with('skills')->ordered()->get();

        return Inertia::render('skill/SkillIndex', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form to create a skill.
     */
    public function create(): Response
    {
        return Inertia::render('skill/CreateSkill', [
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    /**
     * Save a new skill at the end of its category.
     */
    public function store(StoreSkillRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $category = $this->findCategory((int) $data['skill_category_id']);

        $category->skills()->create([
            ...Arr::only($data, self::FIELDS),
            'sort_order' => (int) $category->skills()->max('sort_order') + 1,
        ]);

        $this->toast(__('Skill created.'));

        return to_route('skillIndex');
    }

    /**
     * Show the form to edit a skill.
     */
    public function edit(Skill $skill): Response
    {
        return Inertia::render('skill/EditSkill', [
            'skill' => $skill,
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    /**
     * Save the changes to a skill, which may move to another category.
     */
    public function update(UpdateSkillRequest $request, Skill $skill): RedirectResponse
    {
        $data = $request->validated();
        $category = $this->findCategory((int) $data['skill_category_id']);

        $skill->update([...Arr::only($data, self::FIELDS), 'skill_category_id' => $category->id]);

        $this->toast(__('Skill updated.'));

        return to_route('skillIndex');
    }

    /**
     * Delete a skill.
     */
    public function destroy(Skill $skill): RedirectResponse
    {
        $skill->delete();

        $this->toast(__('Skill deleted.'), 'error');

        return back();
    }

    /**
     * Save the new display order of the skills. Only the founder's skills move.
     */
    public function reorder(ReorderRequest $request): RedirectResponse
    {
        $categoryIds = Founder::current()->skillCategories()->select('id');

        $this->saveSortOrder(Skill::whereIn('skill_category_id', $categoryIds), $request->validated('ids'));

        return back();
    }

    /**
     * Category choices for the skill form: [{ value: id, label: name }].
     *
     * @return array<int, array{value: int, label: string}>
     */
    private function categoryOptions(): array
    {
        return Founder::current()->skillCategories()
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (SkillCategory $category): array => ['value' => $category->id, 'label' => $category->name])
            ->all();
    }

    /**
     * One of the founder's categories, or a validation error.
     */
    private function findCategory(int $id): SkillCategory
    {
        return Founder::current()->skillCategories()->find($id)
            ?? throw ValidationException::withMessages(['skill_category_id' => __('Choose a valid category.')]);
    }
}
