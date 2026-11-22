<?php

namespace App\Http\Controllers\Experience;

use App\Http\Controllers\Controller;
use App\Http\Requests\Experience\StoreExperienceRequest;
use App\Http\Requests\Experience\UpdateExperienceRequest;
use App\Http\Requests\ReorderRequest;
use App\Models\Experience;
use App\Services\Experience\ExperienceService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the founder's work history.
 */
class ExperienceController extends Controller
{
    public function __construct(
        private readonly ExperienceService $experienceService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/experiences/Index', [
            'experiences' => $this->experienceService->list(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/experiences/Create');
    }

    public function store(StoreExperienceRequest $request): RedirectResponse
    {
        $this->experienceService->create($request->validated());
        $this->toast(__('Experience created.'));

        return to_route('admin.experiences.index');
    }

    public function edit(Experience $experience): Response
    {
        return Inertia::render('admin/experiences/Edit', [
            'experience' => $this->experienceService->find($experience),
        ]);
    }

    public function update(UpdateExperienceRequest $request, Experience $experience): RedirectResponse
    {
        $this->experienceService->update($experience, $request->validated());
        $this->toast(__('Experience updated.'));

        return to_route('admin.experiences.index');
    }

    public function destroy(Experience $experience): RedirectResponse
    {
        $this->experienceService->delete($experience);
        $this->toast(__('Experience deleted.'), 'error');

        return back();
    }

    public function reorder(ReorderRequest $request): RedirectResponse
    {
        $this->experienceService->reorder($request->validated('ids'));

        return back();
    }
}
