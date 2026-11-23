<?php

namespace Database\Seeders;

use App\Enums\LandingSectionType;
use App\Models\ContactMessage;
use App\Models\Project;
use App\Services\Education\EducationService;
use App\Services\Experience\ExperienceService;
use App\Services\Founder\FounderService;
use App\Services\LandingPage\LandingPageService;
use App\Services\LandingPage\LandingSectionService;
use App\Services\Media\MediaService;
use App\Services\Project\ProjectService;
use App\Services\Skill\SkillService;
use App\Services\Studio\StudioService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Realistic starter content, so the site looks finished on first run.
 *
 * Everything goes through the same services the admin panel uses, so seeded
 * data follows the same rules (slugs, ordering, technologies, highlights).
 * Each step skips itself when its content already exists, so the seeder is
 * safe to run again. Everything here is editable in the admin panel.
 */
class PortfolioSeeder extends Seeder
{
    public function __construct(
        private readonly StudioService $studios,
        private readonly FounderService $founders,
        private readonly ProjectService $projects,
        private readonly SkillService $skills,
        private readonly ExperienceService $experiences,
        private readonly EducationService $educations,
        private readonly LandingPageService $landingPages,
        private readonly LandingSectionService $sections,
    ) {}

    public function run(): void
    {
        $this->seedStudio();
        $this->seedFounder();
        $this->seedSkills();
        $this->seedProjects();
        $this->seedClients();
        $this->seedStudioHome();
        $this->seedLandingPage();
        $this->seedExperiences();
        $this->seedEducations();

        if (ContactMessage::query()->doesntExist()) {
            ContactMessage::factory()->count(3)->create();
            ContactMessage::factory()->read()->count(2)->create();
        }
    }

    private function seedStudio(): void
    {
        $studio = $this->studios->current();

        if ($studio->name !== 'Your Studio') {
            return;
        }

        $this->studios->update([
            'name' => 'Sheikh Nabil',
            'short_name' => 'SN Studio',
            'legal_name' => 'Sheikh Nabil Studio',
            'headline' => 'Product studio',
            'tagline' => 'We design, build and maintain web software for teams that need it to hold up under real load.',
            'bio' => 'Sheikh Nabil is a small software studio working with founders and product teams '
                ."who need web platforms built properly the first time.\n\n"
                .'We take on a handful of projects at a time so the people who scope the work are the '
                .'same people who build it. Everything ships with its repository, its deployment '
                .'pipeline and its documentation.',
            'mission' => 'Build software our clients can own outright, and keep it running without us.',
            'founded_year' => 2019,
            'is_accepting_projects' => true,
            'location' => 'Dhaka, Bangladesh',
            'public_email' => config('portfolio.public_identity.contact_email'),
            'website_url' => 'https://sheikhnabil.com',
            'hero_eyebrow' => 'Product studio',
            'hero_title' => 'Software that holds up under real load.',
            'hero_statement' => 'Web apps, platforms and internal systems for growing teams.',
            'primary_cta_label' => 'Start a project',
            'primary_cta_url' => '#contact',
            'secondary_cta_label' => 'See our work',
            'secondary_cta_url' => '#work',
            'socials' => [
                'github' => 'https://github.com/',
                'linkedin' => 'https://linkedin.com/',
                'website' => 'https://sheikhnabil.com',
            ],
            'meta_title' => 'Sheikh Nabil, Product Studio',
            'meta_description' => 'A software studio designing, building and maintaining web applications, platforms and internal systems for growing teams.',
        ]);

        // A placeholder logo, so a fresh install shows a brand right away.
        $studio->forceFill([
            'logo_path' => $this->putSvg('brand/logo.svg', $this->wordmarkSvg('SN', 'Sheikh Nabil')),
            'logo_mark_path' => $this->putSvg('brand/logo-mark.svg', $this->markSvg('SN')),
        ])->save();

        $this->studios->forgetBrand();
    }

