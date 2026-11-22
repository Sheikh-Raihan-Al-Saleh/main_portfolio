<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EducationRequest;
use App\Models\Education;
use App\Services\EducationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EducationController extends Controller
{
    public function __construct(
        private readonly EducationService $educations,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/educations/Index', [
            'educations' => $this->educations->adminIndex(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/educations/Create');
    }

    public function store(EducationRequest $request): RedirectResponse
    {
        $this->educations->create($request->validated());

        $this->toast(__('Education created.'));

        return to_route('admin.educations.index');
    }

    public function edit(Education $education): Response
    {
        return Inertia::render('admin/educations/Edit', ['education' => $education]);
    }

    public function update(EducationRequest $request, Education $education): RedirectResponse
    {
        $this->educations->update($education, $request->validated());

        $this->toast(__('Education updated.'));

        return to_route('admin.educations.index');
    }

    public function destroy(Education $education): RedirectResponse
    {
        $this->educations->delete($education);

        $this->toast(__('Education deleted.'), 'error');

        return back();
    }
}
