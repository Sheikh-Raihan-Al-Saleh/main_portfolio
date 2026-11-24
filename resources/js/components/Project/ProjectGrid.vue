<script setup lang="ts">
import ProjectCard from '@/components/Project/ProjectCard.vue';
import Reveal from '@/components/Site/Reveal.vue';
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
            v-for="(project, projectIndex) in projects"
            :key="project.id"
            :delay="
                feature && projectIndex === 0 ? 0 : (projectIndex % 2) * 0.1
            "
            :class="
                feature && projectIndex === 0
                    ? 'md:col-span-2'
                    : projectIndex % 2 === 1
                      ? 'md:mt-24'
                      : ''
            "
        >
            <ProjectCard
                :project="project"
                :variant="feature && projectIndex === 0 ? 'feature' : 'default'"
                :index="numbered ? projectIndex : undefined"
                :eager="projectIndex < 2"
            />
        </Reveal>
    </div>
</template>
