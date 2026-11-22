<?php

namespace App\Http\Controllers\LandingPage;

use App\Http\Controllers\Controller;
use App\Http\Requests\LandingPage\StoreLandingSectionRequest;
use App\Http\Requests\LandingPage\UpdateLandingSectionRequest;
use App\Http\Requests\LandingPage\UploadLandingMediaRequest;
use App\Http\Requests\ReorderRequest;
use App\Models\LandingSection;
use App\Services\LandingPage\LandingSectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Admin: content blocks of the studio home page and project landing pages.
 */
class LandingSectionController extends Controller
{
    public function __construct(
        private readonly LandingSectionService $sectionService,
    ) {}

    public function store(StoreLandingSectionRequest $request): RedirectResponse
    {
        $owner = $this->sectionService->ownerFor($request->validated('owner'), $request->validated('project_id'));
        $this->sectionService->create($owner, $request->validated());
        $this->toast(__('Section added.'));

        return back();
    }

    public function update(UpdateLandingSectionRequest $request, LandingSection $landingSection): RedirectResponse
    {
        $this->sectionService->update($landingSection, $request->validated());
        $this->toast(__('Section updated.'));

        return back();
    }

    public function destroy(LandingSection $landingSection): RedirectResponse
    {
        $this->sectionService->delete($landingSection);
        $this->toast(__('Section deleted.'), 'error');

        return back();
    }

    public function reorder(ReorderRequest $request): RedirectResponse
    {
        $this->sectionService->reorder($request->validated('ids'));

        return back();
    }

    /**
     * Upload an image or video for a block. Returns JSON, because the builder
     * needs the stored path right away.
     */
    public function uploadMedia(UploadLandingMediaRequest $request): JsonResponse
    {
        return response()->json(
            $this->sectionService->uploadMedia($request->file('file'), $request->validated('directory')),
        );
    }
}
