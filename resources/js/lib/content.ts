import type { ContentConfig, Profile } from '@/types';

/**
 * Mirror of `Profile::contentDefaults()` in PHP. Kept in sync so the applied
 * copy is identical whether a fresh row ships its defaults from the database
 * or an old row merges its stored (possibly partial) JSON over these.
 */
export const contentDefaults: ContentConfig = {
    sections: {
        about: {
            eyebrow: 'About',
            title: 'Engineer by craft, builder by nature',
            highlight: 'craft',
            description: 'A quick note on who I am and how I work.',
        },
        skills: {
            eyebrow: 'Skills',
            title: 'A production-grade toolkit',
            highlight: 'toolkit',
            description:
                'Select a module to see the tools I reach for on the job — and how comfortable I am with each one.',
        },
        projects: {
            eyebrow: 'Projects',
            title: 'Work that ships',
            highlight: 'ships',
            description:
                "A selection of products and platforms I've built end to end.",
        },
        experience: {
            eyebrow: 'Experience',
            title: "Where I've shipped",
            highlight: 'shipped',
            description:
                'The roles, teams and products that shaped how I build.',
        },
        contact: {
            eyebrow: 'Contact',
            title: "Let's build something",
            highlight: 'build',
            description:
                'Have a project, a role, or just a question? My inbox is open — expect a reply within a few working days.',
        },
    },
    hero: {
        primary_cta_label: 'View my work →',
        primary_cta_url: '#projects',
        secondary_cta_label: 'Get in touch',
        secondary_cta_url: '#contact',
        resume_label: './resume.pdf',
        years_label: 'years',
        projects_label: 'projects',
        skills_label: 'skills',
        scroll_label: 'scroll',
    },
    about: {
        role_label: 'role',
        location_label: 'location',
        email_label: 'email',
        phone_label: 'phone',
        available_open: 'Open to work — remote',
        available_closed: 'Selective availability',
    },
    contact: {
        toast_title: 'Message sent',
        toast_description:
            "Thanks for reaching out! I'll get back to you soon.",
    },
};

function isRecord(value: unknown): value is Record<string, unknown> {
    return typeof value === 'object' && value !== null && !Array.isArray(value);
}

function merge<T>(base: T, override: unknown): T {
    if (!isRecord(override)) {
        return base;
    }

    if (!isRecord(base)) {
        return override as T;
    }

    const result: Record<string, unknown> = { ...base };

    for (const [key, value] of Object.entries(override)) {
        if (value === null || value === undefined) {
            continue;
        }

        result[key] =
            key in result && isRecord(value) && isRecord(result[key])
                ? merge(result[key], value)
                : value;
    }

    return result as T;
}

/**
 * Resolve the effective page copy for this profile: stored admin values win,
 * anything missing falls back to the shipping defaults.
 */
export function contentConfig(profile?: Profile | null): ContentConfig {
    return merge(contentDefaults, profile?.content ?? {});
}