    private function seedFounder(): void
    {
        $founder = $this->founders->current();

        if ($founder->name !== 'Your Name') {
            return;
        }

        $this->founders->update([
            'name' => config('portfolio.public_identity.founder_name'),
            'headline' => 'Software Engineer',
            'tagline' => 'I build fast, accessible web applications end to end.',
            'bio' => "I'm a full-stack software engineer with a focus on the web. I care about "
                ."shipping software that is fast, maintainable and genuinely pleasant to use.\n\n"
                .'Most of my work lives in the Laravel and TypeScript ecosystem, but I enjoy '
                .'picking up whatever tool the problem actually calls for.',
            'founder_message' => 'I started this studio so that the people I worked with as an individual '
                .'contractor could get the same thing: senior engineering, honest estimates, and software '
                ."that is still maintainable two years later.\n\n"
                .'We stay deliberately small. You talk to the people writing the code, and we hand over the '
                .'repository, the deployment scripts and the documentation when we are done.',
            'roles' => ['Software Engineer', 'Full-Stack Developer', 'Laravel & Vue Specialist'],
            'is_available_for_work' => true,
            'location' => 'Dhaka, Bangladesh',
            'public_email' => config('portfolio.public_identity.contact_email'),
            'hero_title' => "Hi, I'm {{name}}.",
            'hero_statement' => 'I build software for the web, from the schema to the last pixel.',
            'primary_cta_label' => 'View my work',
            'primary_cta_url' => '#projects',
            'secondary_cta_label' => 'Get in touch',
            'secondary_cta_url' => '#contact',
            'socials' => [
                'github' => 'https://github.com/',
                'linkedin' => 'https://linkedin.com/',
            ],
            'meta_title' => 'Software Engineer Portfolio',
            'meta_description' => 'Full-stack software engineer building fast, accessible web applications with Laravel, Vue and TypeScript.',
        ]);
    }

    private function seedSkills(): void
    {
        $founder = $this->founders->current();

        if ($founder->skills()->exists()) {
            return;
        }

        $categories = $founder->skillCategories()->pluck('id', 'slug');

        $skills = [
            ['PHP', 'language', 95, true],
            ['TypeScript', 'language', 90, true],
            ['JavaScript', 'language', 92, false],
            ['SQL', 'language', 85, false],
            ['Laravel', 'framework', 95, true],
            ['Inertia.js', 'framework', 88, false],
            ['Livewire', 'framework', 80, false],
            ['Vue 3', 'frontend', 92, true],
            ['React', 'frontend', 78, false],
            ['Tailwind CSS', 'frontend', 93, true],
            ['MySQL', 'database', 88, true],
            ['PostgreSQL', 'database', 80, false],
            ['Redis', 'database', 76, false],
            ['Docker', 'devops', 82, true],
            ['GitHub Actions', 'devops', 80, false],
            ['AWS', 'devops', 72, false],
            ['Git', 'tool', 94, false],
            ['Pest / PHPUnit', 'tool', 86, false],
            ['Figma', 'tool', 70, false],
        ];

        foreach ($skills as [$name, $category, $proficiency, $featured]) {
            $this->skills->create([
                'skill_category_id' => $categories[$category],
                'name' => $name,
                'proficiency' => $proficiency,
                'is_featured' => $featured,
            ]);
        }
    }

