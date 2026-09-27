<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\HandlesMediaUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LandingSectionRequest;
use App\Models\Company;
use App\Models\LandingSection;
use App\Models\ProjectLandingPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LandingSectionController extends Controller
{
    use HandlesMediaUploads;

    /**
     * Append a section to whichever page owns it: a project's landing page, or
     * the company home page. The request guarantees exactly one owner key.
     */
    public function store(LandingSectionRequest $request): RedirectResponse
    {
        $section = new LandingSection($request->sectionAttributes());

        $section->sort_order = $this->nextSortOrder(
            $section->company_id,
            $section->landing_page_id,
        );

        $section->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Section added.')]);

        return back();
    }

    public function update(
        LandingSectionRequest $request,
        LandingSection $landingSection,
    ): RedirectResponse {
        $previousMedia = $landingSection->mediaPaths();

        $landingSection->fill($request->sectionAttributes());
        $landingSection->save();

        // Anything the edit dropped from the payload is now unreferenced.
        $this->deleteMediaMany(
            array_diff($previousMedia, $landingSection->mediaPaths()),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Section updated.')]);

        return back();
    }

    public function destroy(LandingSection $landingSection): RedirectResponse
    {
        $this->deleteMediaMany($landingSection->mediaPaths());
        $landingSection->delete();

        Inertia::flash('toast', ['type' => 'error', 'message' => __('Section deleted.')]);

        return back();
    }

    /**
     * Persist a new ordering for one landing page's sections.
     */
    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:landing_sections,id'],
        ]);

        foreach ($validated['ids'] as $position => $id) {
            LandingSection::query()->whereKey($id)->update(['sort_order' => $position]);
        }

        return back();
    }

    /**
     * The sort order to append at, given whichever owner the section has.
     */
    private function nextSortOrder(?int $companyId, ?int $landingPageId): int
    {
        $relation = $companyId !== null
            ? Company::query()->findOrFail($companyId)->sections()
            : ProjectLandingPage::query()->findOrFail($landingPageId)->sections();

        return (int) $relation->max('sort_order') + 1;
    }

    /**
     * Store one image or video for a section payload and hand back its path.
     *
     * Returns JSON rather than an Inertia redirect because the builder needs
     * the path immediately to write into the section's `data`.
     */
    public function uploadMedia(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,avif,gif,mp4,webm', 'max:51200'],
            'directory' => ['required', 'string', 'in:steps,gallery,video,poster,avatar'],
        ]);

        $path = $this->putMedia(
            $request->file('file'),
            'landing/'.$validated['directory'],
        );

        return response()->json([
            'path' => $path,
            'url' => Storage::disk('uploads')->url($path),
        ]);
    }
}
