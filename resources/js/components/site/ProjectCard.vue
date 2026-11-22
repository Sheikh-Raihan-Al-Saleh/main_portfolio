<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight } from '@lucide/vue';
import { computed } from 'vue';
import LiquidImage from '@/components/site/LiquidImage.vue';
import { cn } from '@/lib/utils';
import type { Project } from '@/types';

/**
 * A project card in the editorial style: the cover as a liquid image that
 * ripples under the cursor, then the role and year, the title in condensed
 * type with an arrow that turns, the summary and the stack on a hairline.
 * The whole card is one link to the project page.
 */
type Props = {
    project: Project;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), { class: undefined });

const year = computed(
    () =>
        props.project.completed_at?.slice(0, 4) ??
        props.project.started_at?.slice(0, 4) ??
        null,
);
</script>

<template>
    <Link
        :href="`/projects/${project.slug}`"
        :class="cn('group flex flex-col', props.class)"
        data-cursor="view"
    >
        <div class="relative aspect-[4/3] overflow-hidden rounded-sm bg-muted">
            <LiquidImage
                v-if="project.cover_image_url"
                :src="project.cover_image_url"
                :alt="project.title"
                class="size-full"
            />
            <div
                v-else
                class="ui-dots grid size-full place-items-center bg-muted"
            >
                <span class="ui-display text-8xl text-foreground/15">{{
                    project.title.slice(0, 1)
                }}</span>
            </div>

            <span
                v-if="project.is_featured"
                class="ui-label absolute top-3 left-3 bg-[var(--accent-fill)] px-2 py-1 text-[var(--accent-ink)]"
                >Featured</span
            >
        </div>

        <div class="flex flex-1 flex-col gap-3 border-b border-border py-5">
            <div class="ui-label flex items-center justify-between gap-3">
                <span
                    v-if="project.role"
                    class="truncate text-muted-foreground"
                    >{{ project.role }}</span
                >
                <span v-if="year" class="ml-auto text-muted-foreground">{{
                    year
                }}</span>
            </div>
            <h3
                class="ui-display flex items-start justify-between gap-4 text-4xl sm:text-5xl"
            >
                <span>{{ project.title }}</span>
                <ArrowUpRight
                    class="mt-1 size-7 shrink-0 transition-transform duration-500 group-hover:rotate-45"
                    aria-hidden="true"
                />
            </h3>
            <p
                v-if="project.summary"
                class="line-clamp-2 leading-relaxed text-muted-foreground"
            >
                {{ project.summary }}
            </p>
            <div class="mt-auto flex flex-wrap items-center gap-1.5 pt-1">
                <span
                    v-for="tech in (project.tech_stack ?? []).slice(0, 4)"
                    :key="tech"
                    class="ui-pill"
                    >{{ tech }}</span
                >
            </div>
        </div>
    </Link>
</template>
