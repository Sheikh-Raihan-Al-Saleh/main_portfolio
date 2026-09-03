<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\HandlesMediaUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExperienceRequest;
use App\Models\Experience;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExperienceController extends Controller
{
    use HandlesMediaUploads;

    public function index(): Response
    {
        return inertia('admin/experiences/Index', [
            'experiences' => Experience::query()->ordered()->get(),
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/experiences/Create');
    }

    public function store(ExperienceRequest $request): RedirectResponse
    {
        $experience = new Experience($this->attributes($request));
        $experience->logo_path = $this->storeMedia($request->file('logo'), 'experiences');
        $experience->sort_order = (int) Experience::query()->max('sort_order') + 1;
        $experience->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Experience created.')]);

        return to_route('admin.experiences.index');
    }

    public function edit(Experience $experience): Response
    {
        return inertia('admin/experiences/Edit', ['experience' => $experience]);
    }

    public function update(ExperienceRequest $request, Experience $experience): RedirectResponse
    {
        $experience->fill($this->attributes($request));

        if ($request->boolean('remove_logo')) {
            $this->deleteMedia($experience->logo_path);
            $experience->logo_path = null;
        }

        $experience->logo_path = $this->storeMedia(
            $request->file('logo'), 'experiences', $experience->logo_path,
        );
        $experience->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Experience updated.')]);

        return to_route('admin.experiences.index');
    }

    public function destroy(Experience $experience): RedirectResponse
    {
        $this->deleteMedia($experience->logo_path);
        $experience->delete();

        Inertia::flash('toast', ['type' => 'error', 'message' => __('Experience deleted.')]);

        return back();
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:experiences,id'],
        ]);

        foreach ($validated['ids'] as $position => $id) {
            Experience::query()->whereKey($id)->update(['sort_order' => $position]);
        }

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(ExperienceRequest $request): array
    {
        return $request->safe()->only([
            'company', 'role', 'employment_type', 'location', 'start_date',
            'end_date', 'description', 'highlights', 'company_url',
        ]);
    }
}
