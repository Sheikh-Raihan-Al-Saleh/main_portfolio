<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { AnimatePresence, motion } from 'motion-v';
import { computed, ref } from 'vue';
import CircuitBackground from '@/components/site/CircuitBackground.vue';
import ProjectCard from '@/components/site/ProjectCard.vue';
import Reveal from '@/components/site/Reveal.vue';
import { cn } from '@/lib/utils';
import projectRoutes from '@/routes/projects';
import type { Profile, Project } from '@/types';

type Props = {
    profile: Profile;
    projects: Project[];
    technologies: string[];
    filters: { tech: string | null };
};

const props = defineProps<Props>();

/**
 * The server ships every published project once and filtering happens here,
 * so switching a tag is instant with no network round-trip (SRS 3.2). The URL
 * is still kept in sync so a filtered view stays shareable and bookmarkable.
 */
const activeTech = ref<string | null>(props.filters.tech);

const visibleProjects = computed(() =>
    activeTech.value === null
        ? props.projects
        : props.projects.filter((project) =>
              (project.tech_stack ?? []).includes(activeTech.value as string),
          ),
);

function filterBy(tech: string | null) {
    activeTech.value = tech;

    router.replace({
        url: projectRoutes.index({ query: tech ? { tech } : { tech: null } })
            .url,
        preserveScroll: true,
        preserveState: true,
    });
}
</script>

<template>
    <Head :title="`Projects — ${profile.name}`">
        <meta
            name="description"
            :content="`Projects built by ${profile.name}${profile.headline ? ', ' + profile.headline : ''}.`"
        />
    </Head>

    <section class="relative overflow-hidden pt-36 pb-24 lg:pt-44">
        <div
            class="pointer-events-none absolute inset-x-0 top-0 h-[32rem]"
            aria-hidden="true"
        >
            <CircuitBackground />
            <div class="ui-blob ui-blob-a -top-32 left-1/3 size-[28rem]" />
        </div>

        <div class="site-container relative">
            <div class="flex flex-col items-center gap-5 text-center">
                <Reveal
                    ><span class="ui-eyebrow"
                        >Archive · {{ projects.length }} projects</span
                    ></Reveal
                >
                <Reveal :delay="0.05">
                    <h1 class="ui-display ui-h1">
                        All <span class="text-gradient">projects</span>
                    </h1>
                </Reveal>
                <Reveal :delay="0.1">
                    <p class="max-w-xl text-lg text-muted-foreground">
                        Everything published, newest and most notable first.
                    </p>
                </Reveal>
            </div>

            <Reveal
                v-if="technologies.length"
                :delay="0.15"
                class="mt-10 flex flex-wrap justify-center gap-2"
            >
                <div
                    role="group"
                    aria-label="Filter by technology"
                    class="contents"
                >
                    <button
                        v-for="tech in [null, ...technologies]"
                        :key="tech ?? 'all'"
                        type="button"
                        :aria-pressed="activeTech === tech"
                        :class="
                            cn(
                                'rounded-full border px-4 py-2 text-sm font-medium transition-colors',
                                activeTech === tech
                                    ? 'border-transparent bg-signal text-signal-foreground shadow-[0_8px_20px_-10px_var(--brand-glow)]'
                                    : 'border-border bg-card text-muted-foreground hover:text-foreground',
                            )
                        "
                        @click="filterBy(tech)"
                    >
                        {{ tech ?? 'All' }}
                    </button>
                </div>
            </Reveal>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <AnimatePresence>
                    <motion.div
                        v-for="project in visibleProjects"
                        :key="project.id"
                        layout
                        :initial="{ opacity: 0, scale: 0.97 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :exit="{ opacity: 0, scale: 0.97 }"
                        :transition="{ duration: 0.25 }"
                    >
                        <ProjectCard :project="project" class="h-full" />
                    </motion.div>
                </AnimatePresence>
            </div>

            <p
                v-if="!visibleProjects.length"
                class="py-20 text-center text-muted-foreground"
            >
                No projects match that filter.
            </p>
        </div>
    </section>
</template>