    private function seedProjects(): void
    {
        if (Project::query()->exists()) {
            return;
        }

        $projects = [
            [
                'owner' => 'studio',
                'title' => 'Ahad ERP',
                'summary' => 'A multi-tenant ERP handling inventory, deliveries, customer ledgers and daily collections for a distribution business.',
                'description' => "A production ERP built for a distribution company managing hundreds of daily deliveries.\n\n"
                    .'The system replaced a sprawl of spreadsheets with a single source of truth: real-time stock levels, '
                    ."customer ledgers that reconcile automatically, and a collections workflow that field staff use from their phones.\n\n"
                    .'The hardest part was the ledger: every transaction had to be auditable and reversible without ever '
                    .'leaving a balance inconsistent, which meant careful use of database transactions and an append-only journal.',
                'tech_stack' => ['Laravel', 'Vue', 'Inertia', 'MySQL', 'Tailwind CSS'],
                'role' => 'Lead Developer',
                'is_featured' => true,
            ],
            [
                'owner' => 'studio',
                'title' => 'Realtime Analytics Dashboard',
                'summary' => 'Streaming event analytics with sub-second chart updates over WebSockets, backed by a time-series rollup pipeline.',
                'description' => "An analytics product that ingests millions of events a day and renders them live.\n\n"
                    .'Raw events land in a queue, get rolled up into per-minute and per-hour buckets by a background worker, '
                    ."and are pushed to connected dashboards over WebSockets. Pre-aggregation keeps query times flat as the dataset grows.\n\n"
                    .'The frontend renders incremental updates without re-mounting charts, so the dashboard stays smooth under heavy event volume.',
                'tech_stack' => ['Laravel', 'Redis', 'WebSockets', 'TypeScript', 'Vue'],
                'role' => 'Full-Stack Developer',
                'is_featured' => true,
            ],
            [
                'owner' => 'studio',
                'title' => 'Headless Commerce API',
                'summary' => 'A REST and webhook API powering storefronts across web and mobile, with idempotent checkout and Stripe integration.',
                'description' => "A commerce backend designed to be consumed by several different storefronts.\n\n"
                    .'Checkout is fully idempotent, so a retried request can never double-charge a customer, and every state '
                    ."change emits a signed webhook that clients can subscribe to.\n\n"
                    .'Comprehensive test coverage around payments and inventory made it safe to deploy several times a day.',
                'tech_stack' => ['Laravel', 'PostgreSQL', 'Stripe', 'Docker', 'Pest'],
                'role' => 'Backend Engineer',
                'is_featured' => true,
            ],
            [
                'owner' => 'founder',
                'title' => 'Design System Component Library',
                'summary' => 'An accessible, themeable Vue component library with full keyboard support and dark mode built in.',
                'description' => "A shared component library that unified the UI across four internal products.\n\n"
                    .'Every component was built against WCAG 2.1 AA: real focus management, keyboard navigation, and '
                    ."screen-reader announcements rather than ARIA sprinkled on afterwards.\n\n"
                    .'Theming runs on CSS custom properties, so a product can rebrand the whole library by overriding a handful of tokens.',
                'tech_stack' => ['Vue', 'TypeScript', 'Tailwind CSS', 'Vite'],
                'role' => 'Frontend Engineer',
                'is_featured' => false,
            ],
            [
                'owner' => 'founder',
                'title' => 'CI Pipeline Orchestrator',
                'summary' => 'A self-hosted job runner that parallelises test suites across containers and reports back to GitHub.',
                'description' => "A tool that cut a 40-minute test suite down to under 6 minutes.\n\n"
                    .'It shards tests across ephemeral Docker containers, balances shards using historical timing data, '
                    ."and streams results back to GitHub checks as each shard finishes.\n\n"
                    .'Failed shards can be retried individually instead of re-running the entire suite.',
                'tech_stack' => ['Go', 'Docker', 'GitHub Actions', 'Redis'],
                'role' => 'Platform Engineer',
                'is_featured' => false,
            ],
            [
                'owner' => 'founder',
                'title' => 'Markdown Knowledge Base',
                'summary' => 'A git-backed documentation site with full-text search, versioned pages and instant client-side navigation.',
                'description' => "Documentation that lives in git but reads like a polished product site.\n\n"
                    .'Markdown files are compiled at deploy time into a search index, so full-text search runs entirely '
                    ."client-side with no search service to operate.\n\n"
                    .'Every page keeps its version history, and editors can preview changes before merging.',
                'tech_stack' => ['Laravel', 'Vue', 'Tailwind CSS', 'MySQL'],
                'role' => 'Full-Stack Developer',
                'is_featured' => false,
            ],
        ];

        foreach ($projects as $index => $project) {
            $this->projects->create([
                ...$project,
                'is_published' => true,
                'repo_url' => 'https://github.com/',
                'started_at' => now()->subMonths(($index + 1) * 5)->toDateString(),
                'completed_at' => now()->subMonths($index * 3)->toDateString(),
            ]);
        }
    }

