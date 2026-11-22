<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Education;
use App\Models\Experience;
use App\Models\LandingSection;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Saves drag-and-drop ordering for every sortable list in the admin panel.
 */
class ReorderService
{
    /**
     * Sortable lists, by the {type} used in the reorder URL. The optional
     * parent columns say which rows belong to one list: IDs from different
     * parents are rejected, or their sort orders would interleave.
     *
     * @var array<string, array{model: class-string<Model>, parent: list<string>}>
     */
    public const TYPES = [
        'projects' => ['model' => Project::class, 'parent' => []],
        'project-images' => ['model' => ProjectImage::class, 'parent' => ['project_id']],
        'landing-sections' => ['model' => LandingSection::class, 'parent' => ['sectionable_type', 'sectionable_id']],
        'clients' => ['model' => Client::class, 'parent' => ['studio_id']],
        'skill-categories' => ['model' => SkillCategory::class, 'parent' => ['founder_id']],
        'skills' => ['model' => Skill::class, 'parent' => []],
        'experiences' => ['model' => Experience::class, 'parent' => ['founder_id']],
        'educations' => ['model' => Education::class, 'parent' => ['founder_id']],
    ];

    /**
     * Give each ID its position in the list as its sort_order.
     *
     * @param  array<int, int>  $ids
     */
    public function reorder(string $type, array $ids): void
    {
        ['model' => $modelClass, 'parent' => $parent] = self::TYPES[$type];

        $rows = $modelClass::query()->whereKey($ids)->get(['id', ...$parent]);

        if ($rows->count() !== count(array_unique($ids))) {
            throw ValidationException::withMessages(['ids' => __('Some items no longer exist. Reload and try again.')]);
        }

        if ($parent !== [] && $rows->map(fn (Model $row): string => implode('|', $row->only($parent)))->unique()->count() > 1) {
            throw ValidationException::withMessages(['ids' => __('All items must belong to the same list.')]);
        }

        DB::transaction(function () use ($modelClass, $ids): void {
            foreach (array_values($ids) as $position => $id) {
                $modelClass::query()->whereKey($id)->update(['sort_order' => $position]);
            }
        });
    }
}
