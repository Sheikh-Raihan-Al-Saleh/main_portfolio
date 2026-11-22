<?php

namespace App\Support\Defaults;

/**
 * Starting values for a new studio row, so a fresh install renders a
 * finished-looking site before anything is edited in the admin panel.
 */
class StudioDefaults
{
    /**
     * @return array<string, mixed>
     */
    public static function attributes(): array
    {
        return [
            'name' => 'Your Studio',
            'slug' => 'studio',
            'hero_eyebrow' => 'Product studio',
            'hero_title' => '{{name}} builds software that ships.',
            'hero_statement' => 'Web apps, platforms and systems for growing teams.',
            'primary_cta_label' => 'Start a project',
            'primary_cta_url' => '#contact',
            'secondary_cta_label' => 'See our work',
            'secondary_cta_url' => '#work',
            'footer' => self::footer(),
        ];
    }

    /**
     * Footer columns. Links may use {{placeholder}} tokens ({{year}}, {{name}},
     * {{email}}, {{location}}, {{linkedin}}, {{whatsapp}}...), which the
     * frontend resolves when the footer renders.
     *
     * @return array<string, mixed>
     */
    public static function footer(): array
    {
        return [
            'status_text' => 'Accepting new projects',
            'status_text_unavailable' => 'Currently unavailable',
            'copyright' => '© {{year}} {{name}}. All rights reserved.',
            'back_to_top' => 'Back to top',
            'columns' => [
                [
                    'title' => 'Studio',
                    'links' => [
                        ['label' => 'Home', 'url' => '/'],
                        ['label' => 'Work', 'url' => '/projects'],
                        ['label' => 'About', 'url' => '/about'],
                        ['label' => 'Contact', 'url' => '#contact'],
                    ],
                ],
                [
                    'title' => 'Services',
                    'links' => [
                        ['label' => 'Web applications', 'url' => '#capabilities'],
                        ['label' => 'APIs & integrations', 'url' => '#capabilities'],
                        ['label' => 'SaaS platforms', 'url' => '#capabilities'],
                        ['label' => 'Performance audits', 'url' => '#capabilities'],
                    ],
                ],
                [
                    'title' => 'Resources',
                    'links' => [
                        ['label' => 'Case studies', 'url' => '/projects'],
                        ['label' => 'Founder portfolio', 'url' => '/founder'],
                        ['label' => 'LinkedIn', 'url' => '{{linkedin}}'],
                    ],
                ],
                [
                    'title' => 'Contact',
                    'links' => [
                        ['label' => 'Email', 'url' => 'mailto:{{email}}'],
                        ['label' => 'WhatsApp', 'url' => '{{whatsapp}}'],
                        ['label' => '{{location}}', 'url' => ''],
                    ],
                ],
            ],
            'legal_links' => [
                ['label' => 'Privacy Policy', 'url' => ''],
                ['label' => 'Terms of Service', 'url' => ''],
            ],
        ];
    }
}
