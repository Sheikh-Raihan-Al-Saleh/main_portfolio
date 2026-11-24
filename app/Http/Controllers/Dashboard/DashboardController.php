<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Founder;
use App\Models\Project;
use App\Models\Studio;
use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the dashboard home page - counts, charts, recent messages and projects.
 */
class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboard) {}

    /**
     * Show the admin home.
     */
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => $this->dashboard->stats(),
            'activity' => $this->dashboard->activity(),
            'completeness' => [
                'studio' => $this->dashboard->studioCompleteness(Studio::current()),
                'founder' => $this->dashboard->founderCompleteness(Founder::current()),
            ],
            'recentMessages' => ContactMessage::latest()->limit(5)->get(['id', 'name', 'email', 'subject', 'read_at', 'created_at']),
            'recentProjects' => Project::ordered()->limit(5)->get(['id', 'title', 'slug', 'is_published', 'is_featured', 'updated_at']),
            'messageSources' => $this->dashboard->messageSources(),
        ]);
    }
}
