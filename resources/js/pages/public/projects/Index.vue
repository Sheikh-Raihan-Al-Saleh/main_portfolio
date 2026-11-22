<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import ProjectGrid from '@/components/project/ProjectGrid.vue';
import Reveal from '@/components/site/Reveal.vue';
import { useSite } from '@/composables/useSite';
import type { TechnologyTag } from '@/types';
import type { PublicProjectsIndexProps } from '@/types';

/**
 * The work archive (/projects). Every published project arrives in one
 * payload and is filtered here, so switching filters is instant. The filters
 * are mirrored into the URL so a filtered view can be shared.
 */
const props = defineProps<PublicProjectsIndexProps>();

const { studio, founder } = useSite();

const owner = ref<'studio' | 'founder' | null>(props.filters.owner);

/** Technologies that appear on at least one project, alphabetically. */
const technologies = computed<TechnologyTag[]>(() => {
    const unique = new Map<string, TechnologyTag>();

    props.projects.forEach((project) =>
        project.technologies.forEach((tech) => unique.set(tech.slug, tech)),
    );

    return [...unique.values()].sort((a, b) => a.name.localeCompare(b.name));
});

// A stale ?tech= link that no project carries falls back to "all".
const tech = ref<string | null>(
    technologies.value.some((item) => item.slug === props.filters.tech)
        ? props.filters.tech
        : null,
);

const visible = computed(() =>
    props.projects.filter(
        (project) =>
            (owner.value === null || project.owner === owner.value) &&
            (tech.value === null ||
                project.technologies.some((item) => item.slug === tech.value)),
    ),
);

const owners = computed(() => [
    { value: null, label: 'All work' },
    { value: 'studio' as const, label: studio.value.short_name },
    { value: 'founder' as const, label: founder.value.name },
]);

watch([owner, tech], ([ownerValue, techValue]) => {
    const url = new URL(window.location.href);

    for (const [key, value] of [
        ['owner', ownerValue],
        ['tech', techValue],
    ] as const) {
        if (value) {
            url.searchParams.set(key, value);
        } else {
            url.searchParams.delete(key);
        }
    }

    window.history.replaceState(window.history.state, '', url);
});
</script>

<template>
    <Head title="Work">
        <meta
            name="description"
            :content="`Selected projects by ${studio.name} and ${founder.name}.`"
        />
    </Head>

    <section class="container-site pt-10 pb-12 sm:pt-16">
        <Reveal>
            <p class="eyebrow">Archive</p>
            <h1 class="display display-xl mt-8">Selected <em>work</em></h1>
            <p class="lead mt-8 max-w-2xl">
                Products, platforms and experiments, from client work at
                {{ studio.name }} to things {{ founder.name }} builds
                independently.
            </p>
        </Reveal>
    </section>

    <section
        class="container-site sticky top-20 z-30 bg-paper/90 py-4 backdrop-blur sm:top-24"
    >
        <div
            class="flex flex-col gap-4 border-y border-line py-4 lg:flex-row lg:items-center lg:justify-between"
        >
            <div
                class="flex flex-wrap gap-2"
                role="group"
                aria-label="Filter by owner"
            >
                <button
                    v-for="option in owners"
                    :key="option.label"
                    type="button"
                    class="tag cursor-pointer transition-colors hover:border-ink"
                    :class="owner === option.value && 'tag-active'"
                    :aria-pressed="owner === option.value"
                    @click="owner = option.value"
                >
                    {{ option.label }}
                </button>
            </div>

            <div
                class="mask-fade-x -mx-1 flex gap-2 overflow-x-auto px-1 pb-1"
                role="group"
                aria-label="Filter by technology"
            >
                <button
                    type="button"
                    class="tag shrink-0 cursor-pointer"
                    :class="tech === null && 'tag-active'"
                    :aria-pressed="tech === null"
                    @click="tech = null"
                >
                    Any stack
                </button>
                <button
                    v-for="item in technologies"
                    :key="item.slug"
                    type="button"
                    class="tag shrink-0 cursor-pointer transition-colors hover:border-ink"
                    :class="tech === item.slug && 'tag-active'"
                    :aria-pressed="tech === item.slug"
                    @click="tech = tech === item.slug ? null : item.slug"
                >
                    {{ item.name }}
                </button>
            </div>
        </div>
    </section>

    <section class="container-site section-tight">
        <p class="meta mb-10" aria-live="polite">
            {{ visible.length }}
            {{ visible.length === 1 ? 'project' : 'projects' }}
        </p>

        <ProjectGrid
            v-if="visible.length"
            :key="`${owner}-${tech}`"
            :projects="visible"
        />
        <p v-else class="lead py-20 text-center text-stone">
            No projects match these filters yet.
        </p>
    </section>
</template>
