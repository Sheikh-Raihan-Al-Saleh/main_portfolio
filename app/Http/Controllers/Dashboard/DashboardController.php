<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the dashboard home page.
 */
class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    /**
     * Show the admin home: counts, charts, recent messages and projects.
     */
    public function __invoke(): Response
    {
        return Inertia::render('admin/dashboard/Index', [
            'stats' => $this->dashboardService->stats(),
            'activity' => $this->dashboardService->activity(),
            'completeness' => $this->dashboardService->completeness(),
            'recentMessages' => $this->dashboardService->recentMessages(),
            'recentProjects' => $this->dashboardService->recentProjects(),
            'messageSources' => $this->dashboardService->messageSources(),
        ]);
    }
}
