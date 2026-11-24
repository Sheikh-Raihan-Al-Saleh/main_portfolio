<?php

namespace App\Services;

use App\Models\ContactMessage;
use App\Models\Founder;
use App\Models\Project;
use App\Models\Studio;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * The numbers and charts of the admin dashboard.
 */
class DashboardService
{
    /**
     * Content counts, cached for one minute.
     *
     * @return array<string, int>
     */
    public function stats(): array
    {
        return Cache::remember('dashboard.stats', 60, function (): array {
            $studio = Studio::current()->loadCount(['projects', 'clients', 'sections']);
            $founder = Founder::current()->loadCount(['projects', 'skills', 'experiences', 'educations']);

            return [
                'projects' => $studio->projects_count + $founder->projects_count,
                'publishedProjects' => Project::published()->count(),
                'clients' => $studio->clients_count,
                'skills' => $founder->skills_count,
                'experiences' => $founder->experiences_count,
                'educations' => $founder->educations_count,
                'messages' => ContactMessage::count(),
                'unreadMessages' => ContactMessage::whereNull('read_at')->count(),
                'homeBlocks' => $studio->sections_count,
            ];
        });
    }

    /**
     * Daily counts for the last 30 days: new messages and projects touched,
     * as two series aligned to the same labels.
     *
     * @return array{labels: list<string>, messages: list<int>, projects: list<int>}
     */
    public function activity(): array
    {
        $since = now()->subDays(29)->startOfDay();

        $messages = $this->countByDay(ContactMessage::where('created_at', '>=', $since)->pluck('created_at'));
        $projects = $this->countByDay(Project::where('updated_at', '>=', $since)->pluck('updated_at'));

        $series = ['labels' => [], 'messages' => [], 'projects' => []];

        for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
            $day = now()->subDays($daysAgo);
            $key = $day->format('Y-m-d');

            $series['labels'][] = $day->format('M j');
            $series['messages'][] = $messages[$key] ?? 0;
            $series['projects'][] = $projects[$key] ?? 0;
        }

        return $series;
    }

    /**
     * Recent vs older enquiries, for the donut chart. Messages store no
     * referrer, so a time split is the only honest breakdown.
     *
     * @return list<array{source: string, count: int}>
     */
    public function messageSources(): array
    {
        $cutoff = now()->subDays(30);
        $recent = ContactMessage::where('created_at', '>=', $cutoff)->count();
        $older = ContactMessage::where('created_at', '<', $cutoff)->count();

        if ($recent === 0 && $older === 0) {
            return [];
        }

        $sources = [['source' => 'Last 30 days', 'count' => $recent]];

        if ($older > 0) {
            $sources[] = ['source' => 'Older', 'count' => $older];
        }

        return $sources;
    }

    /**
     * How complete the studio profile is.
     *
     * @return array{percent: int, missing: list<string>}
     */
    public function studioCompleteness(Studio $studio): array
    {
        return $this->score([
            'Name' => $studio->name !== 'Your Studio',
            'Logo' => filled($studio->logo_path),
            'Logo mark' => filled($studio->logo_mark_path),
            'Headline' => filled($studio->headline),
            'Tagline' => filled($studio->tagline),
            'Bio' => filled($studio->bio),
            'Mission' => filled($studio->mission),
            'Location' => filled($studio->location),
            'Public email' => filled($studio->public_email),
            'Social links' => $studio->socialLinks->isNotEmpty(),
            'Meta description' => filled($studio->meta_description),
            'Clients' => $studio->clients()->exists(),
        ]);
    }

    /**
     * How complete the founder profile is.
     *
     * @return array{percent: int, missing: list<string>}
     */
    public function founderCompleteness(Founder $founder): array
    {
        return $this->score([
            'Name' => $founder->name !== 'Your Name',
            'Headline' => filled($founder->headline),
            'Tagline' => filled($founder->tagline),
            'Bio' => filled($founder->bio),
            'Avatar' => filled($founder->avatar_path),
            'Roles' => filled($founder->roles),
            'Skills' => $founder->skills()->exists(),
            'Experience' => $founder->experiences()->exists(),
            'Education' => $founder->educations()->exists(),
            'Resume' => filled($founder->resume_path),
            'Social links' => $founder->socialLinks->isNotEmpty(),
            'Meta description' => filled($founder->meta_description),
        ]);
    }

    /**
     * How many rows fall on each day, keyed "Y-m-d".
     *
     * @param  Collection<int, mixed>  $timestamps
     * @return array<string, int>
     */
    private function countByDay(Collection $timestamps): array
    {
        return $timestamps
            ->countBy(fn (CarbonInterface $timestamp): string => $timestamp->format('Y-m-d'))
            ->all();
    }

    /**
     * The share of filled fields as a percentage, with the missing ones by label.
     *
     * @param  array<string, bool>  $fields
     * @return array{percent: int, missing: list<string>}
     */
    private function score(array $fields): array
    {
        $missing = array_keys(array_filter($fields, fn (bool $filled): bool => ! $filled));

        return [
            'percent' => (int) round(((count($fields) - count($missing)) / count($fields)) * 100),
            'missing' => $missing,
        ];
    }
}
