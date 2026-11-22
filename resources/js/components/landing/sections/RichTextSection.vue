<script setup lang="ts">
import SectionShell from '@/components/landing/SectionShell.vue';
import type { RichTextSectionProps } from '@/types';

defineProps<RichTextSectionProps>();
</script>

<template>
    <SectionShell
        :eyebrow="section.eyebrow"
        :heading="section.heading"
        :subheading="section.subheading"
        :anchor="anchor"
    >
        <div
            v-if="section.body_html"
            class="card-3d prose-landing mx-auto max-w-3xl p-6 sm:p-8"
            v-html="section.body_html"
        />
    </SectionShell>
</template>