    /**
     * The client logo strip. Logos are written as simple SVG wordmarks so the
     * strip shows real artwork on a fresh install.
     */
    private function seedClients(): void
    {
        $studio = $this->studios->current();

        if ($studio->clients()->exists()) {
            return;
        }

        $clients = [
            ['Northwind', 'Logistics', 'Fleet tracking platform for a regional carrier.', 'NW', '#2563eb'],
            ['Kitepay', 'Fintech', 'Merchant dashboard and reconciliation tooling.', 'KP', '#0d9488'],
            ['Verdant', 'AgriTech', 'IoT dashboard for distributed crop operations.', 'VD', '#65a30d'],
            ['Halcyon', 'Health', 'Patient scheduling rebuilt around real workflows.', 'HC', '#7c3aed'],
            ['Cobalt Labs', 'Developer tools', 'Usage analytics for a developer platform.', 'CB', '#ea580c'],
        ];

        foreach ($clients as $index => [$name, $industry, $summary, $mark, $ink]) {
            $logo = $this->putSvg('clients/'.$index.'-'.Str::slug($name).'.svg', $this->wordmarkSvg($mark, $name, $ink));

            $studio->clients()->create([
                'name' => $name,
                'industry' => $industry,
                'summary' => $summary,
                'website_url' => 'https://example.com',
                'logo_path' => $logo,
                'sort_order' => $index,
                'is_visible' => true,
            ]);
        }
    }

    /**
     * Starter blocks for the studio home page. Only blocks that read well
     * without uploaded media are seeded.
     */
    private function seedStudioHome(): void
    {
        $studio = $this->studios->current();

        if ($studio->sections()->exists()) {
            return;
        }

        $blocks = [
            [
                'type' => LandingSectionType::Stats->value,
                'heading' => 'By the numbers',
                'subheading' => 'A few figures that describe how the studio actually operates.',
                'data' => ['items' => [
                    ['value' => 40, 'suffix' => '+', 'label' => 'Products shipped'],
                    ['value' => 99.9, 'suffix' => '%', 'label' => 'Uptime across live systems'],
                    ['value' => 3, 'suffix' => '', 'label' => 'Engineers you talk to directly'],
                ]],
            ],
            [
                'type' => LandingSectionType::Faq->value,
                'heading' => 'Questions we get asked first',
                'subheading' => 'If you are wondering whether we are the right studio, these are the short answers.',
                'data' => ['items' => [
                    ['question' => 'How does a project start?', 'answer' => 'A short call, then a written scope with a fixed estimate. Nothing is built until you have agreed to what it will contain.'],
                    ['question' => 'Do we own the code?', 'answer' => 'Always. The repository, the deployment pipeline and the documentation are yours from the first commit.'],
                    ['question' => 'What happens after launch?', 'answer' => 'Every project has a support window, and most clients stay on a retainer for monitoring and iteration.'],
                ]],
            ],
        ];

        foreach ($blocks as $block) {
            $this->sections->create($studio, [...$block, 'is_visible' => true]);
        }
    }

