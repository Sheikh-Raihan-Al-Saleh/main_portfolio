<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LandingSectionRequest;
use App\Models\LandingSection;
use App\Services\LandingSectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Content blocks for the studio home page and project landing pages.
 */
class LandingSectionController extends Controller
{
    public function __construct(
        private readonly LandingSectionService $sections,
    ) {}

    public function store(LandingSectionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->sections->create($this->sections->owner($data), $data);

        $this->toast(__('Section added.'));

        return back();
    }

    public function update(LandingSectionRequest $request, LandingSection $landingSection): RedirectResponse
    {
        $this->sections->update($landingSection, $request->validated());

        $this->toast(__('Section updated.'));

        return back();
    }

    public function destroy(LandingSection $landingSection): RedirectResponse
    {
        $this->sections->delete($landingSection);

        $this->toast(__('Section deleted.'), 'error');

        return back();
    }

    /**
     * Upload one image or video for a section before it is saved. Returns
     * JSON, because the builder needs the stored path right away.
     */
    public function uploadMedia(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,avif,gif,mp4,webm', 'max:51200'],
            'directory' => ['required', 'string', 'in:steps,gallery,video,poster,avatar'],
        ]);

        return response()->json(
            $this->sections->uploadMedia($validated['file'], $validated['directory']),
        );
    }
}
