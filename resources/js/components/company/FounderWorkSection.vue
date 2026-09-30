<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import ProjectCard from '@/components/portfolio/ProjectCard.vue';
import SectionHeading from '@/components/portfolio/SectionHeading.vue';
import { useStudioContent } from '@/composables/useStudioContent';
import { fadeUp, inViewOnce, stagger } from '@/lib/motion';
import type { Company, Project } from '@/types';

type Props = {
    company?: Company;
    projects: Project[];
    /** Published personal projects in total, so the copy can be honest. */
    totalProjects?: number;
};

const props = withDefaults(defineProps<Props>(), { company: undefined });

const content = useStudioContent(props.company, 'founder_work');

const remaining = computed(
    () => (props.totalProjects ?? props.projects.length) - props.projects.length,
);
</script>

<template>
    <section
        id="founder-work"
        class="bg-noise cv-auto scroll-mt-20 border-t border-border py-20 sm:py-24"
    >
        <div class="container-laravel section-dashed-xl">
            <SectionHeading
                index="06"
                :eyebrow="content.eyebrow"
                :title="content.title"
                :highlight="content.highlight"
                :description="content.description"
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
                    {{ content.empty_text }}
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
                        {{ content.archive_label }}
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
