<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight } from '@lucide/vue';
import LiquidImage from '@/components/Site/LiquidImage.vue';
import { cn } from '@/composables/utils';
import type { ProjectCardProps } from '@/types';

/**
 * One project in a grid: the cover (with the liquid hover effect), title,
 * summary and stack. The whole card links to the project page; the case
 * study link is offered on top when the project has a published one.
 */

const props = withDefaults(defineProps<ProjectCardProps>(), {
    variant: 'default',
    index: undefined,
    eager: false,
});
</script>

<template>
    <article
        :class="
            cn(
                'group relative flex flex-col',
                variant === 'feature' &&
                    'lg:grid lg:grid-cols-12 lg:items-end lg:gap-10',
            )
        "
    >
        <Link
            :href="project.url"
            :class="
                cn(
                    'relative block overflow-hidden rounded-[1.25rem] bg-paper-deep',
                    variant === 'feature' && 'lg:col-span-8',
                )
            "
            :aria-label="project.title"
        >
            <LiquidImage
                v-if="project.cover_image_url"
                :src="project.cover_image_url"
                :alt="project.title"
                :eager="eager"
                class="aspect-[16/10] w-full"
                img-class="transition-transform duration-[1.2s] ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-[1.04]"
            />
            <div
                v-else
                class="grid aspect-[16/10] w-full place-items-center bg-paper-deep"
                aria-hidden="true"
            >
                <span class="display text-7xl text-stone/40">{{
                    project.title.charAt(0)
                }}</span>
            </div>

            <span
                class="absolute top-4 right-4 grid size-12 place-items-center rounded-full bg-paper text-ink opacity-0 shadow-sm transition-all duration-500 group-hover:opacity-100 group-focus-visible:opacity-100"
                aria-hidden="true"
            >
                <ArrowUpRight class="size-5" />
            </span>
        </Link>

        <div
            :class="
                cn('mt-6', variant === 'feature' && 'lg:col-span-4 lg:mt-0')
            "
        >
            <p class="meta flex items-center gap-3">
                <span v-if="index !== undefined" class="index-number">{{
                    String(props.index! + 1).padStart(2, '0')
                }}</span>
                <span v-if="project.role">{{ project.role }}</span>
                <span v-if="project.year">· {{ project.year }}</span>
            </p>

            <h3
                :class="
                    cn(
                        'display mt-3 transition-colors group-hover:text-brand',
                        variant === 'feature'
                            ? 'display-md'
                            : 'text-3xl sm:text-4xl',
                    )
                "
            >
                <Link :href="project.url">{{ project.title }}</Link>
            </h3>

            <p v-if="project.summary" class="body-copy mt-3 line-clamp-3">
                {{ project.summary }}
            </p>

            <ul
                v-if="project.technologies.length"
                class="mt-5 flex flex-wrap gap-2"
            >
                <li
                    v-for="tech in project.technologies.slice(0, 5)"
                    :key="tech.slug"
                    class="tag"
                >
                    {{ tech.name }}
                </li>
            </ul>

            <div
                v-if="project.landing_url || project.live_url"
                class="mt-6 flex flex-wrap gap-5 text-sm font-medium"
            >
                <Link
                    v-if="project.landing_url"
                    :href="project.landing_url"
                    class="link-underline"
                >
                    Read the case study
                </Link>
                <a
                    v-if="project.live_url"
                    :href="project.live_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="link-underline text-stone hover:text-ink"
                >
                    Live site ↗
                </a>
            </div>
        </div>
    </article>
</template>
