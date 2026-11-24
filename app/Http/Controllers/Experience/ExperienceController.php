<?php

namespace App\Http\Controllers\Experience;

use App\Http\Controllers\Controller;
use App\Http\Requests\Experience\StoreExperienceRequest;
use App\Http\Requests\Experience\UpdateExperienceRequest;
use App\Http\Requests\ReorderRequest;
use App\Http\Resources\Experience\ExperienceResource;
use App\Models\Experience;
use App\Models\Founder;
use App\Services\ExperienceService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the founder's work history and its highlight bullets.
 */
class ExperienceController extends Controller
{
    public function __construct(private ExperienceService $experiences) {}

    /**
     * Show every role, in order, with its highlights.
     */
    public function index(): Response
    {
        $experiences = Founder::current()->experiences()->with('highlights')->ordered()->get();

        return Inertia::render('experience/ExperienceIndex', [
            'experiences' => ExperienceResource::collection($experiences)->resolve(),
        ]);
    }

    /**
     * Show the form to create a role.
     */
    public function create(): Response
    {
        return Inertia::render('experience/CreateExperience');
    }

    /**
     * Save a new role at the end of the list, with its logo and highlights.
     */
    public function store(StoreExperienceRequest $request): RedirectResponse
    {
        $this->experiences->create($request->validated());

        $this->toast(__('Experience created.'));

        return to_route('experienceIndex');
    }

    /**
     * Show the form to edit a role.
     */
    public function edit(Experience $experience): Response
    {
        return Inertia::render('experience/EditExperience', [
            'experience' => ExperienceResource::make($experience->load('highlights'))->resolve(),
        ]);
    }

    /**
     * Save the changes to a role, its logo and its highlights.
     */
    public function update(UpdateExperienceRequest $request, Experience $experience): RedirectResponse
    {
        $this->experiences->update($experience, $request->validated());

        $this->toast(__('Experience updated.'));

        return to_route('experienceIndex');
    }

    /**
     * Delete a role (its highlights cascade) and its logo file.
     */
    public function destroy(Experience $experience): RedirectResponse
    {
        $this->experiences->delete($experience);

        $this->toast(__('Experience deleted.'), 'error');

        return back();
    }

    /**
     * Save the new display order of the roles.
     */
    public function reorder(ReorderRequest $request): RedirectResponse
    {
        $this->saveSortOrder(Founder::current()->experiences(), $request->validated('ids'));

        return back();
    }
}
