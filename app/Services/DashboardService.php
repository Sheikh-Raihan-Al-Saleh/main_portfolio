<?php

namespace App\Services;

use App\Models\ContactMessage;
use App\Models\Founder;
use App\Models\Project;
use App\Models\Studio;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Numbers and lists for the admin dashboard.
 */
class DashboardService
{
    public function __construct(
        private readonly StudioService $studios,
        private readonly FounderService $founders,
    ) {}

    /**
     * Everything the dashboard renders. Counts are cached for a minute; the
     * recent lists are always fresh.
     *
     * @return array<string, mixed>
     */
    public function overview(): array
    {
        $studio = $this->studios->current();
        $founder = $this->founders->current();

        return [
            'stats' => Cache::remember('dashboard.stats', 60, fn (): array => $this->stats($studio, $founder)),
            'activity' => $this->activitySeries(),
            'completeness' => [
                'studio' => $this->studioCompleteness($studio),
                'founder' => $this->founderCompleteness($founder),
            ],
            'recentMessages' => ContactMessage::query()
                ->latest()
                ->limit(5)
                ->get(['id', 'name', 'email', 'subject', 'read_at', 'created_at']),
            'recentProjects' => Project::query()
                ->ordered()
                ->limit(5)
                ->get(['id', 'title', 'slug', 'is_published', 'is_featured', 'updated_at']),
            'messageSources' => $this->messageSources(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function stats(Studio $studio, Founder $founder): array
    {
        $studio->loadCount(['projects', 'clients', 'sections']);
        $founder->loadCount(['projects', 'skills', 'experiences', 'educations']);

        return [
            'projects' => $studio->projects_count + $founder->projects_count,
            'publishedProjects' => Project::query()->published()->count(),
            'clients' => $studio->clients_count,
            'skills' => $founder->skills_count,
            'experiences' => $founder->experiences_count,
            'educations' => $founder->educations_count,
            'messages' => ContactMessage::query()->count(),
            'unreadMessages' => ContactMessage::query()->unread()->count(),
            'homeBlocks' => $studio->sections_count,
        ];
    }

    /**
     * Daily counts for the last 30 days: new messages and projects touched,
     * as two series aligned to the same labels.
     *
     * @return array{labels: list<string>, messages: list<int>, projects: list<int>}
     */
    private function activitySeries(): array
    {
        $since = now()->subDays(29)->startOfDay();

        $messages = $this->countByDay(
            ContactMessage::query()->where('created_at', '>=', $since)->pluck('created_at'),
        );
        $projects = $this->countByDay(
            Project::query()->where('updated_at', '>=', $since)->pluck('updated_at'),
        );

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
     * @param  Collection<int, mixed>  $timestamps
     * @return array<string, int>
     */
    private function countByDay(Collection $timestamps): array
    {
        return $timestamps
            ->countBy(fn ($timestamp): string => $timestamp->format('Y-m-d'))
            ->all();
    }

    /**
     * Recent vs older enquiries, for the donut chart. Messages store no
     * referrer, so a time split is the only honest breakdown.
     *
     * @return list<array{source: string, count: int}>
     */
    private function messageSources(): array
    {
        $cutoff = now()->subDays(30);
        $recent = ContactMessage::query()->where('created_at', '>=', $cutoff)->count();
        $older = ContactMessage::query()->where('created_at', '<', $cutoff)->count();

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
     * @return array{percent: int, missing: list<string>}
     */
    private function studioCompleteness(Studio $studio): array
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
     * @return array{percent: int, missing: list<string>}
     */
    private function founderCompleteness(Founder $founder): array
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
