<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight, Mail, MapPin } from '@lucide/vue';
import { computed } from 'vue';
import SocialIcon from '@/components/portfolio/SocialIcon.vue';
import type { Profile } from '@/types';

/**
 * The founder, introduced on the studio site: portrait, name and role, the
 * opening of the founder's note, and the way through to the portfolio.
 */
type Props = {
    profile: Profile;
    href?: string;
    detailed?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    href: '/founder',
    detailed: false,
});

const role = computed(
    () => props.profile.roles?.[0] ?? props.profile.headline ?? 'Founder',
);

const intro = computed(() => {
    // \s* also swallows the \r of Windows line endings.
    const first = props.profile.founder_message?.split(/\n\s*\n/)[0]?.trim();

    return first && first.length > 0 ? first : (props.profile.tagline ?? '');
});

const socials = computed(() =>
    (
        [
            {
                href: props.profile.socials?.github ?? undefined,
                label: 'GitHub',
                icon: 'github',
            },
            {
                href: props.profile.socials?.linkedin ?? undefined,
                label: 'LinkedIn',
                icon: 'linkedin',
            },
        ] satisfies {
            href?: string;
            label: string;
            icon: 'github' | 'linkedin';
        }[]
    ).filter((social) => Boolean(social.href)),
);
</script>

<template>
    <article class="ui-card @container overflow-hidden p-2">
        <div class="grid gap-2 @xl:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
            <Link
                :href="href"
                class="group relative block aspect-[4/5] overflow-hidden rounded-[1.1rem] bg-muted @xl:aspect-auto"
            >
                <img
                    v-if="profile.avatar_url"
                    :src="profile.avatar_url"
                    :alt="profile.name"
                    loading="lazy"
                    class="size-full object-cover transition-transform duration-700 group-hover:scale-105"
                />
                <span
                    v-else
                    class="ui-display text-gradient grid size-full place-items-center text-8xl"
                >
                    {{ profile.name?.charAt(0) }}
                </span>
                <span
                    class="ui-glass absolute bottom-3 left-3 rounded-full px-3 py-1.5 text-xs font-semibold"
                >
                    {{ profile.headline ?? 'Founder' }}
                </span>
            </Link>

            <div class="flex flex-col gap-4 p-5 @xl:p-7">
                <p class="text-sm font-medium text-brand">{{ role }}</p>
                <h3 class="ui-display text-3xl">{{ profile.name }}</h3>
                <p
                    v-if="intro"
                    class="line-clamp-6 leading-relaxed text-pretty text-muted-foreground"
                >
                    “{{ intro }}”
                </p>

                <dl v-if="detailed" class="grid gap-2 text-sm">
                    <div
                        v-if="profile.public_email"
                        class="flex items-center gap-2.5"
                    >
                        <dt>
                            <Mail class="size-4 text-muted-foreground" /><span
                                class="sr-only"
                                >Email</span
                            >
                        </dt>
                        <dd>
                            <a
                                :href="`mailto:${profile.public_email}`"
                                class="ui-link"
                                >{{ profile.public_email }}</a
                            >
                        </dd>
                    </div>
                    <div
                        v-if="profile.location"
                        class="flex items-center gap-2.5"
                    >
                        <dt>
                            <MapPin class="size-4 text-muted-foreground" /><span
                                class="sr-only"
                                >Location</span
                            >
                        </dt>
                        <dd class="text-muted-foreground">
                            {{ profile.location }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-auto flex flex-wrap items-center gap-2 pt-2">
                    <Link :href="href" class="ui-btn ui-btn-primary ui-btn-sm">
                        View portfolio
                        <ArrowUpRight class="size-4" />
                    </Link>
                    <a
                        v-for="social in socials"
                        :key="social.label"
                        :href="social.href"
                        target="_blank"
                        rel="noopener noreferrer"
                        :aria-label="social.label"
                        class="grid size-9 place-items-center rounded-full border border-border text-muted-foreground transition-colors hover:border-brand hover:text-brand"
                    >
                        <SocialIcon :name="social.icon" class="size-4" />
                    </a>
                </div>
            </div>
        </div>
    </article>
</template>
