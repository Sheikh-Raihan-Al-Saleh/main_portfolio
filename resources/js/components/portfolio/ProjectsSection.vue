<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ArrowUpRight, ExternalLink, FolderGit2 } from '@lucide/vue';
import { AnimatePresence, motion } from 'motion-v';
import { computed, ref } from 'vue';
import ProjectDetailModal from '@/components/portfolio/ProjectDetailModal.vue';
import SectionHeading from '@/components/portfolio/SectionHeading.vue';
import { contentConfig } from '@/lib/content';
import { fadeUp, inViewOnce, stagger } from '@/lib/motion';
import type { Profile, Project } from '@/types';

type Props = {
    projects: Project[];
};

const props = defineProps<Props>();

const page = usePage<{ profile: Profile | null }>();
const content = computed(() => contentConfig(page.props.profile));

const filter = ref<'all' | 'featured'>('all');

const visible = computed(() =>
    filter.value === 'all'
        ? props.projects
        : props.projects.filter((p) => p.is_featured),
);

const featuredProject = computed(
    () => visible.value.find((p) => p.is_featured) ?? null,
);
const gridProjects = computed(() =>
    visible.value.filter((p) => p.id !== featuredProject.value?.id),
);

const modalProject = ref<Project | null>(null);

function closeModal(value: boolean) {
    if (!value) {
        modalProject.value = null;
    }
}

function openProject(project: Project) {
    modalProject.value = project;
}
</script>

