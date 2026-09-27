<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import ProjectCard from '@/components/portfolio/ProjectCard.vue';
import SectionHeading from '@/components/portfolio/SectionHeading.vue';
import { fadeUp, inViewOnce, stagger } from '@/lib/motion';
import type { Company, Project } from '@/types';

type Props = {
    company: Company;
    projects: Project[];
    /** Published company work in total, so a truncated home page can say so. */
    totalProjects?: number;
};

const props = defineProps<Props>();

/** The first featured company project is given the full-width hero card. */
const featured = computed(
    () => props.projects.find((project) => project.is_featured) ?? null,
);

const rest = computed(() =>
    props.projects.filter((project) => project.id !== featured.value?.id),
);

const remaining = computed(
    () => (props.totalProjects ?? props.projects.length) - props.projects.length,
);
</script>

<template>
    <section
        id="work"
        class="bg-noise relative scroll-mt-20 border-t border-border pt-20 sm:pt-24"
    >
        <div class="corner-dot corner-dot-tl" aria-hidden="true" />
        <div class="corner-dot corner-dot-tr" aria-hidden="true" />

        <div class="container-laravel section-dashed-xl">
            <SectionHeading
                index="01"
                eyebrow="Selected work"
                title="Things we have built"
                :highlight="'built'"
                :description="`A selection of client and studio work from ${company.name}. Each one links to a short case study covering the problem, the approach and what shipped.`"
            />

            <motion.div
                :variants="stagger(0.08)"
                initial="hidden"
                while-in-view="visible"
                :in-view-options="inViewOnce"
            >
                <div
                    v-if="featured"
                    class="mb-12"
                >
                    <ProjectCard :project="featured" :featured="true" />
                </div>

                <div
                    v-if="rest.length"
                    class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <ProjectCard
                        v-for="project in rest"
                        :key="project.id"
                        :project="project"
                    />
                </div>

                <motion.p
                    v-if="!projects.length"
                    :variants="fadeUp"
                    class="rounded-lg border border-dashed border-border py-16 text-center text-sm text-muted-foreground"
                >
                    No published work yet.
                </motion.p>

                <motion.div :variants="fadeUp" class="mt-14 text-center">
                    <p
                        v-if="remaining > 0"
                        class="mb-4 text-sm text-muted-foreground"
                    >
                        {{ remaining }} more
                        {{ remaining === 1 ? 'project' : 'projects' }} in the
                        archive.
                    </p>

                    <Link
                        href="/projects"
                        class="btn-laravel group inline-flex items-center gap-2 rounded-lg px-6 py-3 text-sm font-semibold"
                    >
                        view full archive
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
