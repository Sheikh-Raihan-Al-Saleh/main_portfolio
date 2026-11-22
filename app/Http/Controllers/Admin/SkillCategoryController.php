<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SkillCategoryRequest;
use App\Models\SkillCategory;
use App\Services\SkillService;
use Illuminate\Http\RedirectResponse;

/**
 * Skill categories, managed inline on the skills page.
 */
class SkillCategoryController extends Controller
{
    public function __construct(
        private readonly SkillService $skills,
    ) {}

    public function store(SkillCategoryRequest $request): RedirectResponse
    {
        $this->skills->createCategory($request->validated('name'));

        $this->toast(__('Category added.'));

        return back();
    }

    public function update(SkillCategoryRequest $request, SkillCategory $skillCategory): RedirectResponse
    {
        $this->skills->renameCategory($skillCategory, $request->validated('name'));

        $this->toast(__('Category renamed.'));

        return back();
    }

    public function destroy(SkillCategory $skillCategory): RedirectResponse
    {
        $this->skills->deleteCategory($skillCategory);

        $this->toast(__('Category deleted.'), 'error');

        return back();
    }
}
