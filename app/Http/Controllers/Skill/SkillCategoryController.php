<?php

namespace App\Http\Controllers\Skill;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderRequest;
use App\Http\Requests\Skill\StoreSkillCategoryRequest;
use App\Http\Requests\Skill\UpdateSkillCategoryRequest;
use App\Models\SkillCategory;
use App\Services\Skill\SkillService;
use Illuminate\Http\RedirectResponse;

/**
 * Admin: skill categories, managed inline on the skills page.
 */
class SkillCategoryController extends Controller
{
    public function __construct(
        private readonly SkillService $skillService,
    ) {}

    public function store(StoreSkillCategoryRequest $request): RedirectResponse
    {
        $this->skillService->createCategory($request->validated('name'));
        $this->toast(__('Category added.'));

        return back();
    }

    public function update(UpdateSkillCategoryRequest $request, SkillCategory $skillCategory): RedirectResponse
    {
        $this->skillService->updateCategory($skillCategory, $request->validated('name'));
        $this->toast(__('Category renamed.'));

        return back();
    }

    public function destroy(SkillCategory $skillCategory): RedirectResponse
    {
        $this->skillService->deleteCategory($skillCategory);
        $this->toast(__('Category deleted.'), 'error');

        return back();
    }

    public function reorder(ReorderRequest $request): RedirectResponse
    {
        $this->skillService->reorderCategories($request->validated('ids'));

        return back();
    }
}