    /**
     * A fully composed case-study page for the flagship project, exercising
     * the embed and video demo formats. Blocks that need uploads are seeded
     * hidden, so they appear once real screenshots are added in the admin.
     */
    private function seedLandingPage(): void
    {
        $project = Project::query()->where('is_featured', true)->orderBy('sort_order')->orderByDesc('id')->with('technologies:id,name')->first();

        if ($project === null || $project->landingPage()->exists()) {
            return;
        }

        $page = $this->landingPages->save($project, [
            'eyebrow' => 'Case study',
            'headline' => "{$project->title}: built to ship, built to last",
            'subheadline' => 'How a sprawl of spreadsheets became a single auditable system that field staff actually enjoy using.',
            'primary_cta_label' => 'See it live',
            'primary_cta_url' => 'https://example.com',
            'secondary_cta_label' => 'Start a project',
            'secondary_cta_url' => '/#contact',
            'accent_from' => '#1d4ed8',
            'accent_to' => '#f97316',
            'seo_description' => "A deep dive into how {$project->title} was designed and built.",
            'is_published' => true,
        ]);

        $blocks = [
            [
                'type' => LandingSectionType::Features->value,
                'eyebrow' => 'What it does',
                'heading' => 'Built around the work, not the database',
                'data' => ['items' => [
                    ['icon' => 'Zap', 'title' => 'Sub-second updates', 'text' => 'Live figures pushed over WebSockets, so nobody refreshes to find out what changed.'],
                    ['icon' => 'ShieldCheck', 'title' => 'Auditable by design', 'text' => 'An append-only journal means every balance can be traced back to the entry that moved it.'],
                    ['icon' => 'Smartphone', 'title' => 'Field-ready', 'text' => 'The collections flow was designed phone-first, because that is where it is actually used.'],
                ]],
            ],
            [
                'type' => LandingSectionType::Stats->value,
                'heading' => 'By the numbers',
                'data' => ['items' => [
                    ['value' => 99.9, 'suffix' => '%', 'label' => 'Uptime over 12 months'],
                    ['value' => 40, 'suffix' => 'ms', 'label' => 'Median API response'],
                    ['value' => 1200, 'suffix' => '+', 'label' => 'Deliveries tracked daily'],
                    ['value' => 6, 'suffix' => '', 'label' => 'Spreadsheets retired'],
                ]],
            ],
            [
                'type' => LandingSectionType::DemoVideo->value,
                'eyebrow' => 'Watch',
                'heading' => 'Two minutes, end to end',
                'data' => [
                    'provider' => 'youtube',
                    'video_url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
                    'caption' => 'A walkthrough of the delivery and collections flow.',
                    'autoplay' => false,
                ],
            ],
            [
                'type' => LandingSectionType::DemoEmbed->value,
                'eyebrow' => 'Try it',
                'heading' => 'The live demo',
                'subheading' => 'Loads the real application in a sandboxed frame when you ask it to.',
                'data' => ['url' => 'https://example.com', 'aspect' => '16/9', 'chrome' => 'browser', 'allow_fullscreen' => true],
            ],
            [
                'type' => LandingSectionType::Tech->value,
                'heading' => 'Built with',
                'data' => ['items' => $project->technologies->pluck('name')->all() ?: ['Laravel', 'Vue', 'MySQL']],
            ],
            [
                'type' => LandingSectionType::Faq->value,
                'heading' => 'Questions I get asked',
                'data' => ['items' => [
                    ['question' => 'Can this be adapted to another industry?', 'answer' => 'Yes. The ledger and inventory cores are domain-agnostic; the workflow layer is what changes.'],
                    ['question' => 'How long did it take?', 'answer' => 'Roughly five months to first production use, then continuous delivery from there.'],
                    ['question' => 'Do you hand over the source?', 'answer' => 'Always. Client work ships with the repository, deployment scripts and documentation.'],
                ]],
            ],
            [
                'type' => LandingSectionType::Cta->value,
                'eyebrow' => 'Next',
                'heading' => 'Got something similar in mind?',
                'subheading' => 'Tell us what is slowing your team down and we will tell you honestly whether software is the fix.',
                'data' => ['label' => 'Start a conversation', 'url' => '/#contact', 'note' => 'Usually replies within a day.'],
            ],
        ];

        foreach ($blocks as $block) {
            $this->sections->create($page, [...$block, 'is_visible' => true]);
        }
    }

