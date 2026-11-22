<?php

namespace App\Http\Controllers\Technology;

use App\Http\Controllers\Controller;
use App\Http\Requests\Technology\StoreTechnologyRequest;
use App\Http\Requests\Technology\UpdateTechnologyRequest;
use App\Models\Technology;
use App\Services\Technology\TechnologyService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the tech-stack vocabulary. Edited in dialogs on the list page.
 */
class TechnologyController extends Controller
{
    public function __construct(
        private readonly TechnologyService $technologyService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/technologies/Index', [
            'technologies' => $this->technologyService->list(),
        ]);
    }

    public function store(StoreTechnologyRequest $request): RedirectResponse
    {
        $this->technologyService->create($request->validated());
        $this->toast(__('Technology added.'));

        return back();
    }

    public function update(UpdateTechnologyRequest $request, Technology $technology): RedirectResponse
    {
        $this->technologyService->update($technology, $request->validated());
        $this->toast(__('Technology updated.'));

        return back();
    }

    public function destroy(Technology $technology): RedirectResponse
    {
        $this->technologyService->delete($technology);
        $this->toast(__('Technology deleted.'), 'error');

        return back();
    }
}
