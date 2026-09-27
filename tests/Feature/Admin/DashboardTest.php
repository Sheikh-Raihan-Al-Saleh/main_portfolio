<?php

use App\Models\ContactMessage;
use App\Models\Project;
use App\Models\User;

test('the dashboard exposes stats and recent activity', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    Project::factory()->companyWork()->create();
    Project::factory()->companyWork()->draft()->create();
    ContactMessage::factory()->create();
    ContactMessage::factory()->read()->create();

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/Dashboard')
            ->where('stats.projects', 2)
            ->where('stats.publishedProjects', 1)
            ->where('stats.messages', 2)
            ->where('stats.unreadMessages', 1)
            ->has('recentMessages', 2)
            ->has('recentProjects', 2)
        );
});

test('a non-admin cannot reach the dashboard', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->get('/admin')->assertForbidden();
});
