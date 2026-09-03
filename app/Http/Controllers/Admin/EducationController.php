<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EducationRequest;
use App\Models\Education;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EducationController extends Controller
{
    public function index(): Response
    {
        return inertia('admin/educations/Index', [
            'educations' => Education::query()->ordered()->get(),
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/educations/Create');
    }

    public function store(EducationRequest $request): RedirectResponse
    {
        Education::create([
            ...$request->validated(),
            'sort_order' => $request->integer('sort_order') ?: (int) Education::query()->max('sort_order') + 1,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Education created.')]);

        return to_route('admin.educations.index');
    }

    public function edit(Education $education): Response
    {
        return inertia('admin/educations/Edit', ['education' => $education]);
    }

    public function update(EducationRequest $request, Education $education): RedirectResponse
    {
        $education->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Education updated.')]);

        return to_route('admin.educations.index');
    }

    public function destroy(Education $education): RedirectResponse
    {
        $education->delete();

        Inertia::flash('toast', ['type' => 'error', 'message' => __('Education deleted.')]);

        return back();
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:educations,id'],
        ]);

        foreach ($validated['ids'] as $position => $id) {
            Education::query()->whereKey($id)->update(['sort_order' => $position]);
        }

        return back();
    }
}
