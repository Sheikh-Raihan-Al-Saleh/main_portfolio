<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExperienceRequest;
use App\Models\Experience;
use App\Services\ExperienceService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ExperienceController extends Controller
{
    public function __construct(
        private readonly ExperienceService $experiences,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/experiences/Index', [
            'experiences' => $this->experiences->adminIndex(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/experiences/Create');
    }

    public function store(ExperienceRequest $request): RedirectResponse
    {
        $this->experiences->create($request->validated());

        $this->toast(__('Experience created.'));

        return to_route('admin.experiences.index');
    }

    public function edit(Experience $experience): Response
    {
        return Inertia::render('admin/experiences/Edit', [
            'experience' => $this->experiences->loadForEdit($experience),
        ]);
    }

    public function update(ExperienceRequest $request, Experience $experience): RedirectResponse
    {
        $this->experiences->update($experience, $request->validated());

        $this->toast(__('Experience updated.'));

        return to_route('admin.experiences.index');
    }

    public function destroy(Experience $experience): RedirectResponse
    {
        $this->experiences->delete($experience);

        $this->toast(__('Experience deleted.'), 'error');

        return back();
    }
}
