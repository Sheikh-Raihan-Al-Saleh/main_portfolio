<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use App\Http\Resources\Experience\ExperienceResource;
use App\Http\Resources\Project\ProjectCardResource;
use App\Models\Founder;
use App\Models\Project;
use App\Services\FileService;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Public founder pages: the portfolio (/founder) and the resume (/resume).
 */
class FounderPageController extends Controller
{
    /**
     * Show the founder portfolio page: skills, experience, education and projects.
     */
    public function show(): Response
    {
        $founder = Founder::current();

        // Only categories that have skills are shown.
        $skillGroups = $founder->skillCategories()->has('skills')->with('skills')->ordered()->get();
        $experiences = $founder->experiences()->with('highlights')->ordered()->get();
        $educations = $founder->educations()->ordered()->get();
        $projects = Project::forCards()->published()->ownedBy($founder)->ordered()->get();

        return Inertia::render('founder/FounderPage', [
            'content' => $founder->pageContent(),
            'skillGroups' => $skillGroups,
            'experiences' => ExperienceResource::collection($experiences)->resolve(),
            'educations' => $educations,
            'projects' => ProjectCardResource::collection($projects)->resolve(),
            'stats' => [
                'projects' => $projects->count(),
                'skills' => $founder->skills()->count(),
                'yearsExperience' => $this->yearsOfExperience($experiences->min('start_date')),
            ],
        ]);
    }

    /**
     * Download the uploaded resume with a readable filename, or 404 when there is none.
     */
    public function resume(FileService $files): StreamedResponse
    {
        $founder = Founder::current();

        abort_unless($files->exists($founder->resume_path), 404);

        return Storage::disk(FileService::DISK)->response(
            (string) $founder->resume_path,
            Str::slug($founder->name).'-resume.pdf',
        );
    }

    /**
     * Whole years since the first job started, or 0 without any job.
     */
    private function yearsOfExperience(?CarbonInterface $firstJobStart): int
    {
        return $firstJobStart ? (int) $firstJobStart->diffInYears(now(), absolute: true) : 0;
    }
}
