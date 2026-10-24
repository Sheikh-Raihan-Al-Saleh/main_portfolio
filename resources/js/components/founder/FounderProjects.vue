<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { AnimatePresence, motion } from 'motion-v';
import { computed, ref } from 'vue';
import ProjectCard from '@/components/site/ProjectCard.vue';
import Reveal from '@/components/site/Reveal.vue';
import SectionHeader from '@/components/site/SectionHeader.vue';
import { contentConfig } from '@/lib/content';
import { cn } from '@/lib/utils';
import type { Profile, Project } from '@/types';

/**
 * The founder's work as a grid of project cards, with an All / Featured
 * switch that filters without a round trip.
 */
type Props = {
    projects: Project[];
};

const props = defineProps<Props>();

const page = usePage<{ profile: Profile | null }>();
const copy = computed(
    () => contentConfig(page.props.profile).sections.projects,
);

const filter = ref<'all' | 'featured'>('all');

const hasFeatured = computed(() =>
    props.projects.some((project) => project.is_featured),
);

const visible = computed(() =>
    filter.value === 'all'
        ? props.projects
        : props.projects.filter((project) => project.is_featured),
);
</script>

<template>
    <section id="projects" class="site-section scroll-mt-24 bg-card/60">
        <div class="site-container">
            <SectionHeader
                :eyebrow="copy.eyebrow"
                :title="copy.title"
                :highlight="copy.highlight"
                :description="copy.description"
            >
                <div
                    v-if="hasFeatured"
                    role="group"
                    aria-label="Filter projects"
                    class="inline-flex rounded-full border border-border bg-card p-1 shadow-[var(--shadow-soft)]"
                >
                    <button
                        v-for="option in ['all', 'featured'] as const"
                        :key="option"
                        type="button"
                        :aria-pressed="filter === option"
                        :class="
                            cn(
                                'rounded-full px-4 py-1.5 text-sm font-medium capitalize transition-colors',
                                filter === option
                                    ? 'bg-signal text-signal-foreground'
                                    : 'text-muted-foreground hover:text-foreground',
                            )
                        "
                        @click="filter = option"
                    >
                        {{ option }}
                    </button>
                </div>
            </SectionHeader>

            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <AnimatePresence>
                    <motion.div
                        v-for="project in visible"
                        :key="project.id"
                        layout
                        :initial="{ opacity: 0, y: 16 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :exit="{ opacity: 0, scale: 0.97 }"
                        :transition="{ duration: 0.35 }"
                    >
                        <ProjectCard :project="project" class="h-full" />
                    </motion.div>
                </AnimatePresence>
            </div>

            <Reveal v-if="!projects.length">
                <p class="mt-10 text-center text-muted-foreground">
                    No projects published yet.
                </p>
            </Reveal>
        </div>
    </section>
</template>
