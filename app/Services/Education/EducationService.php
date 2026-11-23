<?php

namespace App\Services\Education;

use App\Models\Education;
use App\Services\Founder\FounderService;
use App\Support\SortOrder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

/**
 * Education module: the founder's education history.
 */
class EducationService
{
    /** Form fields saved to the table. */
    private const FIELDS = ['institution', 'degree', 'field_of_study', 'start_date', 'end_date', 'grade', 'description'];

    public function __construct(
        private readonly FounderService $founderService,
    ) {}

    /**
     * All education entries, in order.
     *
     * @return Collection<int, Education>
     */
    public function list(): Collection
    {
        return $this->founderService->current()->educations()
            ->orderBy('sort_order')
            ->orderByDesc('start_date')
            ->get();
    }

    /**
     * Add an entry at the end of the list.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Education
    {
        $founder = $this->founderService->current();

        return $founder->educations()->create([
            ...Arr::only($data, self::FIELDS),
            'sort_order' => (int) $founder->educations()->max('sort_order') + 1,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Education $education, array $data): Education
    {
        $education->update(Arr::only($data, self::FIELDS));

        return $education;
    }

    /**
     * Delete an education entry.
     */
    public function delete(Education $education): void
    {
        $education->delete();
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        SortOrder::apply($this->founderService->current()->educations(), $orderedIds);
    }
}