<template>
    <section
        id="projects"
        class="bg-noise relative scroll-mt-20 border-t border-border pt-20 sm:pt-24"
    >
        <div class="corner-dot corner-dot-tl" aria-hidden="true" />
        <div class="corner-dot corner-dot-tr" aria-hidden="true" />

        <div class="container-laravel section-dashed-xl">
            <SectionHeading
                index="03"
                :eyebrow="content.sections.projects.eyebrow"
                :title="content.sections.projects.title"
                :highlight="content.sections.projects.highlight"
                :description="content.sections.projects.description"
            />

            <motion.div
                :variants="stagger(0.08)"
                initial="hidden"
                while-in-view="visible"
                :in-view-options="inViewOnce"
            >
                <!-- Filters -->
                <motion.div
                    :variants="fadeUp"
                    class="mb-10 flex flex-wrap items-center gap-2"
                >
                    <button
                        type="button"
                        :aria-pressed="filter === 'all'"
                        class="rounded-lg border px-4 py-1.5 text-xs font-semibold transition-all duration-200"
                        :class="
                            filter === 'all'
                                ? 'border-foreground bg-foreground text-background'
                                : 'border-border bg-card text-muted-foreground hover:text-foreground'
                        "
                        @click="filter = 'all'"
                    >
                        all
                    </button>
                    <button
                        type="button"
                        :aria-pressed="filter === 'featured'"
                        class="rounded-lg border px-4 py-1.5 text-xs font-semibold transition-all duration-200"
                        :class="
                            filter === 'featured'
                                ? 'border-foreground bg-foreground text-background'
                                : 'border-border bg-card text-muted-foreground hover:text-foreground'
                        "
                        @click="filter = 'featured'"
                    >
                        ★ featured
                    </button>
                    <span class="ml-auto text-xs text-muted-foreground"
                        >{{ visible.length }} project{{
                            visible.length === 1 ? '' : 's'
                        }}</span
                    >
                </motion.div>

                <!-- Featured hero card -->
                <motion.div
                    v-if="featuredProject"
                    :variants="fadeUp"
                    class="mb-10"
                >
                    <article
                        class="group relative flex overflow-hidden rounded-lg border border-border transition-shadow duration-300 hover:shadow-lg lg:flex-row"
                    >
                        <button
                            type="button"
                            class="relative w-full shrink-0 overflow-hidden lg:w-[55%]"
                            :aria-label="`View ${featuredProject.title} details`"
                            @click="openProject(featuredProject)"
                        >
                            <div class="aspect-[16/7] w-full">
                                <img
                                    v-if="featuredProject.cover_image_url"
                                    :src="featuredProject.cover_image_url"
                                    :alt="featuredProject.title"
                                    loading="lazy"
                                    class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.03]"
                                />
                                <div
                                    v-else
                                    class="grid h-full w-full place-items-center bg-muted text-foreground/20"
                                >
                                    <FolderGit2 class="size-14" />
                                </div>
                            </div>
                        </button>

                        <div class="flex flex-1 flex-col gap-3 p-5 sm:p-6">
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="font-mono text-xs text-brand uppercase"
                                >
                                    ★ featured
                                </span>
                                <span
                                    class="font-mono text-xs text-muted-foreground uppercase"
                                >
                                    {{
                                        featuredProject.landing_url
                                            ? 'live case study'
                                            : 'open source'
                                    }}
                                </span>
                                <span
                                    v-if="featuredProject.started_at"
                                    class="ml-auto text-[11px] text-muted-foreground tabular-nums"
                                >
                                    {{
                                        new Date(
                                            featuredProject.started_at,
                                        ).toLocaleDateString(undefined, {
                                            year: 'numeric',
                                        })
                                    }}
                                </span>
                            </div>

                            <h3
                                class="text-2xl font-bold tracking-tight text-balance"
                            >
                                <button
                                    type="button"
                                    class="text-left transition-colors duration-200 group-hover:text-brand"
                                    @click="openProject(featuredProject)"
                                >
                                    {{ featuredProject.title }}
                                </button>
                            </h3>

                            <p
                                v-if="featuredProject.summary"
                                class="line-clamp-4 text-sm leading-relaxed text-foreground/70"
                            >
                                {{ featuredProject.summary }}
                            </p>

                            <div class="mt-auto flex flex-wrap gap-1.5 pt-2">
                                <span
                                    v-for="tech in (
                                        featuredProject.tech_stack ?? []
                                    ).slice(0, 6)"
                                    :key="tech"
                                    class="rounded-lg border border-border bg-muted px-2.5 py-1 text-[10px] font-medium text-foreground/75"
                                >
                                    {{ tech }}
                                </span>
                            </div>

                            <div
                                class="flex flex-wrap items-center gap-4 border-t border-border pt-4"
                            >
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand transition-colors duration-200 hover:text-brand/80"
                                    @click="openProject(featuredProject)"
                                >
                                    details
                                    <ArrowUpRight
                                        class="size-3.5 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                                        aria-hidden="true"
                                    />
                                </button>
                                <a
                                    v-if="featuredProject.live_url"
                                    :href="featuredProject.live_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground transition-colors duration-200 hover:text-foreground"
                                >
                                    live
                                    <ExternalLink class="size-3.5" />
                                </a>
                                <a
                                    v-if="featuredProject.repo_url"
                                    :href="featuredProject.repo_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground transition-colors duration-200 hover:text-foreground"
                                >
                                    source
                                    <FolderGit2 class="size-3.5" />
                                </a>
                            </div>
                        </div>
                    </article>
                </motion.div>

                <!-- Grid cards -->
                <div
                    v-if="gridProjects.length"
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <AnimatePresence>
                        <motion.article
                            v-for="(project, i) in gridProjects"
                            :key="project.id"
                            :variants="fadeUp"
                            initial="hidden"
                            animate="visible"
                            exit="hidden"
                            class="group relative flex flex-col overflow-hidden rounded-lg border border-border transition-shadow duration-300 hover:shadow-lg"
                        >
                            <button
                                type="button"
                                class="relative block overflow-hidden"
                                :class="
                                    project.cover_image_url
                                        ? i % 3 === 0
                                            ? 'aspect-[4/3]'
                                            : 'aspect-video'
                                        : 'min-h-48'
                                "
                                :aria-label="`View ${project.title} details`"
                                @click="openProject(project)"
                            >
                                <img
                                    v-if="project.cover_image_url"
                                    :src="project.cover_image_url"
                                    :alt="project.title"
                                    loading="lazy"
                                    class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.04]"
                                />
                                <div
                                    v-else
                                    class="grid h-full w-full place-items-center bg-muted text-foreground/20"
                                >
                                    <FolderGit2 class="size-10" />
                                </div>
                                <span
                                    class="absolute right-3 bottom-3 inline-flex translate-y-1 items-center gap-1.5 rounded-lg bg-background/90 px-3 py-1.5 text-[11px] font-semibold text-foreground opacity-0 shadow-md backdrop-blur transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100"
                                >
                                    <span class="text-brand">$</span>
                                    quick_view
                                </span>
                            </button>

                            <div class="flex flex-1 flex-col gap-3 p-5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        v-if="project.is_featured"
                                        class="font-mono text-xs text-brand uppercase"
                                    >
                                        ★ featured
                                    </span>
                                    <span
                                        class="font-mono text-xs text-muted-foreground uppercase"
                                    >
                                        {{
                                            project.landing_url ? 'live' : 'oss'
                                        }}
                                    </span>
                                    <span
                                        v-if="project.started_at"
                                        class="ml-auto text-[11px] text-muted-foreground tabular-nums"
                                    >
                                        {{
                                            new Date(
                                                project.started_at,
                                            ).toLocaleDateString(undefined, {
                                                year: 'numeric',
                                            })
                                        }}
                                    </span>
                                </div>

                                <h3
                                    class="text-base font-bold tracking-tight text-balance"
                                >
                                    <button
                                        type="button"
                                        class="text-left transition-colors duration-200 group-hover:text-brand"
                                        @click="openProject(project)"
                                    >
                                        {{ project.title }}
                                    </button>
                                </h3>

                                <p
                                    v-if="project.summary"
                                    class="line-clamp-3 text-sm leading-relaxed text-foreground/70"
                                >
                                    {{ project.summary }}
                                </p>

                                <div
                                    class="mt-auto flex flex-wrap gap-1.5 pt-2"
                                >
                                    <span
                                        v-for="tech in (
                                            project.tech_stack ?? []
                                        ).slice(0, 3)"
                                        :key="tech"
                                        class="rounded-lg border border-border bg-muted px-2 py-1 text-[10px] font-medium text-foreground/75"
                                    >
                                        {{ tech }}
                                    </span>
                                    <span
                                        v-if="
                                            (project.tech_stack ?? []).length >
                                            3
                                        "
                                        class="rounded-lg border border-border px-2 py-1 text-[10px] text-muted-foreground"
                                    >
                                        +{{
                                            (project.tech_stack ?? []).length -
                                            3
                                        }}
                                    </span>
                                </div>

                                <div
                                    class="flex items-center gap-3 border-t border-border pt-3"
                                >
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand transition-colors duration-200 hover:text-brand/80"
                                        @click="openProject(project)"
                                    >
                                        details →
                                    </button>
                                    <a
                                        v-if="project.live_url"
                                        :href="project.live_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="ml-auto inline-flex items-center gap-1 text-[10px] font-semibold text-muted-foreground transition-colors duration-200 hover:text-foreground"
                                    >
                                        live
                                        <ExternalLink class="size-3" />
                                    </a>
                                    <a
                                        v-if="project.repo_url"
                                        :href="project.repo_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 text-[10px] font-semibold text-muted-foreground transition-colors duration-200 hover:text-foreground"
                                    >
                                        src
                                        <FolderGit2 class="size-3" />
                                    </a>
                                </div>
                            </div>
                        </motion.article>
                    </AnimatePresence>
                </div>

                <!-- Archive link -->
                <motion.div :variants="fadeUp" class="mt-12 text-center">
                    <a
                        href="/projects"
                        class="btn-laravel group inline-flex items-center gap-2 rounded-lg px-6 py-3 text-sm font-semibold"
                    >
                        view full archive
                        <ArrowUpRight
                            class="size-4 text-brand transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                            aria-hidden="true"
                        />
                    </a>
                </motion.div>
            </motion.div>

            <ProjectDetailModal
                :project="modalProject"
                :open="modalProject !== null"
                @update:open="closeModal"
            />
        </div>
    </section>
</template>
