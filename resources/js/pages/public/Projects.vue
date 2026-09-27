<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { AnimatePresence, motion } from 'motion-v';
import { computed, ref } from 'vue';
import ProjectCard from '@/components/portfolio/ProjectCard.vue';
import SectionHeading from '@/components/portfolio/SectionHeading.vue';
import { inViewOnce, stagger } from '@/lib/motion';
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

    <div id="top" class="mx-auto max-w-6xl scroll-mt-20 px-4 py-20 sm:px-6">
        <SectionHeading
            eyebrow="Archive"
            title="All projects"
            highlight="projects"
            description="Everything I've published, newest and most notable first."
        />

        <div v-if="technologies.length" class="mb-10 flex flex-wrap gap-2">
            <button
                type="button"
                :aria-pressed="activeTech === null"
                :class="
                    cn(
                        'relative rounded-full border px-3 py-1.5 text-sm transition-colors',
                        activeTech === null
                            ? 'border-brand text-brand-foreground'
                            : 'border-border/70 text-muted-foreground hover:border-brand/50 hover:text-foreground',
                    )
                "
                @click="filterBy(null)"
            >
                <!-- Shared layout id: the pill slides between chips instead of
                     blinking out of one and into the next. -->
                <motion.span
                    v-if="activeTech === null"
                    layout-id="tech-filter-pill"
                    class="absolute inset-0 rounded-full bg-brand"
                />
                <span class="relative">All</span>
            </button>
            <button
                v-for="tech in technologies"
                :key="tech"
                type="button"
                :aria-pressed="activeTech === tech"
                :class="
                    cn(
                        'relative rounded-full border px-3 py-1.5 text-sm transition-colors',
                        activeTech === tech
                            ? 'border-brand text-brand-foreground'
                            : 'border-border/70 text-muted-foreground hover:border-brand/50 hover:text-foreground',
                    )
                "
                @click="filterBy(tech)"
            >
                <motion.span
                    v-if="activeTech === tech"
                    layout-id="tech-filter-pill"
                    class="absolute inset-0 rounded-full bg-brand"
                />
                <span class="relative">{{ tech }}</span>
            </button>
        </div>

        <motion.div
            :variants="stagger(0.05)"
            initial="hidden"
            while-in-view="visible"
            :in-view-options="inViewOnce"
            class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
        >
            <AnimatePresence>
                <motion.div
                    v-for="project in visibleProjects"
                    :key="project.id"
                    layout
                    :initial="{ opacity: 0, scale: 0.96 }"
                    :animate="{ opacity: 1, scale: 1 }"
                    :exit="{ opacity: 0, scale: 0.96 }"
                    :transition="{ duration: 0.25 }"
                >
                    <ProjectCard :project="project" />
                </motion.div>
            </AnimatePresence>
        </motion.div>

        <p
            v-if="!visibleProjects.length"
            class="py-16 text-center text-muted-foreground"
        >
            No projects match that filter.
        </p>
    </div>
</template>