    private function seedExperiences(): void
    {
        if ($this->founders->current()->experiences()->exists()) {
            return;
        }

        $experiences = [
            [
                'company' => 'Nexus Software',
                'role' => 'Senior Software Engineer',
                'employment_type' => 'Full-time',
                'location' => 'Remote',
                'start_date' => now()->subYears(2)->startOfMonth()->toDateString(),
                'end_date' => null,
                'description' => 'Lead engineer on the core platform team, owning architecture decisions and mentoring three mid-level developers.',
                'highlights' => [
                    'Cut median API response time by 62% by introducing query-level caching and fixing N+1 hotspots.',
                    'Designed and shipped a multi-tenant billing system processing six figures monthly.',
                    'Introduced a CI pipeline that took deploys from weekly to several times a day.',
                ],
            ],
            [
                'company' => 'Bright Labs',
                'role' => 'Full-Stack Developer',
                'employment_type' => 'Full-time',
                'location' => 'Dhaka, Bangladesh',
                'start_date' => now()->subYears(4)->startOfMonth()->toDateString(),
                'end_date' => now()->subYears(2)->startOfMonth()->toDateString(),
                'description' => 'Built and maintained client applications across the Laravel and Vue stack for startups and agencies.',
                'highlights' => [
                    'Delivered 11 client projects end to end, from discovery through production support.',
                    'Rebuilt the flagship product frontend as an Inertia SPA, halving time-to-interactive.',
                    'Established the team code review and testing standards still in use today.',
                ],
            ],
            [
                'company' => 'Freelance',
                'role' => 'Web Developer',
                'employment_type' => 'Contract',
                'location' => 'Remote',
                'start_date' => now()->subYears(6)->startOfMonth()->toDateString(),
                'end_date' => now()->subYears(4)->startOfMonth()->toDateString(),
                'description' => 'Worked directly with small businesses to design, build and host their web presence.',
                'highlights' => [
                    'Shipped 20+ marketing sites and custom CMS builds.',
                    'Handled hosting, DNS, backups and ongoing maintenance for a portfolio of clients.',
                ],
            ],
        ];

        foreach ($experiences as $experience) {
            $this->experiences->create($experience);
        }
    }

    private function seedEducations(): void
    {
        if ($this->founders->current()->educations()->exists()) {
            return;
        }

        $this->educations->create([
            'institution' => 'University of Dhaka',
            'degree' => 'BSc',
            'field_of_study' => 'Computer Science & Engineering',
            'start_date' => now()->subYears(10)->startOfYear()->toDateString(),
            'end_date' => now()->subYears(6)->startOfYear()->toDateString(),
            'grade' => '3.82 / 4.00',
            'description' => 'Focused on distributed systems and databases. Final year project was a fault-tolerant job scheduler.',
        ]);
    }

    private function putSvg(string $path, string $svg): string
    {
        Storage::disk(MediaService::DISK)->put($path, $svg);

        return $path;
    }

    /**
     * A monogram tile beside a name, used for placeholder logos.
     */
    private function wordmarkSvg(string $mark, string $name, string $ink = '#111111'): string
    {
        $width = 64 + (int) ceil(mb_strlen($name) * 12.5);

        return <<<SVG
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$width} 64" role="img" aria-label="{$name}">
                <rect x="4" y="8" width="48" height="48" rx="10" fill="{$ink}"/>
                <text x="28" y="33" text-anchor="middle" dominant-baseline="middle" font-family="ui-sans-serif, system-ui, sans-serif" font-size="18" font-weight="700" fill="#ffffff">{$mark}</text>
                <text x="64" y="33" dominant-baseline="middle" font-family="ui-sans-serif, system-ui, sans-serif" font-size="22" font-weight="700" fill="#111111">{$name}</text>
            </svg>
            SVG;
    }

    /**
     * A square monogram, used as the placeholder logo mark and favicon.
     */
    private function markSvg(string $mark): string
    {
        return <<<SVG
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" role="img" aria-label="{$mark}">
                <rect width="64" height="64" rx="14" fill="#111111"/>
                <text x="32" y="34" text-anchor="middle" dominant-baseline="middle" font-family="ui-sans-serif, system-ui, sans-serif" font-size="24" font-weight="700" fill="#ffffff">{$mark}</text>
            </svg>
            SVG;
    }
}
