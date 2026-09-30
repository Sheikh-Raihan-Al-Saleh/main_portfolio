<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight, ExternalLink, GitBranch } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import ParallaxLayer from '@/components/motion/ParallaxLayer.vue';
import ProductMockup from '@/components/motion/ProductMockup.vue';
import Scene3D from '@/components/motion/Scene3D.vue';
import ScrollReveal from '@/components/motion/ScrollReveal.vue';
import ProjectCard from '@/components/portfolio/ProjectCard.vue';
import SectionHeading from '@/components/portfolio/SectionHeading.vue';
import { useStudioContent } from '@/composables/useStudioContent';
import { fadeUp, inViewOnce, stagger } from '@/lib/motion';
import type { Company, Project } from '@/types';

/**
 * Selected work.
 *
 * The first featured project is presented as a case study rather than a card:
 * a product mockup that leans and parallaxes, paired with the problem, the
 * stack and the way in. The rest of the archive stays in the familiar grid, so
 * the section opens with an argument and closes with an index.
 */
type Props = {
    company: Company;
    projects: Project[];
    /** Published company work in total, so a truncated home page can say so. */
    totalProjects?: number;
};

const props = defineProps<Props>();

const content = useStudioContent(props.company, 'work');

/** The first featured company project is given the full-width composition. */
const featured = computed(
    () => props.projects.find((project) => project.is_featured) ?? null,
);

const rest = computed(() =>
    props.projects.filter((project) => project.id !== featured.value?.id),
);

const remaining = computed(
    () => (props.totalProjects ?? props.projects.length) - props.projects.length,
);

/** What the mockup's address bar should read, so it feels like a real product. */
const featuredUrl = computed(() => {
    const project = featured.value;

    if (!project) {
        return 'localhost';
    }

    return project.live_url ?? `projects/${project.slug}`;
});

/** The years the project ran, when the studio recorded them. */
const featuredTimeline = computed(() => {
    const project = featured.value;

    if (!project) {
        return null;
    }

    const start = project.started_at?.slice(0, 4) ?? null;
    const end = project.completed_at?.slice(0, 4) ?? null;

    if (!start) {
        return end;
    }

    return start === end || !end ? start : `${start}—${end}`;
});

const featuredSummary = computed(
    () => featured.value?.summary ?? featured.value?.description ?? null,
);
</script>

<template>
    <section
        id="work"
        class="bg-noise cv-auto relative scroll-mt-20 border-t border-border pt-20 sm:pt-24"
    >
        <div class="corner-dot corner-dot-tl" aria-hidden="true" />
        <div class="corner-dot corner-dot-tr" aria-hidden="true" />

        <div class="container-laravel section-dashed-xl">
            <SectionHeading
                index="02"
                :eyebrow="content.eyebrow"
                :title="content.title"
                :highlight="content.highlight"
                :description="content.description"
            />

            <!-- ── Featured case study ── -->
            <ScrollReveal v-if="featured" :distance="34" :blur="6" class="mb-16">
                <Scene3D :max-rotate="6" :perspective="1500">
                    <ParallaxLayer :distance="22">
                        <div
                            class="grid items-center gap-10 lg:grid-cols-[1.3fr_1fr] lg:gap-14"
                        >
                            <ProductMockup
                                :image-url="featured.cover_image_url"
                                :alt="featured.title"
                                :url="featuredUrl"
                                :label="featured.slug"
                                layered
                                radius="xl"
                            />

                            <div class="flex flex-col gap-4">
                                <p
                                    class="font-mono text-[11px] tracking-[0.18em] text-brand uppercase"
                                >
                                    {{ content.featured_label }}
                                </p>

                                <h3
                                    class="font-display text-2xl font-bold tracking-tight text-balance sm:text-3xl"
                                >
                                    {{ featured.title }}
                                </h3>

                                <p
                                    v-if="featuredSummary"
                                    class="line-clamp-4 leading-relaxed text-pretty text-muted-foreground"
                                >
                                    {{ featuredSummary }}
                                </p>

                                <dl
                                    v-if="featured.role || featuredTimeline"
                                    class="flex flex-wrap gap-x-8 gap-y-3 border-y border-border py-4"
                                >
                                    <div v-if="featured.role">
                                        <dt
                                            class="font-mono text-[10px] tracking-widest text-muted-foreground uppercase"
                                        >
                                            Role
                                        </dt>
                                        <dd class="mt-1 text-sm font-medium">
                                            {{ featured.role }}
                                        </dd>
                                    </div>
                                    <div v-if="featuredTimeline">
                                        <dt
                                            class="font-mono text-[10px] tracking-widest text-muted-foreground uppercase"
                                        >
                                            Timeline
                                        </dt>
                                        <dd
                                            class="mt-1 font-mono text-sm font-medium"
                                        >
                                            {{ featuredTimeline }}
                                        </dd>
                                    </div>
                                </dl>

                                <ul
                                    v-if="featured.tech_stack?.length"
                                    class="flex flex-wrap gap-1.5"
                                >
                                    <li
                                        v-for="tech in featured.tech_stack.slice(
                                            0,
                                            6,
                                        )"
                                        :key="tech"
                                        class="studio-chip"
                                    >
                                        {{ tech }}
                                    </li>
                                </ul>

                                <div class="mt-1 flex flex-wrap items-center gap-3">
                                    <Link
                                        :href="`/projects/${featured.slug}`"
                                        class="btn-laravel-primary group inline-flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold"
                                    >
                                        {{ content.case_study_label }}
                                        <ArrowUpRight
                                            class="size-4 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                                            aria-hidden="true"
                                        />
                                    </Link>

                                    <a
                                        v-if="featured.live_url"
                                        :href="featured.live_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn-laravel inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold"
                                    >
                                        <ExternalLink
                                            class="size-4 text-brand"
                                            aria-hidden="true"
                                        />
                                        {{ content.live_label }}
                                    </a>

                                    <a
                                        v-if="featured.repo_url"
                                        :href="featured.repo_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                                    >
                                        <GitBranch
                                            class="size-4"
                                            aria-hidden="true"
                                        />
                                        {{ content.source_label }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </ParallaxLayer>
                </Scene3D>
            </ScrollReveal>

            <!-- ── The rest of the archive ── -->
            <motion.div
                :variants="stagger(0.08)"
                initial="hidden"
                while-in-view="visible"
                :in-view-options="inViewOnce"
            >
                <div v-if="rest.length" class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
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
                    {{ content.empty_text }}
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
