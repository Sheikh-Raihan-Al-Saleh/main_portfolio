<?php

namespace App\Services;

use App\Models\Experience;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The founder's work history and its highlight bullets.
 */
class ExperienceService
{
    public function __construct(
        private readonly MediaService $media,
        private readonly FounderService $founders,
    ) {}

    /**
     * @return Collection<int, Experience>
     */
    public function adminIndex(): Collection
    {
        return $this->founders->current()->experiences()->ordered()->with('highlights')->get();
    }

    public function loadForEdit(Experience $experience): Experience
    {
        return $experience->load('highlights');
    }

    /**
     * @param  array<string, mixed>  $data  Validated form data (files included).
     */
    public function create(array $data): Experience
    {
        return DB::transaction(function () use ($data): Experience {
            $founder = $this->founders->current();

            $experience = new Experience($this->attributes($data));
            $experience->sort_order = (int) $founder->experiences()->max('sort_order') + 1;
            $experience->logo_path = isset($data['logo']) ? $this->media->store($data['logo'], 'experiences') : null;

            $founder->experiences()->save($experience);
            $this->syncHighlights($experience, $data['highlights'] ?? []);

            return $experience;
        });
    }

    /**
     * @param  array<string, mixed>  $data  Validated form data (files included).
     */
    public function update(Experience $experience, array $data): Experience
    {
        return DB::transaction(function () use ($experience, $data): Experience {
            $experience->fill($this->attributes($data));
            $experience->logo_path = $this->media->replace(
                $data['logo'] ?? null,
                'experiences',
                $experience->logo_path,
                (bool) ($data['remove_logo'] ?? false),
            );
            $experience->save();

            $this->syncHighlights($experience, $data['highlights'] ?? []);

            return $experience;
        });
    }

    public function delete(Experience $experience): void
    {
        $experience->delete();
        $this->media->delete($experience->logo_path);
    }

    /**
     * Replace the highlight bullets with the submitted list, in order.
     *
     * @param  array<int, string|null>  $highlights
     */
    private function syncHighlights(Experience $experience, array $highlights): void
    {
        $experience->highlights()->delete();

        $rows = collect($highlights)
            ->map(fn (?string $body): string => trim((string) $body))
            ->filter()
            ->values()
            ->map(fn (string $body, int $position): array => ['body' => $body, 'sort_order' => $position]);

        $experience->highlights()->createMany($rows->all());
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        return collect($data)->only([
            'company', 'role', 'employment_type', 'location', 'start_date',
            'end_date', 'description', 'company_url',
        ])->all();
    }
}
