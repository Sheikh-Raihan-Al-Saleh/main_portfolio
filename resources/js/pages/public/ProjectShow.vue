<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowUpRight } from '@lucide/vue';
import { computed } from 'vue';
import SmartImage from '@/components/media/SmartImage.vue';
import ProjectGallery from '@/components/project/ProjectGallery.vue';
import ProjectGrid from '@/components/project/ProjectGrid.vue';
import Reveal from '@/components/site/Reveal.vue';
import SocialIcon from '@/components/site/SocialIcon.vue';
import { useSite } from '@/composables/useSite';
import { paragraphs, yearRange } from '@/lib/text';
import projectRoutes from '@/routes/projects';
import type { ProjectCard, ProjectDetail } from '@/types';

/**
 * A single project (/projects/{slug}).
 */
const props = defineProps<{
    project: ProjectDetail;
    related: ProjectCard[];
}>();

const { studio, founder } = useSite();

const ownerName = computed(() =>
    props.project.owner === 'founder' ? founder.value.name : studio.value.name,
);

const facts = computed(() =>
    [
        { label: 'For', value: ownerName.value },
        { label: 'Role', value: props.project.role },
        {
            label: 'Timeline',
            value:
                props.project.started_at || props.project.completed_at
                    ? yearRange(
                          props.project.started_at,
                          props.project.completed_at,
                          'Ongoing',
                      )
                    : null,
        },
    ].filter((fact) => fact.value),
);
</script>

<template>
    <Head :title="project.title">
        <meta
            v-if="project.summary"
            name="description"
            :content="project.summary"
        />
        <meta
            v-if="project.cover_image_url"
            property="og:image"
            :content="project.cover_image_url"
        />
    </Head>

    <article>
        <header class="container-site pt-8 sm:pt-12">
            <Link
                :href="projectRoutes.index().url"
                class="meta inline-flex items-center gap-2 hover:text-ink"
            >
                <ArrowLeft class="size-4" aria-hidden="true" /> All work
            </Link>

            <Reveal class="mt-10">
                <p class="eyebrow">
                    {{
                        project.owner === 'founder'
                            ? 'Personal project'
                            : 'Studio project'
                    }}
                </p>
                <h1 class="display display-xl mt-6 max-w-[16ch]">
                    {{ project.title }}
                </h1>
            </Reveal>

            <div class="mt-12 grid gap-10 lg:grid-cols-12">
                <Reveal :delay="0.1" class="lg:col-span-7">
                    <p v-if="project.summary" class="lead">
                        {{ project.summary }}
                    </p>
                </Reveal>

                <Reveal :delay="0.15" class="lg:col-span-4 lg:col-start-9">
                    <dl>
                        <div
                            v-for="fact in facts"
                            :key="fact.label"
                            class="rule flex justify-between gap-6 py-3"
                        >
                            <dt class="meta">{{ fact.label }}</dt>
                            <dd class="text-right font-medium">
                                {{ fact.value }}
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <Link
                            v-if="project.landing_url"
                            :href="project.landing_url"
                            class="btn btn-accent btn-sm"
                        >
                            Case study
                            <ArrowUpRight
                                class="btn-arrow size-4"
                                aria-hidden="true"
                            />
                        </Link>
                        <a
                            v-if="project.live_url"
                            :href="project.live_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn btn-ink btn-sm"
                        >
                            Visit live site
                            <ArrowUpRight
                                class="btn-arrow size-4"
                                aria-hidden="true"
                            />
                        </a>
                        <a
                            v-if="project.repo_url"
                            :href="project.repo_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn btn-outline btn-sm"
                        >
                            <SocialIcon name="github" /> Source
                        </a>
                    </div>
                </Reveal>
            </div>
        </header>

        <Reveal class="container-site mt-16">
            <SmartImage
                :src="project.cover_image_url"
                :alt="project.title"
                ratio="16/9"
                eager
                class="rounded-[1.5rem]"
            >
                <template #fallback>
                    <span class="display text-[12rem] text-stone/25">{{
                        project.title.charAt(0)
                    }}</span>
                </template>
            </SmartImage>
        </Reveal>

        <section class="container-site section grid gap-14 lg:grid-cols-12">
            <aside class="lg:col-span-3">
                <h2 class="eyebrow">Built with</h2>
                <ul class="mt-5 flex flex-wrap gap-2">
                    <li v-for="tech in project.technologies" :key="tech.slug">
                        <Link
                            :href="
                                projectRoutes.index({
                                    query: { tech: tech.slug },
                                }).url
                            "
                            class="tag hover:border-ink"
                        >
                            {{ tech.name }}
                        </Link>
                    </li>
                </ul>
            </aside>

            <div class="lg:col-span-7 lg:col-start-5">
                <p
                    v-for="(paragraph, i) in paragraphs(project.description)"
                    :key="i"
                    class="mb-6 text-lg leading-[1.8] text-ink-soft first-of-type:text-xl first-of-type:text-ink"
                >
                    {{ paragraph }}
                </p>
            </div>
        </section>

        <section
            v-if="project.images.length"
            class="container-site pb-20"
            aria-label="Gallery"
        >
            <ProjectGallery :images="project.images" />
        </section>
    </article>

    <section v-if="related.length" class="section rule">
        <div class="container-site">
            <h2 class="display display-md mb-14">More <em>work</em></h2>
            <ProjectGrid :projects="related" :numbered="false" />
        </div>
    </section>
</template>
