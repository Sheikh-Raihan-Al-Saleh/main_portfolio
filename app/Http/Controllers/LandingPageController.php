<?php

namespace App\Http\Controllers;

use App\Enums\LandingSectionType;
use App\Models\LandingSection;
use App\Models\Project;
use Illuminate\Support\Str;
use Inertia\Response;

class LandingPageController extends Controller
{
    /**
     * A project's marketing landing page.
     *
     * Both the project and its landing page must be published: an unpublished
     * case study is a draft, and drafts 404 rather than redirecting, so their
     * existence is not disclosed.
     */
    public function show(Project $project): Response
    {
        abort_unless($project->is_published, 404);

        $landingPage = $project->landingPage()->published()->first();

        abort_if($landingPage === null, 404);

        $sections = $landingPage->sections()
            ->visible()
            ->ordered()
            ->get()
            ->map(fn (LandingSection $section): array => $this->presentSection($section));

        return inertia('public/landing/Show', [
            'project' => $project,
            'landingPage' => $landingPage,
            'sections' => $sections,
        ]);
    }

    /**
     * Shape one section for the client.
     *
     * Markdown is rendered here rather than in the browser so no raw HTML is
     * shipped to `v-html` unsanitised.
     *
     * @return array<string, mixed>
     */
    private function presentSection(LandingSection $section): array
    {
        $payload = $section->only([
            'id', 'eyebrow', 'heading', 'subheading', 'body', 'data', 'is_visible',
        ]);

        $payload['type'] = $section->type->value;

        if ($section->type === LandingSectionType::RichText) {
            $payload['body_html'] = Str::markdown(
                (string) data_get($section->data, 'markdown', ''),
                [
                    'html_input' => 'strip',
                    'allow_unsafe_links' => false,
                ],
            );
        }

        return $payload;
    }
}
