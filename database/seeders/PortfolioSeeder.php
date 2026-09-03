<?php

namespace Database\Seeders;

use App\Enums\LandingSectionType;
use App\Models\ContactMessage;
use App\Models\Education;
use App\Models\Experience;
use App\Models\LandingSection;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ProjectLandingPage;
use App\Models\Skill;
use Illuminate\Database\Seeder;

/**
 * Seeds realistic placeholder content so the site looks finished on first run.
 * Everything here is editable from the admin panel.
 */
class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedProfile();
        $this->seedSkills();
        $this->seedProjects();
        $this->seedLandingPage();
        $this->seedExperiences();
        $this->seedEducations();

        if (ContactMessage::query()->doesntExist()) {
            ContactMessage::factory()->count(3)->create();
            ContactMessage::factory()->read()->count(2)->create();
        }
    }

    private function seedProfile(): void
    {
        Profile::current()->update([
            'name' => config('portfolio.admin.name'),
            'headline' => 'Software Engineer',
            'tagline' => 'I build fast, accessible web applications end to end.',
            'bio' => "I'm a full-stack software engineer with a focus on the web. I care about "
                ."shipping software that is fast, maintainable and genuinely pleasant to use.\n\n"
                .'Most of my work lives in the Laravel and TypeScript ecosystem, but I enjoy '
                .'picking up whatever tool the problem actually calls for. Outside of client work '
                .'I write about architecture, contribute to open source, and mentor junior developers.',
            'location' => 'Dhaka, Bangladesh',
            'public_email' => config('portfolio.admin.email'),
            'available_for_work' => true,
            'roles' => ['Software Engineer', 'Full-Stack Developer', 'Laravel & Vue Specialist'],
            'socials' => [
                'github' => 'https://github.com/',
                'linkedin' => 'https://linkedin.com/in/',
                'x' => null,
                'website' => null,
            ],
            'meta_title' => 'Software Engineer Portfolio',
            'meta_description' => 'Full-stack software engineer building fast, accessible web applications with Laravel, Vue and TypeScript.',
        ]);
    }

    private function seedSkills(): void
    {
        if (Skill::query()->exists()) {
            return;
        }

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

        foreach ($skills as $index => [$name, $category, $proficiency, $featured]) {
            Skill::create([
                'name' => $name,
                'category' => $category,
                'proficiency' => $proficiency,
                'is_featured' => $featured,
                'sort_order' => $index,
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
                'title' => 'Ahad ERP',
                'summary' => 'A multi-tenant ERP handling inventory, deliveries, customer ledgers and daily collections for a distribution business.',
                'description' => "A production ERP built for a distribution company managing hundreds of daily deliveries.\n\n"
                    .'The system replaced a sprawl of spreadsheets with a single source of truth: real-time stock levels, '
                    ."customer ledgers that reconcile automatically, and a collections workflow that field staff use from their phones.\n\n"
                    .'The hardest part was the ledger — every transaction had to be auditable and reversible without ever '
                    .'leaving a balance inconsistent, which meant careful use of database transactions and an append-only journal.',
                'tech_stack' => ['Laravel', 'Vue', 'Inertia', 'MySQL', 'Tailwind CSS'],
                'role' => 'Lead Developer',
                'is_featured' => true,
            ],
            [
                'title' => 'Realtime Analytics Dashboard',
                'summary' => 'Streaming event analytics with sub-second chart updates over WebSockets, backed by a time-series rollup pipeline.',
                'description' => "An analytics product that ingests millions of events a day and renders them live.\n\n"
                    .'Raw events land in a queue, get rolled up into per-minute and per-hour buckets by a background worker, '
                    ."and are pushed to connected dashboards over WebSockets. Pre-aggregation keeps query times flat as the dataset grows.\n\n"
                    .'The frontend renders incremental updates without re-mounting charts, so the dashboard stays smooth even under heavy event volume.',
                'tech_stack' => ['Laravel', 'Redis', 'WebSockets', 'TypeScript', 'Vue'],
                'role' => 'Full-Stack Developer',
                'is_featured' => true,
            ],
            [
                'title' => 'Headless Commerce API',
                'summary' => 'A REST + webhook API powering storefronts across web and mobile, with idempotent checkout and Stripe integration.',
                'description' => "A commerce backend designed to be consumed by several different storefronts.\n\n"
                    .'Checkout is fully idempotent — a retried request can never double-charge a customer — and every state '
                    ."change emits a signed webhook that clients can subscribe to.\n\n"
                    .'Comprehensive test coverage around payments and inventory made it safe to deploy multiple times a day.',
                'tech_stack' => ['Laravel', 'PostgreSQL', 'Stripe', 'Docker', 'Pest'],
                'role' => 'Backend Engineer',
                'is_featured' => true,
            ],
            [
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

        // Slugs are set explicitly: DatabaseSeeder mutes model events, so the
        // Project::saving() hook that normally derives them does not fire here.
        foreach ($projects as $index => $project) {
            Project::create([
                ...$project,
                'slug' => Project::uniqueSlug($project['title']),
                'is_published' => true,
                'sort_order' => $index,
                'repo_url' => 'https://github.com/',
                'started_at' => now()->subMonths(($index + 1) * 5),
                'completed_at' => now()->subMonths($index * 3),
            ]);
        }
    }

    /**
     * A fully-composed marketing landing page for the flagship project, so the
     * feature is visible on a fresh install. Exercises all three demo formats.
     *
     * Media paths are deliberately omitted: the walkthrough and gallery blocks
     * need real uploads, which the admin builder provides. The sections that
     * work without files are the ones seeded visible.
     */
    private function seedLandingPage(): void
    {
        if (ProjectLandingPage::query()->exists()) {
            return;
        }

        $project = Project::query()->where('is_featured', true)->first()
            ?? Project::query()->first();

        if ($project === null) {
            return;
        }

        $landingPage = $project->landingPage()->create([
            'eyebrow' => 'Case study',
            'headline' => "{$project->title}: built to ship, built to last",
            'subheadline' => 'How a sprawl of spreadsheets became a single auditable system '
                .'that field staff actually enjoy using.',
            'primary_cta_label' => 'See it live',
            'primary_cta_url' => 'https://example.com',
            'secondary_cta_label' => 'Start a project',
            'secondary_cta_url' => '/#contact',
            'accent_from' => '#6366f1',
            'accent_to' => '#a855f7',
            'seo_description' => "A deep dive into how {$project->title} was designed and built.",
            'is_published' => true,
        ]);

        $sections = [
            [
                'type' => LandingSectionType::Features,
                'eyebrow' => 'What it does',
                'heading' => 'Built around the work, not the database',
                'data' => ['items' => [
                    ['icon' => 'Zap', 'title' => 'Sub-second updates', 'text' => 'Live figures pushed over WebSockets, so nobody refreshes to find out what changed.'],
                    ['icon' => 'ShieldCheck', 'title' => 'Auditable by design', 'text' => 'An append-only journal means every balance can be traced back to the entry that moved it.'],
                    ['icon' => 'Smartphone', 'title' => 'Field-ready', 'text' => 'The collections flow was designed phone-first, because that is where it is actually used.'],
                ]],
            ],
            [
                'type' => LandingSectionType::Stats,
                'heading' => 'By the numbers',
                'data' => ['items' => [
                    ['value' => 99.9, 'suffix' => '%', 'label' => 'Uptime over 12 months'],
                    ['value' => 40, 'suffix' => 'ms', 'label' => 'Median API response'],
                    ['value' => 1200, 'suffix' => '+', 'label' => 'Deliveries tracked daily'],
                    ['value' => 6, 'suffix' => '', 'label' => 'Spreadsheets retired'],
                ]],
            ],
            [
                'type' => LandingSectionType::DemoEmbed,
                'eyebrow' => 'Try it',
                'heading' => 'The live demo',
                'subheading' => 'Loads the real application in a sandboxed frame when you ask it to.',
                'data' => [
                    'url' => 'https://example.com',
                    'aspect' => '16/9',
                    'chrome' => 'browser',
                    'allow_fullscreen' => true,
                ],
            ],
            [
                'type' => LandingSectionType::DemoVideo,
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
                // Hidden until real screenshots are uploaded in the admin.
                'type' => LandingSectionType::DemoWalkthrough,
                'eyebrow' => 'Step by step',
                'heading' => 'How a delivery moves through the system',
                'is_visible' => false,
                'data' => [
                    'frame' => 'browser',
                    'steps' => [
                        ['image_path' => '', 'title' => 'Raise the order', 'caption' => 'Stock is reserved the moment the order is confirmed.'],
                        ['image_path' => '', 'title' => 'Dispatch', 'caption' => 'The driver picks up a manifest generated for their route.'],
                        ['image_path' => '', 'title' => 'Collect and reconcile', 'caption' => 'Payment is recorded on the doorstep and the ledger settles itself.'],
                    ],
                ],
            ],
            [
                'type' => LandingSectionType::Tech,
                'heading' => 'Built with',
                'data' => ['items' => $project->tech_stack ?? ['Laravel', 'Vue', 'MySQL']],
            ],
            [
                'type' => LandingSectionType::Faq,
                'heading' => 'Questions I get asked',
                'data' => ['items' => [
                    ['question' => 'Can this be adapted to another industry?', 'answer' => 'Yes — the ledger and inventory cores are domain-agnostic; the workflow layer is what changes.'],
                    ['question' => 'How long did it take?', 'answer' => 'Roughly five months to first production use, then continuous delivery from there.'],
                    ['question' => 'Do you hand over the source?', 'answer' => 'Always. Client work ships with the repository, deployment scripts and documentation.'],
                ]],
            ],
            [
                'type' => LandingSectionType::Cta,
                'eyebrow' => 'Next',
                'heading' => 'Got something similar in mind?',
                'subheading' => 'Tell me what is slowing your team down and I will tell you honestly whether software is the fix.',
                'data' => [
                    'label' => 'Start a conversation',
                    'url' => '/#contact',
                    'note' => 'Usually replies within a day.',
                ],
            ],
        ];

        foreach ($sections as $index => $section) {
            LandingSection::create([
                ...$section,
                'landing_page_id' => $landingPage->id,
                'sort_order' => $index,
                'is_visible' => $section['is_visible'] ?? true,
            ]);
        }
    }

    private function seedExperiences(): void
    {
        if (Experience::query()->exists()) {
            return;
        }

        $experiences = [
            [
                'company' => 'Nexus Software',
                'role' => 'Senior Software Engineer',
                'employment_type' => 'Full-time',
                'location' => 'Remote',
                'start_date' => now()->subYears(2)->startOfMonth(),
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
                'start_date' => now()->subYears(4)->startOfMonth(),
                'end_date' => now()->subYears(2)->startOfMonth(),
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
                'start_date' => now()->subYears(6)->startOfMonth(),
                'end_date' => now()->subYears(4)->startOfMonth(),
                'description' => 'Worked directly with small businesses to design, build and host their web presence.',
                'highlights' => [
                    'Shipped 20+ marketing sites and custom CMS builds.',
                    'Handled hosting, DNS, backups and ongoing maintenance for a portfolio of clients.',
                ],
            ],
        ];

        foreach ($experiences as $index => $experience) {
            Experience::create([...$experience, 'sort_order' => $index]);
        }
    }

    private function seedEducations(): void
    {
        if (Education::query()->exists()) {
            return;
        }

        Education::create([
            'institution' => 'University of Dhaka',
            'degree' => 'BSc',
            'field_of_study' => 'Computer Science & Engineering',
            'start_date' => now()->subYears(10)->startOfYear(),
            'end_date' => now()->subYears(6)->startOfYear(),
            'grade' => '3.82 / 4.00',
            'description' => 'Focused on distributed systems and databases. Final year project was a fault-tolerant job scheduler.',
            'sort_order' => 0,
        ]);
    }
}
