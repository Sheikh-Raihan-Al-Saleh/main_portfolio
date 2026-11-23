<?php

namespace App\Services\Experience;

use App\Models\Experience;
use App\Services\Founder\FounderService;
use App\Services\Media\MediaService;
use App\Support\SortOrder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Experience module: the founder's work history and its highlight bullets.
 */
class ExperienceService
{
    /** Form fields saved to the table. */
    private const FIELDS = ['company', 'role', 'employment_type', 'location', 'start_date', 'end_date', 'description', 'company_url'];

    public function __construct(
        private readonly MediaService $mediaService,
        private readonly FounderService $founderService,
    ) {}

    /**
     * All roles, in order, with their highlights.
     *
     * @return Collection<int, Experience>
     */
    public function list(): Collection
    {
        return $this->founderService->current()->experiences()
            ->with('highlights')
            ->orderBy('sort_order')
            ->orderByDesc('start_date')
            ->get();
    }

    /**
     * One role with its highlights, for the edit form.
     */
    public function find(Experience $experience): Experience
    {
        return $experience->load('highlights');
    }

    /**
     * Add a role at the end of the list.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Experience
    {
        return DB::transaction(function () use ($data): Experience {
            $founder = $this->founderService->current();

            $experience = new Experience(Arr::only($data, self::FIELDS));
            $experience->sort_order = (int) $founder->experiences()->max('sort_order') + 1;
            $experience->logo_path = isset($data['logo']) ? $this->mediaService->store($data['logo'], 'experiences') : null;
            $founder->experiences()->save($experience);

            $this->saveHighlights($experience, $data['highlights'] ?? []);

            return $experience;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Experience $experience, array $data): Experience
    {
        return DB::transaction(function () use ($experience, $data): Experience {
            $experience->fill(Arr::only($data, self::FIELDS));
            $experience->logo_path = $this->mediaService->replace(
                $data['logo'] ?? null,
                'experiences',
                $experience->logo_path,
                (bool) ($data['remove_logo'] ?? false),
            );
            $experience->save();

            $this->saveHighlights($experience, $data['highlights'] ?? []);

            return $experience;
        });
    }

    /**
     * Delete a role (its highlights cascade) and its logo file.
     */
    public function delete(Experience $experience): void
    {
        $experience->delete();
        $this->mediaService->delete($experience->logo_path);
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        SortOrder::apply($this->founderService->current()->experiences(), $orderedIds);
    }

    /**
     * Replace the highlight bullets with the submitted list. Blank lines are skipped.
     *
     * @param  array<int, string|null>  $highlights
     */
    private function saveHighlights(Experience $experience, array $highlights): void
    {
        $experience->highlights()->delete();

        $position = 0;

        foreach ($highlights as $body) {
            if (trim((string) $body) !== '') {
                $experience->highlights()->create(['body' => trim($body), 'sort_order' => $position++]);
            }
        }
    }
}
