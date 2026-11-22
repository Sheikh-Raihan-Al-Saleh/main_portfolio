<?php

namespace App\Support\Defaults;

/**
 * Starting values and page copy for the founder's personal portfolio.
 */
class FounderDefaults
{
    /**
     * @return array<string, mixed>
     */
    public static function attributes(): array
    {
        return [
            'name' => 'Your Name',
            'slug' => 'founder',
            'hero_title' => "Hi, I'm {{name}}.",
            'hero_statement' => 'I build things for the web.',
            'primary_cta_label' => 'View my work',
            'primary_cta_url' => '#projects',
            'secondary_cta_label' => 'Get in touch',
            'secondary_cta_url' => '#contact',
            'footer' => self::footer(),
            'content' => self::content(),
        ];
    }

    /**
     * Stored page copy merged over the defaults.
     *
     * @param  array<string, mixed>|null  $content
     * @return array<string, mixed>
     */
    public static function mergeContent(?array $content): array
    {
        return ContentMerger::merge(self::content(), $content);
    }

    /**
     * @return array<string, mixed>
     */
    public static function footer(): array
    {
        return [
            'status_text' => 'Available for select projects',
            'status_text_unavailable' => 'Currently unavailable',
            'copyright' => '© {{year}} {{name}}. All rights reserved.',
            'back_to_top' => 'Back to top',
            'columns' => [
                [
                    'title' => 'Portfolio',
                    'links' => [
                        ['label' => 'About', 'url' => '#about'],
                        ['label' => 'Skills', 'url' => '#skills'],
                        ['label' => 'Work', 'url' => '#projects'],
                        ['label' => 'Experience', 'url' => '#experience'],
                        ['label' => 'Contact', 'url' => '#contact'],
                    ],
                ],
                [
                    'title' => 'Resources',
                    'links' => [
                        ['label' => 'Download resume', 'url' => '/resume'],
                        ['label' => 'GitHub', 'url' => '{{github}}'],
                        ['label' => 'LinkedIn', 'url' => '{{linkedin}}'],
                    ],
                ],
                [
                    'title' => 'Contact',
                    'links' => [
                        ['label' => 'Email', 'url' => 'mailto:{{email}}'],
                        ['label' => '{{location}}', 'url' => ''],
                    ],
                ],
            ],
            'legal_links' => [],
        ];
    }

    /**
     * Every heading and label the founder page renders. Each is editable in
     * the admin panel; stored edits are merged over these by mergeContent().
     *
     * @return array<string, mixed>
     */
    public static function content(): array
    {
        return [
            'sections' => [
                'about' => [
                    'eyebrow' => 'About',
                    'title' => 'Engineer by craft, builder by nature',
                    'highlight' => 'craft',
                    'description' => 'A note from the founder, and how he works.',
                    'message_label' => "Founder's note",
                ],
                'skills' => [
                    'eyebrow' => 'Skills',
                    'title' => 'A production-grade toolkit',
                    'highlight' => 'toolkit',
                    'description' => 'The tools I reach for on the job, and how comfortable I am with each one.',
                ],
                'projects' => [
                    'eyebrow' => 'Projects',
                    'title' => 'Work that ships',
                    'highlight' => 'ships',
                    'description' => "A selection of products and platforms I've built end to end.",
                ],
                'experience' => [
                    'eyebrow' => 'Experience',
                    'title' => "Where I've shipped",
                    'highlight' => 'shipped',
                    'description' => 'The roles, teams and products that shaped how I build.',
                ],
                'contact' => [
                    'eyebrow' => 'Contact',
                    'title' => "Let's build something",
                    'highlight' => 'build',
                    'description' => 'Have a project, a role, or just a question? My inbox is open.',
                ],
            ],
            'hero' => [
                'resume_label' => 'Download resume',
                'years_label' => 'years',
                'projects_label' => 'projects',
                'skills_label' => 'skills',
            ],
            'about' => [
                'available_open' => 'Open to work, remote',
                'available_closed' => 'Selective availability',
            ],
        ];
    }
}
