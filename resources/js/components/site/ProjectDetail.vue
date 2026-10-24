<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowUpRight,
    Calendar,
    ExternalLink,
    GitBranch,
    UserRound,
} from '@lucide/vue';
import CircuitBackground from '@/components/site/CircuitBackground.vue';
import ProjectCard from '@/components/site/ProjectCard.vue';
import Reveal from '@/components/site/Reveal.vue';
import type { Project } from '@/types';

/**
 * A project page for either face — the accent comes from whichever face owns
 * the project. Title and facts, the cover, the write-up, the gallery, then
 * related work.
 */
type Props = {
    project: Project;
    related: Project[];
    paragraphs: string[];
    period: string | null;
    backHref: string;
    backLabel: string;
};

defineProps<Props>();
</script>

<template>
    <article class="relative overflow-hidden pt-32 pb-24 lg:pt-40">
        <div
            class="pointer-events-none absolute inset-x-0 top-0 h-[36rem]"
            aria-hidden="true"
        >
            <CircuitBackground />
            <div class="ui-blob ui-blob-a -top-32 right-1/4 size-[28rem]" />
            <div class="ui-blob ui-blob-b top-10 left-1/5 size-[20rem]" />
        </div>

        <div class="site-container relative">
            <Reveal>
                <Link
                    :href="backHref"
                    class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft class="size-4" />
                    {{ backLabel }}
                </Link>
            </Reveal>

            <div class="mt-8 grid gap-10 lg:grid-cols-12 lg:items-end">
                <div class="flex flex-col gap-5 lg:col-span-8">
                    <Reveal :delay="0.05">
                        <h1 class="ui-display ui-h1">{{ project.title }}</h1>
                    </Reveal>
                    <Reveal v-if="project.summary" :delay="0.1">
                        <p
                            class="max-w-2xl text-lg leading-relaxed text-pretty text-muted-foreground sm:text-xl"
                        >
                            {{ project.summary }}
                        </p>
                    </Reveal>
                    <Reveal
                        v-if="
                            project.landing_url ||
                            project.live_url ||
                            project.repo_url
                        "
                        :delay="0.15"
                        class="flex flex-wrap gap-3 pt-2"
                    >
                        <Link
                            v-if="project.landing_url"
                            :href="project.landing_url"
                            class="ui-btn ui-btn-primary"
                        >
                            Read the case study
                            <ArrowUpRight class="size-4" />
                        </Link>
                        <a
                            v-if="project.live_url"
                            :href="project.live_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            :class="
                                project.landing_url
                                    ? 'ui-btn ui-btn-secondary'
                                    : 'ui-btn ui-btn-primary'
                            "
                        >
                            <ExternalLink class="size-4" />
                            Live site
                        </a>
                        <a
                            v-if="project.repo_url"
                            :href="project.repo_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="ui-btn ui-btn-secondary"
                        >
                            <GitBranch class="size-4" />
                            Source
                        </a>
                    </Reveal>
                </div>

                <Reveal :delay="0.2" class="lg:col-span-4">
                    <dl class="ui-card grid gap-4 p-6 text-sm">
                        <div
                            v-if="project.role"
                            class="flex items-center gap-3"
                        >
                            <span class="ui-icon-tile size-9"
                                ><UserRound class="size-4"
                            /></span>
                            <div>
                                <dt class="text-xs text-muted-foreground">
                                    Role
                                </dt>
                                <dd class="font-semibold">
                                    {{ project.role }}
                                </dd>
                            </div>
                        </div>
                        <div v-if="period" class="flex items-center gap-3">
                            <span class="ui-icon-tile size-9"
                                ><Calendar class="size-4"
                            /></span>
                            <div>
                                <dt class="text-xs text-muted-foreground">
                                    Timeline
                                </dt>
                                <dd class="font-semibold">{{ period }}</dd>
                            </div>
                        </div>
                        <div v-if="project.tech_stack?.length">
                            <dt class="text-xs text-muted-foreground">Stack</dt>
                            <dd class="mt-2 flex flex-wrap gap-1.5">
                                <span
                                    v-for="tech in project.tech_stack"
                                    :key="tech"
                                    class="ui-pill"
                                    >{{ tech }}</span
                                >
                            </dd>
                        </div>
                    </dl>
                </Reveal>
            </div>

            <Reveal v-if="project.cover_image_url" :delay="0.1" class="mt-14">
                <div class="ui-card overflow-hidden p-2">
                    <img
                        :src="project.cover_image_url"
                        :alt="project.title"
                        class="w-full rounded-[1.1rem] object-cover"
                    />
                </div>
            </Reveal>

            <div
                v-if="paragraphs.length"
                class="mx-auto mt-16 flex max-w-3xl flex-col gap-6"
            >
                <Reveal v-for="(paragraph, index) in paragraphs" :key="index">
                    <p
                        class="leading-relaxed text-pretty"
                        :class="
                            index === 0
                                ? 'text-xl font-medium sm:text-2xl'
                                : 'text-lg text-muted-foreground'
                        "
                    >
                        {{ paragraph }}
                    </p>
                </Reveal>
            </div>

            <div
                v-if="project.gallery_urls.length"
                class="mt-16 grid gap-5 sm:grid-cols-2"
            >
                <Reveal
                    v-for="(image, index) in project.gallery_urls"
                    :key="image"
                    :delay="(index % 2) * 0.08"
                    :class="index % 3 === 0 ? 'sm:col-span-2' : ''"
                >
                    <div class="ui-card overflow-hidden p-2">
                        <img
                            :src="image"
                            :alt="`${project.title} screenshot ${index + 1}`"
                            loading="lazy"
                            class="w-full rounded-[1.1rem] object-cover"
                        />
                    </div>
                </Reveal>
            </div>

            <section
                v-if="related.length"
                class="mt-24 border-t border-border pt-16"
            >
                <h2 class="ui-display ui-h2 text-center">
                    More <span class="text-gradient">work</span>
                </h2>
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Reveal
                        v-for="(item, i) in related"
                        :key="item.id"
                        :delay="i * 0.08"
                    >
                        <ProjectCard :project="item" class="h-full" />
                    </Reveal>
                </div>
            </section>
        </div>
    </article>
</template>
