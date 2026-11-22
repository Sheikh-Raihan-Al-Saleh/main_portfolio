<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('admins receive the server upload limits', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $request = ini_parse_quantity((string) ini_get('post_max_size'));
    $file = ini_parse_quantity((string) ini_get('upload_max_filesize'));

    $expectedRequest = $request > 0 ? $request : null;
    $expectedFile = $file > 0 ? $file : null;

    if ($expectedRequest !== null && ($expectedFile === null || $expectedRequest < $expectedFile)) {
        $expectedFile = $expectedRequest;
    }

    $this->actingAs($admin)
        ->get('/admin/projects/create')
        ->assertInertia(fn (Assert $page) => $page
            ->where('uploadLimits.file', $expectedFile)
            ->where('uploadLimits.request', $expectedRequest),
        );
});

test('guests do not receive the upload limits', function () {
    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page->where('uploadLimits', null));
});

test('the uploads disk is rooted in the public directory', function () {
    expect(config('filesystems.disks.uploads.root'))->toBe(public_path('uploads'));
});
