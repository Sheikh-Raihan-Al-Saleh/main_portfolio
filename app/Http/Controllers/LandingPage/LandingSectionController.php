<?php

namespace App\Http\Controllers\LandingPage;

use App\Http\Controllers\Controller;
use App\Http\Requests\LandingPage\StoreLandingSectionRequest;
use App\Http\Requests\LandingPage\UpdateLandingSectionRequest;
use App\Http\Requests\LandingPage\UploadLandingMediaRequest;
use App\Http\Requests\ReorderRequest;
use App\Models\LandingSection;
use App\Services\FileService;
use App\Services\LandingSectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Admin: content blocks of the studio home page and project landing pages.
 */
class LandingSectionController extends Controller
{
    public function __construct(private LandingSectionService $sections) {}

    /**
     * Add a block at the end of its page.
     */
    public function store(StoreLandingSectionRequest $request): RedirectResponse
    {
        $this->sections->create($request->validated());

        $this->toast(__('Section added.'));

        return back();
    }

    /**
     * Save a block, deleting any file the edit dropped from it.
     */
    public function update(UpdateLandingSectionRequest $request, LandingSection $landingSection): RedirectResponse
    {
        $this->sections->update($landingSection, $request->validated());

        $this->toast(__('Section updated.'));

        return back();
    }

    /**
     * Delete a block and its files.
     */
    public function destroy(LandingSection $landingSection): RedirectResponse
    {
        $this->sections->delete($landingSection);

        $this->toast(__('Section deleted.'), 'error');

        return back();
    }

    /**
     * Save a new block order. Only blocks of the same page as the first ID
     * are moved.
     */
    public function reorder(ReorderRequest $request): RedirectResponse
    {
        $orderedIds = array_map('intval', $request->validated('ids'));
        $first = isset($orderedIds[0]) ? LandingSection::find($orderedIds[0]) : null;

        if ($first !== null) {
            $samePage = LandingSection::where('sectionable_type', $first->sectionable_type)
                ->where('sectionable_id', $first->sectionable_id);

            $this->saveSortOrder($samePage, $orderedIds);
        }

        return back();
    }

    /**
     * Store an image or video for a block before the block is saved. Returns
     * JSON, because the builder needs the stored path right away.
     */
    public function uploadMedia(UploadLandingMediaRequest $request, FileService $files): JsonResponse
    {
        $path = $files->store($request->file('file'), 'landing/'.$request->validated('directory'));

        return response()->json(['path' => $path, 'url' => FileService::url($path)]);
    }
}
