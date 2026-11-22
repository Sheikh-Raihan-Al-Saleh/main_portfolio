<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Http\Requests\Education\StoreEducationRequest;
use App\Http\Requests\Education\UpdateEducationRequest;
use App\Http\Requests\ReorderRequest;
use App\Models\Education;
use App\Services\Education\EducationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the founder's education history.
 */
class EducationController extends Controller
{
    public function __construct(
        private readonly EducationService $educationService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/educations/Index', [
            'educations' => $this->educationService->list(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/educations/Create');
    }

    public function store(StoreEducationRequest $request): RedirectResponse
    {
        $this->educationService->create($request->validated());
        $this->toast(__('Education created.'));

        return to_route('admin.educations.index');
    }

    public function edit(Education $education): Response
    {
        return Inertia::render('admin/educations/Edit', ['education' => $education]);
    }

    public function update(UpdateEducationRequest $request, Education $education): RedirectResponse
    {
        $this->educationService->update($education, $request->validated());
        $this->toast(__('Education updated.'));

        return to_route('admin.educations.index');
    }

    public function destroy(Education $education): RedirectResponse
    {
        $this->educationService->delete($education);
        $this->toast(__('Education deleted.'), 'error');

        return back();
    }

    public function reorder(ReorderRequest $request): RedirectResponse
    {
        $this->educationService->reorder($request->validated('ids'));

        return back();
    }
}
