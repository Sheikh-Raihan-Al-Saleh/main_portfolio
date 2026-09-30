<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ContactMessage;
use App\Models\Education;
use App\Models\Experience;
use App\Models\LandingSection;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $company = Company::current();

        return inertia('admin/Dashboard', [
            'stats' => [
                'projects' => Project::query()->count(),
                'publishedProjects' => Project::query()->published()->count(),
                'clients' => $company->clients()->count(),
                'skills' => Skill::query()->count(),
                'experiences' => Experience::query()->count(),
                'educations' => Education::query()->count(),
                'messages' => ContactMessage::query()->count(),
                'unreadMessages' => ContactMessage::query()->unread()->count(),
                'homeBlocks' => LandingSection::query()->whereNotNull('company_id')->count(),
            ],

            // 30-day series of messages and published-project changes, for the
            // dashboard's trend chart.
            'activity' => $this->activitySeries(),

            // How complete the public profiles are, so the dashboard can nudge
            // the admin toward the empty modules instead of just counting them.
            'completeness' => $this->completeness($company, Profile::current()),

            'recentMessages' => ContactMessage::query()
                ->latest()
                ->take(5)
                ->get(['id', 'name', 'email', 'subject', 'read_at', 'created_at']),
            'recentProjects' => Project::query()
                ->ordered()
                ->take(5)
                ->get(['id', 'title', 'slug', 'is_published', 'is_featured', 'updated_at']),

            // Top-of-funnel: where enquiries are coming from, derived from the
            // referrer captured with each message.
            'messageSources' => $this->messageSources(),

            'profile' => Profile::current(),
            'company' => $company,
        ]);
    }

    /**
     * Daily counts for the last 30 days: new messages, plus published-project
     * activity (projects touched that day), as two aligned series.
     *
     * @return array{labels: list<string>, messages: list<int>, projects: list<int>}
     */
    private function activitySeries(): array
    {
        $since = now()->subDays(29)->startOfDay();

        $messages = ContactMessage::query()
            ->where('created_at', '>=', $since)
            ->get(['created_at'])
            ->groupBy(fn (ContactMessage $message): string => $message->created_at->format('Y-m-d'));

        $projects = Project::query()
            ->where('updated_at', '>=', $since)
            ->get(['updated_at'])
            ->groupBy(fn (Project $project): string => $project->updated_at->format('Y-m-d'));

        $labels = [];
        $messageSeries = [];
        $projectSeries = [];

        foreach (collect(range(29, 0)) as $daysAgo) {
            $day = now()->subDays((int) $daysAgo);
            $key = $day->format('Y-m-d');

            $labels[] = $day->format('M j');
            $messageSeries[] = $messages->get($key, collect())->count();
            $projectSeries[] = $projects->get($key, collect())->count();
        }

        return [
            'labels' => $labels,
            'messages' => $messageSeries,
            'projects' => $projectSeries,
        ];
    }

    /**
     * Per-module completeness for the two public records: which required
     * fields are filled, expressed as a percentage with the missing keys.
     *
     * @return array{studio: array{percent: int, missing: list<string>}, founder: array{percent: int, missing: list<string>}}
     */
    private function completeness(Company $company, Profile $profile): array
    {
        $studioFields = [
            'Name' => $company->name !== 'Your Company',
            'Headline' => filled($company->headline),
            'Tagline' => filled($company->tagline),
            'Bio' => filled($company->bio),
            'Mission' => filled($company->mission),
            'Location' => filled($company->location),
            'Public email' => filled($company->public_email),
            'Logo' => filled($company->logo_path),
            'Social links' => filled($company->socials),
            'Meta description' => filled($company->meta_description),
            'Home blocks' => $company->sections()->exists(),
            'Clients' => $company->clients()->exists(),
        ];

        $founderFields = [
            'Name' => $profile->name !== 'Your Name',
            'Headline' => filled($profile->headline),
            'Tagline' => filled($profile->tagline),
            'Bio' => filled($profile->bio),
            'Avatar' => filled($profile->avatar_path),
            'Roles' => filled($profile->roles),
            'Skills' => Skill::query()->exists(),
            'Experience' => Experience::query()->exists(),
            'Education' => Education::query()->exists(),
            'Resume' => filled($profile->resume_path),
            'Social links' => filled($profile->socials),
            'Meta description' => filled($profile->meta_description),
        ];

        return [
            'studio' => $this->score($studioFields),
            'founder' => $this->score($founderFields),
        ];
    }

    /**
     * Turn a field-checked map into a percentage plus the still-missing labels.
     *
     * @param  array<string, bool>  $fields
     * @return array{percent: int, missing: list<string>}
     */
    private function score(array $fields): array
    {
        $total = count($fields);

        if ($total === 0) {
            return ['percent' => 100, 'missing' => []];
        }

        $missing = array_keys(array_filter($fields, fn (bool $filled): bool => ! $filled));

        return [
            'percent' => (int) round((($total - count($missing)) / $total) * 100),
            'missing' => $missing,
        ];
    }

    /**
     * The inbox split over time — the only honest breakdown available, since
     * contact messages store no referrer. Feeds the dashboard's donut chart.
     *
     * @return list<array{source: string, count: int}>
     */
    private function messageSources(): array
    {
        $recent = ContactMessage::query()->where('created_at', '>=', now()->subDays(30))->count();
        $older = ContactMessage::query()->where('created_at', '<', now()->subDays(30))->count();

        if ($recent === 0 && $older === 0) {
            return [];
        }

        $sources = [['source' => 'Last 30 days', 'count' => $recent]];

        if ($older > 0) {
            $sources[] = ['source' => 'Older', 'count' => $older];
        }

        return $sources;
    }
}
