<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import { computed } from 'vue';
import SocialIcon from '@/components/portfolio/SocialIcon.vue';
import { getInitials } from '@/composables/useInitials';
import { useSiteOwner } from '@/composables/useSiteOwner';
import { EASE_OUT, inViewOnce } from '@/lib/motion';
import type { FooterColumn, FooterLink } from '@/types';

const page = usePage();

/**
 * The footer belongs to whichever face of the site is being rendered: the
 * company on its own routes, the founder on About.
 */
const { scope, owner, company, profile } = useSiteOwner();

const isCompanySite = computed(() => scope.value === 'company');
const isHome = computed(() => page.url === '/' || page.url.startsWith('/?'));
const isAboutRoute = computed(() => page.url.startsWith('/about'));
const isFounderRoute = computed(() => page.url.startsWith('/founder'));
const year = new Date().getFullYear();
const footer = computed(() => owner.value?.footer);

/**
 * The anchors each non-home page owns, kept explicit so a footer link either
 * scrolls within the page being read or travels to the page that has the
 * section. `work` and `contact` are the two the default footers point at.
 */
const aboutAnchors = ['top', 'clients', 'founder', 'contact'];
const founderAnchors = ['top', 'about', 'skills', 'projects', 'experience', 'contact'];

const cardVariants = {
    hidden: { opacity: 0, rotateX: 12, y: 80, scale: 0.92 },
    visible: {
        opacity: 1,
        rotateX: 0,
        y: 0,
        scale: 1,
        transition: { duration: 1, ease: EASE_OUT },
    },
};

function brandVariants(delay: number) {
    return {
        hidden: { opacity: 0, x: -30 },
        visible: {
            opacity: 1,
            x: 0,
            transition: { duration: 0.7, ease: EASE_OUT, delay },
        },
    };
}

function socialVariants(delay: number) {
    return {
        hidden: { opacity: 0, scale: 0.5, rotateY: 90 },
        visible: {
            opacity: 1,
            scale: 1,
            rotateY: 0,
            transition: { type: 'spring', stiffness: 200, damping: 15, delay },
        },
    };
}

function columnVariants(delay: number) {
    return {
        hidden: { opacity: 0, y: 30, rotateX: 8 },
        visible: {
            opacity: 1,
            y: 0,
            rotateX: 0,
            transition: { duration: 0.6, ease: EASE_OUT, delay },
        },
    };
}

const initials = computed(() => getInitials(owner.value?.name));

/** Website logo from Admin → Founder; the studio logo is the fallback. */
const brandLogoUrl = computed(
    () => profile.value?.logo_url ?? company.value?.logo_url ?? null,
);

function waPhone(): string | null {
    const phone = owner.value?.phone?.replace(/[^0-9]/g, '');

    return phone ? `https://wa.me/${phone}` : null;
}

/**
 * Resolve {{placeholder}} tokens in footer admin text against whichever record
 * owns this page. Unresolved tokens (missing socials, phone, etc.) become empty
 * strings so that links referencing them are silently dropped.
 */
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
        whatsapp: waPhone() ?? '',
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

    if (! trimmed.startsWith('#')) {
        return trimmed;
    }

    if (isHome.value) {
        return trimmed;
    }

    // Each page keeps the anchors it actually owns — the About page has its own
    // contact block, the home page owns the company chapters — and everything
    // else travels to the page that does own it.
    const ownsAnchor = isAboutRoute.value
        ? aboutAnchors
        : isFounderRoute.value
          ? founderAnchors
          : [];

    if (ownsAnchor.includes(trimmed.slice(1))) {
        return trimmed;
    }

    return `${isCompanySite.value ? '/' : '/founder'}${trimmed}`;
}

/** Smoothly returns to the top; the href is the no-JS fallback. */
function scrollToTop(): void {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function isExternal(url: string): boolean {
    return /^https?:\/\//.test(url);
}

type RenderedLink = {
    label: string;
    href: string | undefined;
    external: boolean;
};

function renderLink(link: FooterLink): RenderedLink | null {
    const label = resolve(link.label ?? '').trim();
    const url = normalizeHref(resolve(link.url ?? '').trim());

    if (!label && !url) {
        return null;
    }

    if (!url) {
        return { label: label || url, href: undefined, external: false };
    }

    return { label: label || url, href: url, external: isExternal(url) };
}

type RenderedColumn = {
    title: string;
    links: RenderedLink[];
};

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

const socialLinks = computed(() => {
    const socials = owner.value?.socials ?? {};

    return [
        { href: socials.github, name: 'github' as const, label: 'GitHub' },
        {
            href: socials.linkedin,
            name: 'linkedin' as const,
            label: 'LinkedIn',
        },
        { href: socials.x, name: 'x' as const, label: 'X' },
        { href: socials.website, name: 'website' as const, label: 'Website' },
    ].filter((link) => typeof link.href === 'string' && link.href.length > 0);
});

const legalLinks = computed<RenderedLink[]>(() =>
    (footer.value?.legal_links ?? [])
        .map(renderLink)
        .filter((link): link is RenderedLink => link !== null),
);

/**
 * Availability differs per face: the company advertises whether it is taking
 * work, the founder whether he is looking for a role.
 */
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
        footer.value?.copyright ?? '© {{year}} {{name}}. All rights reserved.',
    ),
);

const backToTop = computed(() =>
    resolve(footer.value?.back_to_top ?? 'Back to top').trim(),
);
</script>

