<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SkillRequest;
use App\Models\Skill;
use App\Services\SkillService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SkillController extends Controller
{
    public function __construct(
        private readonly SkillService $skills,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/skills/Index', [
            'categories' => $this->skills->categoriesWithSkills(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/skills/Create', [
            'categoryOptions' => $this->skills->categoryOptions(),
        ]);
    }

    public function store(SkillRequest $request): RedirectResponse
    {
        $this->skills->create($request->validated());

        $this->toast(__('Skill created.'));

        return to_route('admin.skills.index');
    }

    public function edit(Skill $skill): Response
    {
        return Inertia::render('admin/skills/Edit', [
            'skill' => $skill,
            'categoryOptions' => $this->skills->categoryOptions(),
        ]);
    }

    public function update(SkillRequest $request, Skill $skill): RedirectResponse
    {
        $this->skills->update($skill, $request->validated());

        $this->toast(__('Skill updated.'));

        return to_route('admin.skills.index');
    }

    public function destroy(Skill $skill): RedirectResponse
    {
        $this->skills->delete($skill);

        $this->toast(__('Skill deleted.'), 'error');

        return back();
    }
}
