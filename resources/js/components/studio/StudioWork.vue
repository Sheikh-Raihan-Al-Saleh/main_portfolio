<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, ArrowUpRight, ExternalLink, GitBranch } from '@lucide/vue';
import { computed } from 'vue';
import ProjectCard from '@/components/site/ProjectCard.vue';
import Reveal from '@/components/site/Reveal.vue';
import SectionHeader from '@/components/site/SectionHeader.vue';
import { useStudioContent } from '@/composables/useStudioContent';
import type { Company, Project } from '@/types';

/**
 * Selected work. The first featured project opens as a case-study card; the
 * rest follow as a grid. The founder's own work on the home page reuses this
 * with `contentKey="founder_work"` (no featured card, its own archive link).
 */
type Props = {
    company: Company;
    projects: Project[];
    totalProjects?: number;
    sectionId?: string;
    contentKey?: 'work' | 'founder_work';
    archiveHref?: string;
    muted?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    totalProjects: undefined,
    sectionId: 'work',
    contentKey: 'work',
    archiveHref: '/projects',
    muted: false,
});

const content = useStudioContent(props.company, props.contentKey);
const copy = computed(() => content.value as Record<string, string>);

const featured = computed(() =>
    props.contentKey === 'work'
        ? (props.projects.find((project) => project.is_featured) ?? null)
        : null,
);

const rest = computed(() =>
    props.projects.filter((project) => project.id !== featured.value?.id),
);

const remaining = computed(
    () =>
        (props.totalProjects ?? props.projects.length) - props.projects.length,
);

const timeline = computed(() => {
    const start = featured.value?.started_at?.slice(0, 4) ?? null;
    const end = featured.value?.completed_at?.slice(0, 4) ?? null;

    if (!start) {
        return end;
    }

    return start === end || !end ? start : `${start} — ${end}`;
});
</script>

<template>
    <section
        :id="sectionId"
        class="site-section scroll-mt-24"
        :class="muted ? 'bg-card/60' : ''"
    >
        <div class="site-container">
            <SectionHeader
                :eyebrow="copy.eyebrow"
                :title="copy.title"
                :highlight="copy.highlight"
                :description="copy.description"
            />

            <p
                v-if="!projects.length"
                class="mt-12 text-center text-muted-foreground"
            >
                {{ copy.empty_text }}
            </p>

            <!-- Featured case study -->
            <Reveal v-if="featured" class="mt-14">
                <article
                    class="group ui-card ui-glow-border grid overflow-hidden p-2 lg:grid-cols-12"
                >
                    <Link
                        :href="`/projects/${featured.slug}`"
                        class="relative block aspect-[16/10] overflow-hidden rounded-[1.1rem] bg-muted lg:col-span-7 lg:aspect-auto"
                    >
                        <img
                            v-if="featured.cover_image_url"
                            :src="featured.cover_image_url"
                            :alt="featured.title"
                            loading="lazy"
                            class="size-full object-cover transition-transform duration-1000 group-hover:scale-[1.03]"
                        />
                    </Link>
                    <div class="flex flex-col gap-5 p-6 lg:col-span-5 lg:p-10">
                        <span class="ui-eyebrow w-fit">{{
                            copy.featured_label
                        }}</span>
                        <h3 class="ui-display text-3xl sm:text-4xl">
                            {{ featured.title }}
                        </h3>
                        <p
                            v-if="featured.summary ?? featured.description"
                            class="line-clamp-5 leading-relaxed text-muted-foreground"
                        >
                            {{ featured.summary ?? featured.description }}
                        </p>

                        <dl
                            class="grid grid-cols-2 gap-4 rounded-2xl bg-muted/60 p-4 text-sm"
                        >
                            <div v-if="featured.role">
                                <dt class="text-xs text-muted-foreground">
                                    Role
                                </dt>
                                <dd class="mt-1 font-semibold">
                                    {{ featured.role }}
                                </dd>
                            </div>
                            <div v-if="timeline">
                                <dt class="text-xs text-muted-foreground">
                                    Timeline
                                </dt>
                                <dd class="mt-1 font-semibold">
                                    {{ timeline }}
                                </dd>
                            </div>
                            <div
                                v-if="featured.tech_stack?.length"
                                class="col-span-2"
                            >
                                <dt class="text-xs text-muted-foreground">
                                    Stack
                                </dt>
                                <dd class="mt-2 flex flex-wrap gap-1.5">
                                    <span
                                        v-for="tech in featured.tech_stack"
                                        :key="tech"
                                        class="ui-pill"
                                        >{{ tech }}</span
                                    >
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-auto flex flex-wrap gap-3 pt-2">
                            <Link
                                :href="
                                    featured.landing_url ??
                                    `/projects/${featured.slug}`
                                "
                                class="ui-btn ui-btn-primary"
                            >
                                {{ copy.case_study_label }}
                                <ArrowUpRight class="size-4" />
                            </Link>
                            <a
                                v-if="featured.live_url"
                                :href="featured.live_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="ui-btn ui-btn-secondary"
                            >
                                <ExternalLink class="size-4" />
                                {{ copy.live_label }}
                            </a>
                            <a
                                v-if="featured.repo_url"
                                :href="featured.repo_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="ui-btn ui-btn-secondary"
                            >
                                <GitBranch class="size-4" />
                                {{ copy.source_label }}
                            </a>
                        </div>
                    </div>
                </article>
            </Reveal>

            <div
                v-if="rest.length"
                class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Reveal
                    v-for="(project, i) in rest"
                    :key="project.id"
                    :delay="(i % 3) * 0.08"
                >
                    <ProjectCard :project="project" class="h-full" />
                </Reveal>
            </div>

            <div
                v-if="projects.length && archiveHref"
                class="mt-12 flex justify-center"
            >
                <Link :href="archiveHref" class="ui-btn ui-btn-secondary">
                    {{ copy.archive_label }}
                    <span
                        v-if="remaining > 0"
                        class="rounded-full bg-brand-subtle px-2 py-0.5 text-xs text-brand"
                        >+{{ remaining }}</span
                    >
                    <ArrowRight class="size-4" />
                </Link>
            </div>
        </div>
    </section>
</template>
