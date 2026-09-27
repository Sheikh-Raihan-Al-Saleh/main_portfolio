<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import ProjectCard from '@/components/portfolio/ProjectCard.vue';
import SectionHeading from '@/components/portfolio/SectionHeading.vue';
import { fadeUp, inViewOnce, stagger } from '@/lib/motion';
import type { Project } from '@/types';

type Props = {
    projects: Project[];
    /** Published personal projects in total, so the copy can be honest. */
    totalProjects?: number;
};

const props = defineProps<Props>();

const remaining = computed(
    () => (props.totalProjects ?? props.projects.length) - props.projects.length,
);
</script>

<template>
    <section
        id="founder-work"
        class="bg-noise scroll-mt-20 border-t border-border py-20 sm:py-24"
    >
        <div class="container-laravel section-dashed-xl">
            <SectionHeading
                index="03"
                eyebrow="The founder's own work"
                title="Built outside"
                highlight="client"
                description="Side projects and open source the founder builds in his own time. His full portfolio, resume and full project history live on his own site."
            />

            <motion.div
                :variants="stagger(0.08)"
                initial="hidden"
                while-in-view="visible"
                :in-view-options="inViewOnce"
            >
                <div
                    v-if="projects.length"
                    class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <ProjectCard
                        v-for="project in projects"
                        :key="project.id"
                        :project="project"
                    />
                </div>

                <motion.p
                    v-else
                    :variants="fadeUp"
                    class="rounded-lg border border-dashed border-border py-16 text-center text-sm text-muted-foreground"
                >
                    No personal projects published yet.
                </motion.p>

                <motion.div :variants="fadeUp" class="mt-14 text-center">
                    <p
                        v-if="remaining > 0"
                        class="mb-4 text-sm text-muted-foreground"
                    >
                        {{ remaining }} more
                        {{ remaining === 1 ? 'project' : 'projects' }} on his
                        personal portfolio.
                    </p>

                    <Link
                        href="/founder"
                        class="btn-laravel group inline-flex items-center gap-2 rounded-lg px-6 py-3 text-sm font-semibold"
                    >
                        view full portfolio
                        <ArrowUpRight
                            class="size-4 text-brand transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                            aria-hidden="true"
                        />
                    </Link>
                </motion.div>
            </motion.div>
        </div>
    </section>
</template>
