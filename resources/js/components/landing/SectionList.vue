<script setup lang="ts">
import { resolveSection } from '@/components/landing/sectionRegistry';
import type { LandingSection } from '@/types';

type Props = {
    sections: LandingSection[];
    /**
     * How a section's DOM id is derived, so the anchor can be jumped to and
     * picked up by the scroll spy. The id is passed down to the block.
     */
    anchorFor: (section: LandingSection) => string;
};

defineProps<Props>();
</script>

<template>
    <template v-for="section in sections" :key="section.id">
        <component
            :is="resolveSection(section.type)"
            v-if="resolveSection(section.type)"
            :section="section"
            :anchor="anchorFor(section)"
        />
    </template>
</template>
