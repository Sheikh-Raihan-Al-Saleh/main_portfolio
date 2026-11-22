<script setup lang="ts">
import ProjectCard from '@/components/project/ProjectCard.vue';
import Reveal from '@/components/site/Reveal.vue';
import type { ProjectGridProps } from '@/types';

/**
 * A two-column project grid, optionally led by one wide feature card.
 */
withDefaults(defineProps<ProjectGridProps>(), {
    feature: false,
    numbered: true,
});
</script>

<template>
    <div class="grid gap-x-10 gap-y-20 md:grid-cols-2">
        <Reveal
            v-for="(project, i) in projects"
            :key="project.id"
            :delay="feature && i === 0 ? 0 : (i % 2) * 0.1"
            :class="
                feature && i === 0
                    ? 'md:col-span-2'
                    : i % 2 === 1
                      ? 'md:mt-24'
                      : ''
            "
        >
            <ProjectCard
                :project="project"
                :variant="feature && i === 0 ? 'feature' : 'default'"
                :index="numbered ? i : undefined"
                :eager="i < 2"
            />
        </Reveal>
    </div>
</template>
