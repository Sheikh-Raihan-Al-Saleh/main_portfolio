<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, ArrowUpRight } from '@lucide/vue';
import { computed, ref } from 'vue';
import LiquidImage from '@/components/site/LiquidImage.vue';
import Reveal from '@/components/site/Reveal.vue';
import SectionHeader from '@/components/site/SectionHeader.vue';
import { useStudioContent } from '@/composables/useStudioContent';
import type { Company, Project } from '@/types';

/**
 * Selected work, laid out like a magazine index.
 *
 * The first featured project opens as a full-width liquid image with its
 * title, role and stack. The rest follow as an index — "01 — Title — stack —
 * year" — and on a desktop the hovered row's cover floats beside the cursor.
 * Phones get an inline thumbnail on each row instead.
 *
 * The founder's own work on the home page reuses this with
 * `contentKey="founder_work"` (no featured case, its own archive link).
 */
type Props = {
    company: Company;
    projects: Project[];
    totalProjects?: number;
    sectionId?: string;
    contentKey?: 'work' | 'founder_work';
    archiveHref?: string;
    muted?: boolean;
    /** Chapter number for the section header, e.g. "02". */
    index?: string;
};

const props = withDefaults(defineProps<Props>(), {
    totalProjects: undefined,
    sectionId: 'work',
    contentKey: 'work',
    archiveHref: '/projects',
    muted: false,
    index: '',
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

function year(project: Project): string | null {
    return (
        project.completed_at?.slice(0, 4) ??
        project.started_at?.slice(0, 4) ??
        null
    );
}

function href(project: Project): string {
    return project.landing_url ?? `/projects/${project.slug}`;
}

/* ── The floating preview that follows the cursor over the index ── */

const hovered = ref<Project | null>(null);
const preview = ref<HTMLElement | null>(null);

function onRowEnter(project: Project) {
    hovered.value = project.cover_image_url ? project : null;
}

function onListLeave() {
    hovered.value = null;
}

function onListMove(event: PointerEvent) {
    if (preview.value) {
        preview.value.style.transform = `translate3d(${event.clientX}px, ${event.clientY}px, 0)`;
    }
}
</script>

<template>
    <section
        :id="sectionId"
        class="site-section scroll-mt-24"
        :class="muted ? 'ui-band' : ''"
    >
        <div class="site-container">
            <SectionHeader
                :index="index"
                :eyebrow="copy.eyebrow"
                :title="copy.title"
                :highlight="copy.highlight"
                :description="copy.description"
            />

            <p v-if="!projects.length" class="mt-12 text-muted-foreground">
                {{ copy.empty_text }}
            </p>

            <!-- Featured case study -->
            <Reveal v-if="featured" class="mt-16">
                <article class="group">
                    <Link
                        :href="href(featured)"
                        class="block"
                        data-cursor="view"
                    >
                        <LiquidImage
                            v-if="featured.cover_image_url"
                            :src="featured.cover_image_url"
                            :alt="featured.title"
                            class="aspect-[16/9] rounded-sm bg-muted"
                        />
                        <div
                            v-else
                            class="ui-dots grid aspect-[16/9] place-items-center rounded-sm bg-muted"
                        >
                            <span
                                class="ui-display text-[20vw] text-foreground/10"
                                >{{ featured.title.slice(0, 1) }}</span
                            >
                        </div>
                    </Link>

                    <div
                        class="mt-6 grid gap-6 border-b border-border pb-8 lg:grid-cols-12"
                    >
                        <div class="lg:col-span-7">
                            <p class="ui-label text-muted-foreground">
                                {{ copy.featured_label }}
                                <template v-if="year(featured)">
                                    — {{ year(featured) }}</template
                                >
                            </p>
                            <h3
                                class="ui-display mt-3 text-[clamp(2.75rem,6vw,5.5rem)]"
                            >
                                <Link :href="href(featured)">{{
                                    featured.title
                                }}</Link>
                            </h3>
                        </div>
                        <div class="flex flex-col gap-5 lg:col-span-5">
                            <p
                                v-if="featured.summary ?? featured.description"
                                class="line-clamp-4 text-lg leading-relaxed text-muted-foreground"
                            >
                                {{ featured.summary ?? featured.description }}
                            </p>
                            <ul
                                v-if="featured.tech_stack?.length"
                                class="flex flex-wrap gap-1.5"
                            >
                                <li
                                    v-for="tech in featured.tech_stack"
                                    :key="tech"
                                    class="ui-pill"
                                >
                                    {{ tech }}
                                </li>
                            </ul>
                            <div class="flex flex-wrap gap-3">
                                <Link
                                    :href="href(featured)"
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
                                    {{ copy.live_label }}
                                </a>
                                <a
                                    v-if="featured.repo_url"
                                    :href="featured.repo_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="ui-btn ui-btn-secondary"
                                >
                                    {{ copy.source_label }}
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            </Reveal>

            <!-- The index -->
            <ol
                v-if="rest.length"
                class="mt-10 border-t border-border"
                @pointerleave="onListLeave"
                @pointermove="onListMove"
            >
                <li
                    v-for="(project, i) in rest"
                    :key="project.id"
                    class="border-b border-border"
                    data-testid="work-row"
                    @pointerenter="onRowEnter(project)"
                >
                    <Link
                        :href="href(project)"
                        class="group grid grid-cols-[auto_1fr_auto] items-center gap-4 py-6 transition-colors sm:gap-8 sm:py-8"
                        data-cursor="view"
                    >
                        <span class="ui-label text-muted-foreground">{{
                            String(i + 1).padStart(2, '0')
                        }}</span>

                        <span class="flex min-w-0 items-center gap-4">
                            <img
                                v-if="project.cover_image_url"
                                :src="project.cover_image_url"
                                alt=""
                                loading="lazy"
                                class="aspect-[4/3] w-20 shrink-0 rounded-sm object-cover lg:hidden"
                            />
                            <span class="min-w-0">
                                <span
                                    class="ui-display block truncate text-[clamp(2rem,4.5vw,4rem)] transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:translate-x-3"
                                    >{{ project.title }}</span
                                >
                                <span
                                    v-if="project.tech_stack?.length"
                                    class="ui-label mt-1 block truncate text-muted-foreground"
                                    >{{
                                        project.tech_stack
                                            .slice(0, 3)
                                            .join(' · ')
                                    }}</span
                                >
                            </span>
                        </span>

                        <span class="flex items-center gap-4">
                            <span
                                v-if="year(project)"
                                class="ui-label hidden text-muted-foreground sm:inline"
                                >{{ year(project) }}</span
                            >
                            <span
                                class="grid size-11 place-items-center rounded-full border border-border transition-colors duration-300 group-hover:border-transparent group-hover:bg-[var(--accent-fill)] group-hover:text-[var(--accent-ink)]"
                                aria-hidden="true"
                            >
                                <ArrowUpRight class="size-4" />
                            </span>
                        </span>
                    </Link>
                </li>
            </ol>

            <!-- Cover that follows the cursor over the index (desktop) -->
            <div
                ref="preview"
                class="pointer-events-none fixed top-0 left-0 z-40 hidden lg:block"
                aria-hidden="true"
                data-testid="work-preview"
            >
                <div
                    class="-translate-x-1/2 -translate-y-1/2 transition-[opacity,scale] duration-300 ease-out"
                    :class="
                        hovered ? 'scale-100 opacity-100' : 'scale-75 opacity-0'
                    "
                >
                    <img
                        v-if="hovered?.cover_image_url"
                        :key="hovered.id"
                        :src="hovered.cover_image_url"
                        alt=""
                        class="aspect-[4/3] w-80 -rotate-3 rounded-sm object-cover shadow-2xl"
                    />
                </div>
            </div>

            <div v-if="projects.length && archiveHref" class="mt-12">
                <Link :href="archiveHref" class="ui-btn ui-btn-secondary">
                    {{ copy.archive_label }}
                    <span v-if="remaining > 0">(+{{ remaining }})</span>
                    <ArrowRight class="size-4" />
                </Link>
            </div>
        </div>
    </section>
</template>
