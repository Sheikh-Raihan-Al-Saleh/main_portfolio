<?php

namespace App\Support;

/**
 * Default copy for the studio's fixed story sections.
 *
 * The home page's numbered chapters (what we build, engineering, why us, the
 * closing CTA) render from `companies.content`, but a fresh install has not
 * been edited yet — so these defaults are what the page reads, and what the
 * admin editor pre-fills. They deliberately match the copy that used to live
 * hardcoded in the Vue sections, so no page changes on deploy.
 */
class StudioContent
{
    /**
     * The full default payload, shaped exactly as the admin editor and the
     * public sections read it.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'ticker' => [
                'items' => [
                    'Available for new projects',
                    'Web apps',
                    'SaaS platforms',
                    'APIs & integrations',
                    'AI-powered features',
                ],
            ],

            'capabilities' => [
                'eyebrow' => 'What we build',
                'title' => 'Software, end to end',
                'highlight' => 'end to end',
                'description' => 'One team, one stack, from the first schema to the last deploy. Most of what we build falls into the shapes below — and anything that does not, we build anyway.',
                'kicker' => 'Custom software, from zero',
                'stack_heading' => 'Every product is the same four layers. We build all four.',
                'stack_intro' => 'Nothing is outsourced to a framework you will not be able to hire for, and nothing is assembled from a template that quietly becomes unmaintainable in year two.',
                'footer_note' => 'not on the list?',
                'footer_link' => 'it usually still is — tell us what you need',
                'items' => [
                    [
                        'icon' => 'Blocks',
                        'title' => 'Web applications',
                        'summary' => 'Dashboards, portals and internal tools that people open every day and never think about.',
                        'tech' => ['Vue', 'Inertia', 'Laravel', 'Tailwind'],
                    ],
                    [
                        'icon' => 'Layers',
                        'title' => 'SaaS platforms',
                        'summary' => 'Multi-tenant products with billing, roles, onboarding and the unglamorous parts done properly.',
                        'tech' => ['Multi-tenancy', 'Stripe', 'Queues', 'Webhooks'],
                    ],
                    [
                        'icon' => 'Boxes',
                        'title' => 'Business systems',
                        'summary' => 'Operations, inventory, invoicing and reporting — the software a business is actually run on.',
                        'tech' => ['Workflows', 'Reporting', 'Imports', 'Audit trails'],
                    ],
                    [
                        'icon' => 'Network',
                        'title' => 'APIs, backends & integrations',
                        'summary' => 'Well-documented services that connect the tools you already pay for instead of replacing them.',
                        'tech' => ['REST', 'Webhooks', 'OAuth', 'Queues'],
                    ],
                    [
                        'icon' => 'Bot',
                        'title' => 'AI-powered features',
                        'summary' => 'Assistants, extraction and search built into the product you already have, with the guardrails to match.',
                        'tech' => ['Embeddings', 'RAG', 'Tool use', 'Evaluation'],
                    ],
                    [
                        'icon' => 'Zap',
                        'title' => 'Automation',
                        'summary' => 'The repetitive work between your systems, removed: scheduled jobs, syncs and notifications.',
                        'tech' => ['Schedulers', 'Pipelines', 'ETL', 'Monitoring'],
                    ],
                ],
                'stack' => [
                    ['label' => 'Infrastructure', 'meta' => 'Cloud · CI/CD · monitoring'],
                    ['label' => 'Data', 'meta' => 'Postgres · Redis · backups'],
                    ['label' => 'Services', 'meta' => 'Domain logic · queues · workers'],
                    ['label' => 'Interface', 'meta' => 'Web app · API · admin'],
                ],
            ],

            'engineering' => [
                'eyebrow' => 'Engineering',
                'title' => 'How the pieces fit together',
                'highlight' => 'fit together',
                'description' => 'Nothing here is exotic. It is the same five stages on every project, chosen so that the person maintaining it in three years can find their way around.',
                'stages' => [
                    [
                        'icon' => 'MonitorSmartphone',
                        'label' => 'Interface',
                        'tagline' => 'What people use',
                        'detail' => 'Server-rendered pages that hydrate into something fast, accessible and pleasant to use on a phone in a warehouse. Design systems, responsive layout, and the small interactions that make software feel considered rather than tolerated.',
                        'tech' => ['Vue 3', 'Inertia', 'TypeScript', 'Tailwind'],
                    ],
                    [
                        'icon' => 'Network',
                        'label' => 'API',
                        'tagline' => 'The contract',
                        'detail' => 'Validated, documented endpoints with predictable errors, sane pagination, versioning and rate limits. The explicit boundary between the product and everything behind it — including your other systems.',
                        'tech' => ['REST', 'Validation', 'Auth', 'Webhooks'],
                    ],
                    [
                        'icon' => 'Boxes',
                        'label' => 'Services',
                        'tagline' => 'Where the work happens',
                        'detail' => 'Domain logic, background jobs and third-party integrations kept out of the controller layer, so each piece can be tested on its own, queued, retried and reasoned about six months from now.',
                        'tech' => ['Domain logic', 'Queues', 'Jobs', 'Events'],
                    ],
                    [
                        'icon' => 'Database',
                        'label' => 'Data',
                        'tagline' => 'What it is built on',
                        'detail' => 'Schemas designed for the questions you will ask later. Indexes that matter, migrations that roll forward safely, and backups that have actually been restored at least once.',
                        'tech' => ['PostgreSQL', 'Redis', 'Migrations', 'Backups'],
                    ],
                    [
                        'icon' => 'Cloud',
                        'label' => 'Infrastructure',
                        'tagline' => 'Where it runs',
                        'detail' => 'Repeatable deploys, a staging environment that matches production, and monitoring that tells you something broke before a customer has to. Infrastructure as config, not as tribal knowledge.',
                        'tech' => ['Cloud', 'CI/CD', 'Monitoring', 'Logs'],
                    ],
                ],
            ],

            'value' => [
                'eyebrow' => 'Why work with us',
                'title' => 'Small team, serious delivery',
                'highlight' => 'serious delivery',
                'description' => 'The studio is deliberately small, which means fewer handovers, less overhead and a shorter path from idea to production.',
                'guarantees' => [
                    'Scope agreed in writing',
                    'You own the code',
                    'A working demo every week',
                ],
                'items' => [
                    [
                        'icon' => 'Blocks',
                        'title' => 'Built for your business',
                        'summary' => 'No template with your logo on it. The models, screens and edge cases come from how your team actually works.',
                    ],
                    [
                        'icon' => 'Layers',
                        'title' => 'Architecture that scales',
                        'summary' => 'The shape of the system is decided up front, so growth is a config change rather than a rewrite.',
                    ],
                    [
                        'icon' => 'ShieldCheck',
                        'title' => 'Clean, reviewed engineering',
                        'summary' => 'Typed, tested, reviewed. Every deploy passes the same checks on day 300 as it did on day one.',
                    ],
                    [
                        'icon' => 'GitBranch',
                        'title' => 'Maintainable by design',
                        'summary' => 'Documented decisions, conventional structure, no clever tricks. Your next developer should be productive in a week.',
                    ],
                    [
                        'icon' => 'Handshake',
                        'title' => 'Direct communication',
                        'summary' => 'You talk to the engineer building it. Questions get answers, not account managers.',
                    ],
                    [
                        'icon' => 'Route',
                        'title' => 'Focused on the outcome',
                        'summary' => 'Software is a means to an end. We will tell you when a spreadsheet is the better answer.',
                    ],
                ],
            ],

            'work' => [
                'eyebrow' => 'Selected work',
                'title' => 'Things we have built',
                'highlight' => 'built',
                'description' => 'A selection of client and studio work. Each one links to a short case study covering the problem, the approach and what shipped.',
                'featured_label' => 'Featured case study',
                'case_study_label' => 'read the case study',
                'live_label' => 'live site',
                'source_label' => 'source',
                'archive_label' => 'view full archive',
                'empty_text' => 'No published work yet.',
            ],

            'founder_work' => [
                'eyebrow' => "The founder's own work",
                'title' => 'Built outside client',
                'highlight' => 'client',
                'description' => 'Side projects and open source the founder builds in his own time. His full portfolio, resume and project history live on his own site.',
                'archive_label' => 'view full portfolio',
                'empty_text' => 'No personal projects published yet.',
            ],

            'about' => [
                'eyebrow' => 'About us',
                'title' => 'A small studio, deliberately',
                'highlight' => 'small',
                'mission_label' => 'Our mission',
                'more_label' => 'more about the studio',
            ],

            'founder_bridge' => [
                'studio_label' => 'The studio',
                'studio_heading' => '{{name}} — the team',
                'studio_text' => 'Scoping, design, delivery and everything that keeps a product running after launch. This is who you contract with.',
                'studio_link' => 'how the studio works',
                'founder_label' => 'The founder',
                'founder_heading' => '{{name}}',
                'founder_text' => '{{role}} — the person who writes the code and answers your questions. His own projects, skills and history live on his own page.',
                'founder_link' => "see the founder's portfolio",
            ],

            'cta' => [
                'eyebrow' => 'Next step',
                'title' => "Have a software idea? Let's build it.",
                'highlight' => "Let's build it.",
                'description' => 'Tell us the problem in a few lines. You will get a written reply with an honest read on scope, timeline and cost — whether or not that ends in a project.',
                'button_label' => 'Start a Conversation',
                'email_label' => 'email instead',
                'status_available' => 'Accepting new projects',
                'status_unavailable' => 'Currently booked — get in touch for the next slot',
            ],

            'clients' => [
                'eyebrow' => 'Trusted by',
                'title' => 'Teams we build for',
                'highlight' => 'for',
                'description' => 'The studios, founders and product teams who trusted a small team with their platform.',
            ],

            'contact' => [
                'eyebrow' => 'Start a project',
                'title' => 'Tell us what needs building',
                'highlight' => 'building',
                'description' => 'Describe the problem and we will tell you honestly whether software is the fix, what it would take, and what it would cost.',
                'email_heading' => 'Prefer email?',
                'email_note' => 'Every enquiry gets a written reply with a rough scope and an honest estimate. No sales calls.',
                'form_placeholder' => 'A few lines about your business and what is not working…',
                'form_submit' => 'Send enquiry',
                'form_reply_note' => 'usually replies within a day',
            ],
        ];
    }

    /**
     * Merge stored content over the defaults, so a payload missing a key
     * (new sections added later, older rows) still renders completely.
     *
     * @param  array<string, mixed>|null  $content
     * @return array<string, mixed>
     */
    public static function merge(?array $content): array
    {
        $defaults = self::defaults();

        if ($content === null || $content === []) {
            return $defaults;
        }

        return self::mergeRecursive($defaults, $content);
    }

    /**
     * Union-merge arrays at every level, stored values winning. Scalar and
     * list values from storage replace the default wholesale — a list is
     * never element-merged, or removed items would resurrect.
     *
     * @param  array<string, mixed>  $defaults
     * @param  array<string, mixed>  $stored
     * @return array<string, mixed>
     */
    private static function mergeRecursive(array $defaults, array $stored): array
    {
        $merged = $defaults;

        foreach ($stored as $key => $value) {
            if (is_array($value) && isset($defaults[$key]) && is_array($defaults[$key]) && ! array_is_list($value)) {
                $merged[$key] = self::mergeRecursive($defaults[$key], $value);

                continue;
            }

            $merged[$key] = $value;
        }

        return $merged;
    }
}
