<?php

namespace App\Http\Controllers\Technology;

use App\Http\Controllers\Controller;
use App\Http\Requests\Technology\StoreTechnologyRequest;
use App\Http\Requests\Technology\UpdateTechnologyRequest;
use App\Models\Technology;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the tech-stack vocabulary. Edited in dialogs on the list page.
 */
class TechnologyController extends Controller
{
    /**
     * Show every technology, alphabetically, with how many projects use each.
     */
    public function index(): Response
    {
        return Inertia::render('technology/TechnologyIndex', [
            'technologies' => Technology::withCount('projects')->orderBy('name')->get(),
        ]);
    }

    /**
     * Save a new technology.
     */
    public function store(StoreTechnologyRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Technology::create([...$data, 'slug' => Str::slug($data['name'])]);

        $this->toast(__('Technology added.'));

        return back();
    }

    /**
     * Save the changes to a technology.
     */
    public function update(UpdateTechnologyRequest $request, Technology $technology): RedirectResponse
    {
        $data = $request->validated();

        $technology->update([...$data, 'slug' => Str::slug($data['name'])]);

        $this->toast(__('Technology updated.'));

        return back();
    }

    /**
     * Delete a technology; it is detached from every project (pivot cascades).
     */
    public function destroy(Technology $technology): RedirectResponse
    {
        $technology->delete();

        $this->toast(__('Technology deleted.'), 'error');

        return back();
    }
}
