<?php

namespace App\Services;

use App\Models\Education;
use Illuminate\Database\Eloquent\Collection;

/**
 * The founder's education history.
 */
class EducationService
{
    public function __construct(
        private readonly FounderService $founders,
    ) {}

    /**
     * @return Collection<int, Education>
     */
    public function adminIndex(): Collection
    {
        return $this->founders->current()->educations()->ordered()->get();
    }

    /**
     * @param  array<string, mixed>  $data  Validated form data.
     */
    public function create(array $data): Education
    {
        $founder = $this->founders->current();

        return $founder->educations()->create([
            ...$this->attributes($data),
            'sort_order' => (int) $founder->educations()->max('sort_order') + 1,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data  Validated form data.
     */
    public function update(Education $education, array $data): Education
    {
        $education->update($this->attributes($data));

        return $education;
    }

    public function delete(Education $education): void
    {
        $education->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        return collect($data)->only([
            'institution', 'degree', 'field_of_study', 'start_date', 'end_date',
            'grade', 'description',
        ])->all();
    }
}
