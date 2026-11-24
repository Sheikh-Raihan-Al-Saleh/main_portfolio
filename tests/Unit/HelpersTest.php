<?php

use App\Rules\SafeSvg;
use App\Services\FileService;
use App\Support\ContentMerger;
use Tests\TestCase;

uses(TestCase::class);

test('file urls are relative so they work on any host', function () {
    expect(FileService::url('projects/cover.jpg'))->toBe('/uploads/projects/cover.jpg');
});

test('a blank path has no url', function () {
    expect(FileService::url(null))->toBeNull()
        ->and(FileService::url(''))->toBeNull();
});

test('stored copy is merged over the defaults, lists replaced whole', function () {
    $defaults = ['hero' => ['title' => 'Default', 'note' => 'Kept'], 'items' => ['a', 'b', 'c']];
    $stored = ['hero' => ['title' => 'Edited'], 'items' => ['x']];

    expect(ContentMerger::merge($defaults, $stored))->toBe([
        'hero' => ['title' => 'Edited', 'note' => 'Kept'],
        'items' => ['x'],
    ])->and(ContentMerger::merge($defaults, null))->toBe($defaults);
});

test('plain svg artwork is accepted', function () {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="g"/></defs><rect fill="url(#g)" width="10" height="10"/></svg>';

    expect(SafeSvg::isSafe($svg))->toBeTrue();
});

test('svgs with scripts, handlers, outside links or a doctype are refused', function (string $svg) {
    expect(SafeSvg::isSafe($svg))->toBeFalse();
})->with([
    'script element' => '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>',
    'event handler' => '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"><rect/></svg>',
    'outside link' => '<svg xmlns="http://www.w3.org/2000/svg"><use href="https://evil.example/x.svg#a"/></svg>',
    'javascript url' => '<svg xmlns="http://www.w3.org/2000/svg"><a href="javascript:alert(1)"><rect/></a></svg>',
    'doctype' => '<!DOCTYPE svg [<!ENTITY x "y">]><svg xmlns="http://www.w3.org/2000/svg"/>',
    'not svg' => '<html/>',
]);
