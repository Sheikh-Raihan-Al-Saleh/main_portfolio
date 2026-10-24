<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight } from '@lucide/vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import type { Project } from '@/types';

/**
 * A project card for either face: the cover in a rounded frame that zooms
 * gently on hover, then the title, summary and stack. The whole card is one
 * link to the project page.
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
        :class="
            cn(
                'group ui-card ui-card-hover flex flex-col overflow-hidden p-2',
                props.class,
            )
        "
    >
        <div
            class="relative aspect-[16/10] overflow-hidden rounded-[1.1rem] bg-muted"
        >
            <img
                v-if="project.cover_image_url"
                :src="project.cover_image_url"
                :alt="project.title"
                loading="lazy"
                class="size-full object-cover transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-[1.04]"
            />
            <div
                v-else
                class="grid size-full place-items-center bg-gradient-to-br from-[var(--brand-subtle)] to-muted"
            >
                <span class="ui-display text-gradient text-5xl">{{
                    project.title.slice(0, 1)
                }}</span>
            </div>
            <span
                v-if="project.is_featured"
                class="ui-glass absolute top-3 left-3 rounded-full px-2.5 py-1 text-xs font-semibold"
                >Featured</span
            >
        </div>

        <div class="flex flex-1 flex-col gap-3 p-4">
            <div class="flex items-start justify-between gap-3">
                <h3 class="ui-h3">{{ project.title }}</h3>
                <span
                    class="grid size-8 shrink-0 place-items-center rounded-full border border-border transition-colors group-hover:border-transparent group-hover:bg-signal group-hover:text-signal-foreground"
                    aria-hidden="true"
                >
                    <ArrowUpRight class="size-4" />
                </span>
            </div>
            <p
                v-if="project.summary"
                class="line-clamp-2 text-sm leading-relaxed text-muted-foreground"
            >
                {{ project.summary }}
            </p>
            <div class="mt-auto flex flex-wrap items-center gap-1.5 pt-2">
                <span
                    v-for="tech in (project.tech_stack ?? []).slice(0, 3)"
                    :key="tech"
                    class="ui-pill"
                    >{{ tech }}</span
                >
                <span
                    v-if="year"
                    class="ml-auto text-xs text-muted-foreground"
                    >{{ year }}</span
                >
            </div>
        </div>
    </Link>
</template>