<template>
    <footer class="footer-3d relative overflow-hidden">
        <div class="footer-3d-perspective">
            <div class="footer-grid-bg" aria-hidden="true" />
            <div class="footer-orb footer-orb-1" aria-hidden="true" />
            <div class="footer-orb footer-orb-2" aria-hidden="true" />
            <div class="footer-orb footer-orb-3" aria-hidden="true" />

            <motion.div
                initial="hidden"
                while-in-view="visible"
                :variants="cardVariants"
                :in-view-options="inViewOnce"
                class="footer-main-card"
            >
                <div class="footer-border-glow" aria-hidden="true" />

                <div class="footer-content">
                    <div class="footer-upper">
                        <motion.div
                            initial="hidden"
                            while-in-view="visible"
                            :variants="brandVariants(0.15)"
                            :in-view-options="inViewOnce"
                            class="footer-brand-col"
                        >
                            <Link
                                href="/"
                                class="footer-logo-link"
                                :aria-label="owner?.name ?? 'Home'"
                            >
                                <span
                                    v-if="brandLogoUrl"
                                    class="footer-logo-image"
                                >
                                    <img
                                        :src="brandLogoUrl"
                                        :alt="owner?.name ?? 'Home'"
                                    />
                                </span>
                                <template v-else>
                                    <span class="footer-logo-icon">{{
                                        initials || '{ }'
                                    }}</span>
                                    <span class="footer-logo-text">{{
                                        owner?.name
                                    }}</span>
                                </template>
                            </Link>
                            <p v-if="owner?.tagline" class="footer-tagline">
                                {{ owner.tagline }}
                            </p>
                            <div v-if="statusText" class="footer-status">
                                <span
                                    class="footer-status-dot"
                                    :class="{
                                        'footer-status-dot--off': !isAvailable,
                                    }"
                                />
                                <span class="footer-status-text">{{
                                    statusText
                                }}</span>
                            </div>

                            <div
                                v-if="socialLinks.length"
                                class="footer-socials"
                            >
                                <motion.a
                                    v-for="(link, i) in socialLinks"
                                    :key="link.label"
                                    :href="link.href ?? undefined"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    :aria-label="link.label"
                                    class="footer-social-icon"
                                    initial="hidden"
                                    while-in-view="visible"
                                    :variants="socialVariants(0.5 + i * 0.08)"
                                    :in-view-options="inViewOnce"
                                    while-hover="{ y: -4, scale: 1.15, rotateY: 10 }"
                                >
                                    <SocialIcon
                                        :name="link.name"
                                        class="size-4"
                                    />
                                </motion.a>
                            </div>
                        </motion.div>

                        <motion.div
                            v-for="(col, i) in columns"
                            :key="col.title"
                            initial="hidden"
                            while-in-view="visible"
                            :variants="columnVariants(0.2 + i * 0.08)"
                            :in-view-options="inViewOnce"
                            class="footer-col"
                        >
                            <h4 class="footer-col-title">{{ col.title }}</h4>
                            <ul class="footer-col-list">
                                <li v-for="item in col.links" :key="item.label">
                                    <a
                                        v-if="item.href"
                                        :href="item.href"
                                        :target="
                                            item.external ? '_blank' : undefined
                                        "
                                        :rel="
                                            item.external
                                                ? 'noopener noreferrer'
                                                : undefined
                                        "
                                        class="footer-link"
                                    >
                                        <span class="footer-link-text">{{
                                            item.label
                                        }}</span>
                                        <svg
                                            v-if="item.external"
                                            class="footer-link-arrow"
                                            viewBox="0 0 12 12"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <path
                                                d="M3.5 2.5h6v6M10 2.5L2 10.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </a>
                                    <span
                                        v-else
                                        class="footer-link-text-plain"
                                        >{{ item.label }}</span
                                    >
                                </li>
                            </ul>
                        </motion.div>
                    </div>

                    <!-- <div
                        v-if="owner?.name"
                        class="footer-watermark-wrap"
                        aria-hidden="true"
                    >
                        <motion.p
                            initial="hidden"
                            while-in-view="visible"
                            :variants="watermarkVariants(0.8)"
                            :in-view-options="inViewOnce"
                            class="footer-watermark"
                        >
                            {{ owner.name?.toUpperCase() }}
                        </motion.p>
                    </div> -->

                    <div class="footer-divider" />

                    <div class="footer-bottom">
                        <div class="footer-bottom-inner">
                            <p v-if="copyright" class="footer-copyright">
                                {{ copyright }}
                            </p>

                            <div
                                v-if="legalLinks.length"
                                class="footer-bottom-links"
                            >
                                <template
                                    v-for="(link, i) in legalLinks"
                                    :key="`${link.label}-${i}`"
                                >
                                    <a
                                        v-if="link.href"
                                        :href="link.href"
                                        :target="
                                            link.external ? '_blank' : undefined
                                        "
                                        :rel="
                                            link.external
                                                ? 'noopener noreferrer'
                                                : undefined
                                        "
                                        class="footer-bottom-link"
                                    >
                                        {{ link.label }}
                                    </a>
                                    <span v-else class="footer-bottom-link">{{
                                        link.label
                                    }}</span>
                                    <span
                                        v-if="i < legalLinks.length - 1"
                                        class="footer-bottom-sep"
                                        >&middot;</span
                                    >
                                </template>
                            </div>

                            <motion.a
                                href="#top"
                                class="footer-back-top"
                                while-hover="{ y: -3 }"
                                whileTap="{ scale: 0.95 }"
                                @click.prevent="scrollToTop"
                            >
                                <svg
                                    class="size-3.5"
                                    viewBox="0 0 12 12"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                >
                                    <path
                                        d="M6 10V2M2.5 5.5L6 2l3.5 3.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <span>{{ backToTop }}</span>
                            </motion.a>
                        </div>
                    </div>
                </div>
            </motion.div>
        </div>
    </footer>
</template>
