<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight, Building2 } from '@lucide/vue';
import { computed } from 'vue';
import Reveal from '@/components/site/Reveal.vue';
import { useStudioContent } from '@/composables/useStudioContent';
import type { Company, Profile } from '@/types';

/**
 * The seam between the two sites: a studio card and a founder card side by
 * side. The founder card sets `data-site="founder"`, so it is already drawn
 * in the founder's accent.
 */
type Props = {
    company: Company;
    profile: Profile;
};

const props = defineProps<Props>();

const content = useStudioContent(props.company, 'founder_bridge');

const founderRole = computed(
    () =>
        props.profile.roles?.[0] ??
        props.profile.headline ??
        'Software engineer',
);

function resolve(text: string, role: string): string {
    return (text ?? '')
        .replaceAll('{{name}}', props.company.name)
        .replaceAll('{{role}}', role);
}

const studioHeading = computed(() => resolve(content.value.studio_heading, ''));
const founderText = computed(() =>
    resolve(content.value.founder_text, founderRole.value),
);
</script>

<template>
    <section class="site-section">
        <div class="site-container grid gap-6 lg:grid-cols-2">
            <Reveal>
                <Link
                    href="/about"
                    class="group ui-card ui-card-hover relative flex h-full flex-col gap-6 overflow-hidden p-8 lg:p-10"
                >
                    <div
                        class="ui-blob ui-blob-a -top-20 -right-20 size-72 opacity-30"
                        aria-hidden="true"
                    />
                    <span class="ui-icon-tile relative"
                        ><Building2 class="size-5"
                    /></span>
                    <p class="relative text-sm font-medium text-brand">
                        {{ content.studio_label }}
                    </p>
                    <h2 class="ui-display relative text-3xl sm:text-4xl">
                        {{ studioHeading || company.name }}
                    </h2>
                    <p
                        class="relative max-w-md leading-relaxed text-muted-foreground"
                    >
                        {{ content.studio_text }}
                    </p>
                    <span
                        class="relative mt-auto inline-flex items-center gap-2 font-semibold"
                    >
                        {{ content.studio_link }}
                        <ArrowUpRight
                            class="size-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                        />
                    </span>
                </Link>
            </Reveal>

            <Reveal :delay="0.1">
                <Link
                    href="/founder"
                    data-site="founder"
                    class="group ui-card ui-card-hover relative flex h-full flex-col gap-6 overflow-hidden bg-card p-8 text-foreground lg:p-10"
                >
                    <div
                        class="ui-blob ui-blob-a -top-20 -right-20 size-72 opacity-30"
                        aria-hidden="true"
                    />
                    <img
                        v-if="profile.avatar_url"
                        :src="profile.avatar_url"
                        :alt="profile.name"
                        loading="lazy"
                        class="relative size-14 rounded-2xl object-cover ring-4 ring-[var(--brand-subtle)]"
                    />
                    <p class="relative text-sm font-medium text-brand">
                        {{ content.founder_label }}
                    </p>
                    <h2 class="ui-display relative text-3xl sm:text-4xl">
                        {{ profile.name }}
                    </h2>
                    <p
                        class="relative max-w-md leading-relaxed text-muted-foreground"
                    >
                        {{ founderText }}
                    </p>
                    <span
                        class="relative mt-auto inline-flex items-center gap-2 font-semibold text-brand"
                    >
                        {{ content.founder_link }}
                        <ArrowUpRight
                            class="size-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                        />
                    </span>
                </Link>
            </Reveal>
        </div>
    </section>
</template>
