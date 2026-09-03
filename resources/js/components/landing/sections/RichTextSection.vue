<script setup lang="ts">
import SectionShell from '@/components/landing/SectionShell.vue';
import type { LandingSection } from '@/types';

type Props = {
    section: Extract<LandingSection, { type: 'richtext' }>;
    anchor?: string;
};

defineProps<Props>();
</script>

<template>
    <SectionShell
        :eyebrow="section.eyebrow"
        :heading="section.heading"
        :subheading="section.subheading"
        :anchor="anchor"
    >
        <!--
          `body_html` is rendered server-side by LandingPageController with
          commonmark configured to strip raw HTML and reject unsafe links, so
          nothing author-supplied reaches the DOM as markup.
        -->
        <div
            v-if="section.body_html"
            class="prose-landing mx-auto max-w-3xl"
            v-html="section.body_html"
        />
    </SectionShell>
</template>
