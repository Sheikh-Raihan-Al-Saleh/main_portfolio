<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SkillRequest;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SkillController extends Controller
{
    public function index(): Response
    {
        return inertia('admin/skills/Index', [
            'skills' => Skill::query()->ordered()->get(),
            'categories' => Skill::categories(),
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/skills/Create', ['categories' => Skill::categories()]);
    }

    public function store(SkillRequest $request): RedirectResponse
    {
        Skill::create([
            ...$request->validated(),
            'sort_order' => $request->integer('sort_order') ?: (int) Skill::query()->max('sort_order') + 1,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Skill created.')]);

        return to_route('admin.skills.index');
    }

    public function edit(Skill $skill): Response
    {
        return inertia('admin/skills/Edit', [
            'skill' => $skill,
            'categories' => Skill::categories(),
        ]);
    }

    public function update(SkillRequest $request, Skill $skill): RedirectResponse
    {
        $skill->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Skill updated.')]);

        return to_route('admin.skills.index');
    }

    public function destroy(Skill $skill): RedirectResponse
    {
        $skill->delete();

        Inertia::flash('toast', ['type' => 'error', 'message' => __('Skill deleted.')]);

        return back();
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:skills,id'],
        ]);

        foreach ($validated['ids'] as $position => $id) {
            Skill::query()->whereKey($id)->update(['sort_order' => $position]);
        }

        return back();
    }
}
