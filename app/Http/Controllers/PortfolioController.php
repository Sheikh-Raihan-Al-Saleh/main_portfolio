<?php

namespace App\Http\Controllers;

use App\Models\Education;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortfolioController extends Controller
{
    /**
     * The public landing page: every section of the portfolio on one scroll.
     */
    public function index(): Response
    {
        return inertia('public/Home', [
            'projects' => Project::query()->published()->ordered()->take(6)->get(),
            'skillGroups' => $this->skillGroups(),
            'experiences' => Experience::query()->ordered()->get(),
            'educations' => Education::query()->ordered()->get(),
            'stats' => [
                'projects' => Project::query()->published()->count(),
                'skills' => Skill::query()->count(),
                'yearsExperience' => $this->yearsOfExperience(),
            ],
        ]);
    }

    /**
     * The full project archive.
     *
     * Every published project ships in one payload and the technology filter is
     * applied client-side, so switching tags is instant. The incoming `tech`
     * query string only seeds the initial selection.
     */
    public function projects(Request $request): Response
    {
        $tech = $request->string('tech')->trim()->value();

        $projects = Project::query()->published()->ordered()->get();

        $technologies = $projects
            ->flatMap(fn (Project $project): array => $project->tech_stack ?? [])
            ->unique()
            ->sort()
            ->values();

        // Ignore a tag that no published project actually carries, so a stale
        // link cannot land the visitor on a permanently empty grid.
        if ($tech !== '' && ! $technologies->contains($tech)) {
            $tech = '';
        }

        return inertia('public/Projects', [
            'projects' => $projects,
            'technologies' => $technologies,
            'filters' => ['tech' => $tech ?: null],
        ]);
    }

    public function showProject(Project $project): Response
    {
        abort_unless($project->is_published, 404);

        return inertia('public/ProjectShow', [
            'project' => $project,
            'related' => Project::query()
                ->published()
                ->whereKeyNot($project->id)
                ->ordered()
                ->take(3)
                ->get(),
        ]);
    }

    /**
     * Stream the uploaded resume so the URL stays stable regardless of filename.
     */
    public function resume(): StreamedResponse
    {
        $profile = Profile::current();

        abort_if(blank($profile->resume_path), 404);
        abort_unless(Storage::disk('uploads')->exists($profile->resume_path), 404);

        return Storage::disk('uploads')->response(
            $profile->resume_path,
            str($profile->name)->slug().'-resume.pdf',
        );
    }

    /**
     * Skills bucketed by category, preserving the order defined in Skill::categories().
     *
     * @return array<int, array{key: string, label: string, skills: array<int, Skill>}>
     */
    private function skillGroups(): array
    {
        $skills = Skill::query()->ordered()->get()->groupBy('category');

        return collect(Skill::categories())
            ->map(fn (string $label, string $key): array => [
                'key' => $key,
                'label' => $label,
                'skills' => $skills->get($key, collect())->values()->all(),
            ])
            ->filter(fn (array $group): bool => $group['skills'] !== [])
            ->values()
            ->all();
    }

    /**
     * Years between the earliest role start date and today.
     */
    private function yearsOfExperience(): int
    {
        $earliest = Experience::query()->min('start_date');

        return $earliest === null ? 0 : (int) now()->diffInYears($earliest, absolute: true);
    }
}
