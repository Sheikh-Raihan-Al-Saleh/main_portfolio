<?php

namespace App\Services;

use App\Models\Experience;
use App\Models\Founder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Creates, updates and deletes the founder's roles with their logo and
 * highlight bullets.
 */
class ExperienceService
{
    /** Form fields saved to the table. */
    private const FIELDS = ['company', 'role', 'employment_type', 'location', 'start_date', 'end_date', 'description', 'company_url'];

    public function __construct(private FileService $files) {}

    /**
     * Add a role at the end of the list, with its logo and highlights.
     *
     * @param  array<string, mixed>  $data  Validated form data.
     */
    public function create(array $data): Experience
    {
        return DB::transaction(function () use ($data): Experience {
            $founder = Founder::current();

            $experience = new Experience(Arr::only($data, self::FIELDS));
            $experience->sort_order = (int) $founder->experiences()->max('sort_order') + 1;
            $experience->logo_path = isset($data['logo']) ? $this->files->store($data['logo'], 'experiences') : null;
            $founder->experiences()->save($experience);

            $this->syncHighlights($experience, $data['highlights'] ?? []);

            return $experience;
        });
    }

    /**
     * Save the changes to a role, its logo and its highlights.
     *
     * @param  array<string, mixed>  $data  Validated form data.
     */
    public function update(Experience $experience, array $data): void
    {
        DB::transaction(function () use ($experience, $data): void {
            $experience->fill(Arr::only($data, self::FIELDS));
            $experience->logo_path = $this->files->replace(
                $data['logo'] ?? null,
                'experiences',
                $experience->logo_path,
                (bool) ($data['remove_logo'] ?? false),
            );
            $experience->save();

            $this->syncHighlights($experience, $data['highlights'] ?? []);
        });
    }

    /**
     * Delete a role (its highlights cascade) and its logo file.
     */
    public function delete(Experience $experience): void
    {
        $experience->delete();
        $this->files->delete($experience->logo_path);
    }

    /**
     * Replace a role's highlight bullets with the submitted list. Blank lines
     * are skipped.
     *
     * @param  array<int, string|null>  $highlights
     */
    public function syncHighlights(Experience $experience, array $highlights): void
    {
        $experience->highlights()->delete();

        $position = 0;

        foreach ($highlights as $body) {
            $body = trim((string) $body);

            if ($body !== '') {
                $experience->highlights()->create(['body' => $body, 'sort_order' => $position++]);
            }
        }
    }
}
