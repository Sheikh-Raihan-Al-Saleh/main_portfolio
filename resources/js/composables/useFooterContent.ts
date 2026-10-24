import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { ComputedRef } from 'vue';
import { useSiteOwner } from '@/composables/useSiteOwner';
import type { FooterColumn, FooterLink } from '@/types';

export type RenderedLink = {
    label: string;
    href: string | undefined;
    external: boolean;
};

export type RenderedColumn = {
    title: string;
    links: RenderedLink[];
};

export type SocialLink = {
    href: string;
    name: 'github' | 'linkedin' | 'x' | 'website';
    label: string;
};

export type FooterContent = {
    columns: ComputedRef<RenderedColumn[]>;
    legalLinks: ComputedRef<RenderedLink[]>;
    socialLinks: ComputedRef<SocialLink[]>;
    isAvailable: ComputedRef<boolean>;
    statusText: ComputedRef<string>;
    copyright: ComputedRef<string>;
    backToTop: ComputedRef<string>;
};

/**
 * The anchors each non-home page owns, so a footer link either scrolls within
 * the page being read or travels to the page that has the section.
 */
const ABOUT_ANCHORS = ['top', 'clients', 'founder', 'contact'];
const FOUNDER_ANCHORS = [
    'top',
    'about',
    'skills',
    'projects',
    'experience',
    'contact',
];

/**
 * Everything the admin writes into a footer (Admin → Company / Founder →
 * Footer), resolved for whichever record owns the page being rendered.
 *
 * `{{placeholder}}` tokens are filled from that record. A token with no value
 * becomes an empty string, so a link that depends on a missing social or phone
 * is dropped rather than rendered broken.
 */
export function useFooterContent(): FooterContent {
    const page = usePage();
    const { scope, owner } = useSiteOwner();

    const isCompanySite = computed(() => scope.value === 'company');
    const isHome = computed(
        () => page.url === '/' || page.url.startsWith('/?'),
    );
    const isAboutRoute = computed(() => page.url.startsWith('/about'));
    const isFounderRoute = computed(() => page.url.startsWith('/founder'));
    const footer = computed(() => owner.value?.footer);
    const year = new Date().getFullYear();

    function whatsappUrl(): string | null {
        const phone = owner.value?.phone?.replace(/[^0-9]/g, '');

        return phone ? `https://wa.me/${phone}` : null;
    }

    function resolve(text: string): string {
        const p = owner.value;
        const tokens: Record<string, string> = {
            year: String(year),
            name: p?.name ?? '',
            headline: p?.headline ?? '',
            tagline: p?.tagline ?? '',
            email: p?.public_email ?? '',
            phone: p?.phone ?? '',
            location: p?.location ?? '',
            whatsapp: whatsappUrl() ?? '',
            github: p?.socials?.github ?? '',
            linkedin: p?.socials?.linkedin ?? '',
            x: p?.socials?.x ?? '',
            website: p?.socials?.website ?? '',
        };

        return text.replace(/\{\{\s*(\w+)\s*\}\}/g, (match, key: string) => {
            const value = tokens[key];

            return value === undefined ? match : value;
        });
    }

    function normalizeHref(url: string): string {
        const trimmed = url.trim();

        if (!trimmed.startsWith('#') || isHome.value) {
            return trimmed;
        }

        const owned = isAboutRoute.value
            ? ABOUT_ANCHORS
            : isFounderRoute.value
              ? FOUNDER_ANCHORS
              : [];

        if (owned.includes(trimmed.slice(1))) {
            return trimmed;
        }

        return `${isCompanySite.value ? '/' : '/founder'}${trimmed}`;
    }

    function renderLink(link: FooterLink): RenderedLink | null {
        const label = resolve(link.label ?? '').trim();
        const url = normalizeHref(resolve(link.url ?? '').trim());

        if (!label && !url) {
            return null;
        }

        if (!url) {
            return { label, href: undefined, external: false };
        }

        return {
            label: label || url,
            href: url,
            external: /^https?:\/\//.test(url),
        };
    }

    const columns = computed<RenderedColumn[]>(() =>
        (footer.value?.columns ?? [])
            .map((column: FooterColumn) => ({
                title: resolve(column.title ?? '').trim() || column.title,
                links: (column.links ?? [])
                    .map(renderLink)
                    .filter((link): link is RenderedLink => link !== null),
            }))
            .filter((column) => column.title && column.links.length > 0),
    );

    const legalLinks = computed<RenderedLink[]>(() =>
        (footer.value?.legal_links ?? [])
            .map(renderLink)
            .filter((link): link is RenderedLink => link !== null),
    );

    const socialLinks = computed<SocialLink[]>(() => {
        const socials = owner.value?.socials ?? {};

        return (
            [
                { href: socials.github, name: 'github', label: 'GitHub' },
                { href: socials.linkedin, name: 'linkedin', label: 'LinkedIn' },
                { href: socials.x, name: 'x', label: 'X' },
                { href: socials.website, name: 'website', label: 'Website' },
            ] as const
        )
            .filter(
                (link): link is typeof link & { href: string } =>
                    typeof link.href === 'string' && link.href.length > 0,
            )
            .map((link) => ({ ...link }));
    });

    /** The company advertises whether it takes work; the founder, a role. */
    const isAvailable = computed(
        () =>
            owner.value?.accepting_projects ??
            owner.value?.available_for_work ??
            false,
    );

    const statusText = computed(() => {
        const text = isAvailable.value
            ? (footer.value?.status_text ?? '')
            : (footer.value?.status_text_unavailable ?? '');

        return resolve(text).trim();
    });

    const copyright = computed(() =>
        resolve(
            footer.value?.copyright ??
                '© {{year}} {{name}}. All rights reserved.',
        ),
    );

    const backToTop = computed(() =>
        resolve(footer.value?.back_to_top ?? 'Back to top').trim(),
    );

    return {
        columns,
        legalLinks,
        socialLinks,
        isAvailable,
        statusText,
        copyright,
        backToTop,
    };
}
