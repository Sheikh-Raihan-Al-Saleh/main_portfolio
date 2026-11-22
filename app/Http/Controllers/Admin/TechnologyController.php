<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TechnologyRequest;
use App\Models\Technology;
use App\Services\TechnologyService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The shared technology vocabulary. Edited in dialogs on the index page.
 */
class TechnologyController extends Controller
{
    public function __construct(
        private readonly TechnologyService $technologies,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/technologies/Index', [
            'technologies' => $this->technologies->all(),
        ]);
    }

    public function store(TechnologyRequest $request): RedirectResponse
    {
        $this->technologies->create($request->validated());

        $this->toast(__('Technology added.'));

        return back();
    }

    public function update(TechnologyRequest $request, Technology $technology): RedirectResponse
    {
        $this->technologies->update($technology, $request->validated());

        $this->toast(__('Technology updated.'));

        return back();
    }

    public function destroy(Technology $technology): RedirectResponse
    {
        $this->technologies->delete($technology);

        $this->toast(__('Technology deleted.'), 'error');

        return back();
    }
}
