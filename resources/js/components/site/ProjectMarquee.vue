<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { Project } from '@/types';

/**
 * An endless strip of real project covers, straight from the admin uploads.
 * Each tile links to its case study. The list is doubled so the CSS marquee
 * can loop seamlessly; the copy is hidden from assistive tech and the tab
 * order. Hover pauses it, and reduced motion turns it into a plain
 * scrollable row (see .ui-marquee in site.css).
 *
 * Renders nothing without at least two covers: one tile does not make a strip.
 */
type Props = {
    projects: Project[];
    label?: string;
};

const props = withDefaults(defineProps<Props>(), { label: 'Recent work' });

const items = computed(() =>
    props.projects.filter((project) => project.cover_image_url),
);

/** Enough copies to cover a wide screen before the loop point. */
const track = computed(() => {
    const base = items.value;

    if (base.length === 0) {
        return [];
    }

    const repeats = Math.max(1, Math.ceil(8 / base.length));

    return Array.from({ length: repeats }, () => base).flat();
});

function href(project: Project): string {
    return project.landing_url ?? `/projects/${project.slug}`;
}
</script>

<template>
    <section
        v-if="items.length >= 2"
        class="relative py-10 sm:py-14"
        :aria-label="label"
        data-testid="project-marquee"
    >
        <div class="ui-marquee-mask project-marquee overflow-hidden">
            <div
                class="ui-marquee"
                :style="{ '--marquee-duration': `${track.length * 6}s` }"
            >
                <template v-for="copy in 2" :key="copy">
                    <Link
                        v-for="(project, i) in track"
                        :key="`${copy}-${project.id}-${i}`"
                        :href="href(project)"
                        :aria-hidden="
                            copy === 2 || i >= items.length ? 'true' : undefined
                        "
                        :tabindex="
                            copy === 2 || i >= items.length ? -1 : undefined
                        "
                        class="group relative mr-4 block w-72 shrink-0 sm:w-96"
                        data-cursor="view"
                    >
                        <div
                            class="aspect-[4/3] overflow-hidden rounded-sm bg-muted"
                        >
                            <img
                                :src="project.cover_image_url ?? undefined"
                                :alt="
                                    copy === 1 && i < items.length
                                        ? project.title
                                        : ''
                                "
                                loading="lazy"
                                decoding="async"
                                class="size-full object-cover grayscale-[35%] transition duration-700 group-hover:scale-105 group-hover:grayscale-0"
                            />
                        </div>
                        <div
                            class="flex items-baseline justify-between gap-4 pt-3"
                        >
                            <p class="truncate text-sm font-semibold">
                                {{ project.title }}
                            </p>
                            <p
                                v-if="project.tech_stack?.length"
                                class="ui-label shrink-0 truncate text-muted-foreground"
                            >
                                {{ project.tech_stack[0] }}
                            </p>
                        </div>
                    </Link>
                </template>
            </div>
        </div>
    </section>
</template>

<style scoped>
@media (prefers-reduced-motion: reduce) {
    .project-marquee {
        overflow-x: auto;
    }

    .project-marquee :deep(.ui-marquee) {
        animation: none;
    }
}
</style>
