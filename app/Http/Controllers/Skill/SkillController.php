<?php

namespace App\Http\Controllers\Skill;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderRequest;
use App\Http\Requests\Skill\StoreSkillRequest;
use App\Http\Requests\Skill\UpdateSkillRequest;
use App\Models\Skill;
use App\Services\Skill\SkillService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the founder's skills.
 */
class SkillController extends Controller
{
    public function __construct(
        private readonly SkillService $skillService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/skills/Index', [
            'categories' => $this->skillService->categoriesWithSkills(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/skills/Create', [
            'categoryOptions' => $this->skillService->categoryOptions(),
        ]);
    }

    public function store(StoreSkillRequest $request): RedirectResponse
    {
        $this->skillService->create($request->validated());
        $this->toast(__('Skill created.'));

        return to_route('admin.skills.index');
    }

    public function edit(Skill $skill): Response
    {
        return Inertia::render('admin/skills/Edit', [
            'skill' => $skill,
            'categoryOptions' => $this->skillService->categoryOptions(),
        ]);
    }

    public function update(UpdateSkillRequest $request, Skill $skill): RedirectResponse
    {
        $this->skillService->update($skill, $request->validated());
        $this->toast(__('Skill updated.'));

        return to_route('admin.skills.index');
    }

    public function destroy(Skill $skill): RedirectResponse
    {
        $this->skillService->delete($skill);
        $this->toast(__('Skill deleted.'), 'error');

        return back();
    }

    public function reorder(ReorderRequest $request): RedirectResponse
    {
        $this->skillService->reorder($request->validated('ids'));

        return back();
    }
}
