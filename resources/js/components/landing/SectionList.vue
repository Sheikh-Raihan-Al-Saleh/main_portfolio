<script setup lang="ts">
import { resolveSection } from '@/components/landing/sectionRegistry';
import type { SectionListProps } from '@/types';

defineProps<SectionListProps>();
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
