<?php

use App\Services\Media\MediaService;
use Tests\TestCase;

uses(TestCase::class);

test('media urls are relative so they work on any host', function () {
    expect(MediaService::url('projects/cover.jpg'))->toBe('/uploads/projects/cover.jpg');
});

test('a blank path has no url', function () {
    expect(MediaService::url(null))->toBeNull()
        ->and(MediaService::url(''))->toBeNull();
});
