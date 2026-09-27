<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight, Mail, MapPin } from '@lucide/vue';
import { computed } from 'vue';
import SocialIcon from '@/components/portfolio/SocialIcon.vue';
import type { Profile } from '@/types';

type Props = {
    profile: Profile;
    /** Where the reader goes to read the founder's own portfolio. */
    href?: string;
    /** Shown on the company About page; the home page uses the short form. */
    detailed?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    href: '/founder',
    detailed: false,
});

const role = computed(
    () => props.profile.roles?.[0] ?? props.profile.headline ?? 'Founder',
);

/**
 * The founder's message is a few paragraphs written for the About page. The
 * card shows the opening paragraph only, so the full text stays a reward for
 * following the link rather than a wall of text in a sidebar.
 */
const intro = computed(() => {
    const first = props.profile.founder_message?.split('\n\n')[0]?.trim();

    return first && first.length > 0
        ? first
        : (props.profile.tagline ?? '');
});

const socials = computed(() =>
    (
        [
            { href: props.profile.socials?.github ?? undefined, label: 'GitHub', icon: 'github' },
            {
                href: props.profile.socials?.linkedin ?? undefined,
                label: 'LinkedIn',
                icon: 'linkedin',
            },
        ] satisfies { href?: string; label: string; icon: 'github' | 'linkedin' }[]
    ).filter((social) => Boolean(social.href)),
);
</script>

<template>
    <article
        class="overflow-hidden rounded-2xl border border-border bg-card"
    >
        <div
            class="flex flex-col gap-6 p-6 sm:flex-row sm:items-start sm:p-8"
        >
            <div class="shrink-0">
                <img
                    v-if="profile.avatar_url"
                    :src="profile.avatar_url"
                    :alt="profile.name"
                    class="size-24 rounded-2xl object-cover ring-1 ring-border"
                    loading="lazy"
                />
                <div
                    v-else
                    class="grid size-24 place-items-center rounded-2xl bg-brand/10 font-display text-2xl font-bold text-brand"
                    aria-hidden="true"
                >
                    {{ profile.name?.charAt(0) }}
                </div>
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold tracking-widest text-brand uppercase">
                    {{ profile.headline ?? 'Founder' }}
                </p>
                <h3 class="font-display mt-1 text-2xl font-bold tracking-tight">
                    {{ profile.name }}
                </h3>
                <p class="text-sm text-muted-foreground">{{ role }}</p>

                <p
                    v-if="intro"
                    class="mt-4 text-sm leading-relaxed text-muted-foreground"
                >
                    {{ intro }}
                </p>

                <div
                    v-if="detailed"
                    class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-muted-foreground"
                >
                    <a
                        v-if="profile.public_email"
                        :href="`mailto:${profile.public_email}`"
                        class="inline-flex items-center gap-2 transition-colors hover:text-foreground"
                    >
                        <Mail class="size-4" />
                        {{ profile.public_email }}
                    </a>
                    <span
                        v-if="profile.location"
                        class="inline-flex items-center gap-2"
                    >
                        <MapPin class="size-4" />
                        {{ profile.location }}
                    </span>
                </div>

                <div
                    class="mt-6 flex flex-wrap items-center gap-3"
                >
                    <Link
                        :href="href"
                        class="btn-laravel group inline-flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold"
                    >
                        View personal portfolio
                        <ArrowUpRight
                            class="size-4 text-brand transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                            aria-hidden="true"
                        />
                    </Link>

                    <a
                        v-for="social in socials"
                        :key="social.label"
                        :href="social.href ?? undefined"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-border px-3 py-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <SocialIcon :name="social.icon" class="size-4" />
                        {{ social.label }}
                    </a>
                </div>
            </div>
        </div>
    </article>
</template>
