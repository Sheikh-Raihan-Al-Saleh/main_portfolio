<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUp } from '@lucide/vue';
import { computed } from 'vue';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import SocialLinks from '@/components/site/SocialLinks.vue';
import { useSite } from '@/composables/useSite';
import { fillTokens } from '@/lib/text';
import { home } from '@/routes';
import type { FooterLink } from '@/types';

/**
 * The public footer: the company logo as a large anchor, the footer columns
 * edited in the admin (with {{token}} placeholders filled in), availability,
 * social links and the legal line.
 */
const { brand, studio, founder, face } = useSite();

const owner = computed(() =>
    face.value === 'founder' ? founder.value : studio.value,
);

const available = computed(() =>
    face.value === 'founder'
        ? founder.value.is_available_for_work
        : studio.value.is_accepting_projects,
);

const tokens = computed(() => {
    const socials = Object.fromEntries(
        owner.value.socials.map((link) => [link.platform, link.url]),
    );

    return {
        name: owner.value.name,
        year: new Date().getFullYear(),
        email: owner.value.public_email,
        phone: owner.value.phone,
        location: owner.value.location,
        ...socials,
    };
});

const footer = computed(() => owner.value.footer);

/** A link is shown only when its URL resolved to something real. */
function resolveLink(
    link: FooterLink,
): { label: string; url: string | null } | null {
    const label = fillTokens(link.label, tokens.value).trim();
    const url = fillTokens(link.url, tokens.value).trim();
    const empty = url === '' || url === 'mailto:' || url === 'tel:';

    if (!label) {
        return null;
    }

    // A labelled entry without a URL (e.g. the location) is plain text.
    if (empty && link.url) {
        return null;
    }

    return { label, url: empty ? null : url };
}

const columns = computed(() =>
    (footer.value.columns ?? [])
        .map((column) => ({
            title: column.title,
            links: column.links
                .map(resolveLink)
                .filter((link) => link !== null),
        }))
        .filter((column) => column.links.length > 0),
);

const legal = computed(() =>
    (footer.value.legal_links ?? [])
        .map(resolveLink)
        .filter((link) => link !== null && link.url !== null),
);

const statusText = computed(() =>
    available.value
        ? footer.value.status_text
        : footer.value.status_text_unavailable,
);

const isInternal = (url: string) =>
    url.startsWith('/') && !url.startsWith('//');
</script>

<template>
    <footer class="relative mt-10 border-t border-ink bg-paper">
        <div class="container-site pt-16 pb-10 sm:pt-24">
            <div class="grid gap-14 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <Link :href="home().url" :aria-label="`${brand.name} home`">
                        <BrandLogo size="xl" />
                    </Link>
                    <p v-if="brand.tagline" class="lead mt-8 max-w-md">
                        {{ brand.tagline }}
                    </p>

                    <p
                        v-if="statusText"
                        class="mt-8 inline-flex items-center gap-3 text-sm text-ink-soft"
                    >
                        <span
                            class="status-dot"
                            :class="!available && 'status-dot-off'"
                        />
                        {{ statusText }}
                    </p>

                    <SocialLinks :links="owner.socials" class="mt-8" />
                </div>

                <nav
                    aria-label="Footer"
                    class="grid grid-cols-2 gap-10 sm:grid-cols-3 lg:col-span-7"
                >
                    <div v-for="column in columns" :key="column.title">
                        <h3 class="eyebrow eyebrow-plain mb-5">
                            {{ column.title }}
                        </h3>
                        <ul class="grid gap-3">
                            <li v-for="link in column.links" :key="link!.label">
                                <template v-if="link!.url">
                                    <Link
                                        v-if="isInternal(link!.url)"
                                        :href="link!.url"
                                        class="link-underline text-ink-soft hover:text-ink"
                                    >
                                        {{ link!.label }}
                                    </Link>
                                    <a
                                        v-else
                                        :href="link!.url"
                                        class="link-underline text-ink-soft hover:text-ink"
                                        :target="
                                            link!.url.startsWith('http')
                                                ? '_blank'
                                                : undefined
                                        "
                                        :rel="
                                            link!.url.startsWith('http')
                                                ? 'noopener noreferrer'
                                                : undefined
                                        "
                                    >
                                        {{ link!.label }}
                                    </a>
                                </template>
                                <span v-else class="text-stone">{{
                                    link!.label
                                }}</span>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>

            <!-- The name, set huge, as the closing typographic gesture. -->
            <p
                class="display mt-20 overflow-hidden text-[clamp(3.5rem,15vw,15rem)] leading-[0.8] tracking-tight text-ink"
                aria-hidden="true"
            >
                {{ brand.short_name }}<em>.</em>
            </p>

            <div
                class="rule mt-10 flex flex-col gap-4 pt-6 text-sm text-stone sm:flex-row sm:items-center sm:justify-between"
            >
                <p>
                    {{
                        fillTokens(footer.copyright, tokens) ||
                        `© ${tokens.year} ${brand.name}`
                    }}
                </p>

                <div class="flex flex-wrap items-center gap-6">
                    <a
                        v-for="link in legal"
                        :key="link!.label"
                        :href="link!.url!"
                        class="link-underline hover:text-ink"
                    >
                        {{ link!.label }}
                    </a>
                    <a
                        href="#top"
                        class="inline-flex items-center gap-2 hover:text-ink"
                    >
                        {{ footer.back_to_top || 'Back to top' }}
                        <ArrowUp class="size-4" aria-hidden="true" />
                    </a>
                </div>
            </div>
        </div>
    </footer>
</template>
