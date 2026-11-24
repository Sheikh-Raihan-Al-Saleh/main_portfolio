<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Http\Requests\Education\StoreEducationRequest;
use App\Http\Requests\Education\UpdateEducationRequest;
use App\Http\Requests\ReorderRequest;
use App\Models\Education;
use App\Models\Founder;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the founder's education history.
 */
class EducationController extends Controller
{
    /**
     * Show every education entry, in order.
     */
    public function index(): Response
    {
        return Inertia::render('education/EducationIndex', [
            'educations' => Founder::current()->educations()->ordered()->get(),
        ]);
    }

    /**
     * Show the form to create an education entry.
     */
    public function create(): Response
    {
        return Inertia::render('education/CreateEducation');
    }

    /**
     * Save a new entry at the end of the list.
     */
    public function store(StoreEducationRequest $request): RedirectResponse
    {
        $founder = Founder::current();

        $founder->educations()->create([
            ...$request->validated(),
            'sort_order' => (int) $founder->educations()->max('sort_order') + 1,
        ]);

        $this->toast(__('Education created.'));

        return to_route('educationIndex');
    }

    /**
     * Show the form to edit an education entry.
     */
    public function edit(Education $education): Response
    {
        return Inertia::render('education/EditEducation', ['education' => $education]);
    }

    /**
     * Save the changes to an education entry.
     */
    public function update(UpdateEducationRequest $request, Education $education): RedirectResponse
    {
        $education->update($request->validated());

        $this->toast(__('Education updated.'));

        return to_route('educationIndex');
    }

    /**
     * Delete an education entry.
     */
    public function destroy(Education $education): RedirectResponse
    {
        $education->delete();

        $this->toast(__('Education deleted.'), 'error');

        return back();
    }

    /**
     * Save the new display order of the education entries.
     */
    public function reorder(ReorderRequest $request): RedirectResponse
    {
        $this->saveSortOrder(Founder::current()->educations(), $request->validated('ids'));

        return back();
    }
}
