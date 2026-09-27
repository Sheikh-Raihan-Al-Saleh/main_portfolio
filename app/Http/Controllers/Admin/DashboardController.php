<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ContactMessage;
use App\Models\Education;
use App\Models\Experience;
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
            ],
            'recentMessages' => ContactMessage::query()
                ->latest()
                ->take(5)
                ->get(['id', 'name', 'email', 'subject', 'read_at', 'created_at']),
            'recentProjects' => Project::query()
                ->ordered()
                ->take(5)
                ->get(['id', 'title', 'slug', 'is_published', 'is_featured', 'updated_at']),
            'profile' => Profile::current(),
            'company' => $company,
        ]);
    }
}
