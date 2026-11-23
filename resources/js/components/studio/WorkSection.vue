<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight } from '@lucide/vue';
import ProjectGrid from '@/components/project/ProjectGrid.vue';
import Reveal from '@/components/site/Reveal.vue';
import SectionHeading from '@/components/site/SectionHeading.vue';
import type { WorkSectionProps } from '@/types';

/**
 * A titled block of project cards with a link to the full archive. Used for
 * the studio's selected work and the founder's own work.
 */
withDefaults(defineProps<WorkSectionProps>(), {
    id: undefined,
    highlight: null,
    description: null,
    total: 0,
    archiveHref: undefined,
    archiveLabel: 'View all work',
    emptyText: 'No published work yet.',
    feature: true,
    alwaysShowArchive: false,
});
</script>

<template>
    <section :id="id" class="section scroll-mt-24">
        <div class="container-site">
            <SectionHeading
                :eyebrow="eyebrow"
                :title="title"
                :highlight="highlight"
                :description="description"
                align="split"
            />

            <div class="mt-16">
                <ProjectGrid
                    v-if="projects.length"
                    :projects="projects"
                    :feature="feature"
                />
                <p v-else class="lead text-stone">{{ emptyText }}</p>
            </div>

            <Reveal
                v-if="
                    archiveHref &&
                    (alwaysShowArchive || total > projects.length)
                "
                class="mt-20 flex justify-center"
            >
                <Link :href="archiveHref" class="btn btn-outline">
                    {{ archiveLabel }}
                    <span v-if="total" class="meta">({{ total }})</span>
                    <ArrowUpRight class="btn-arrow size-4" aria-hidden="true" />
                </Link>
            </Reveal>
        </div>
    </section>
</template>
