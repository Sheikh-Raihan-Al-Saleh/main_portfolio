<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ExperienceSection from '@/components/Founder/ExperienceSection.vue';
import FounderAbout from '@/components/Founder/FounderAbout.vue';
import FounderHero from '@/components/Founder/FounderHero.vue';
import SkillsSection from '@/components/Founder/SkillsSection.vue';
import ContactSection from '@/components/Site/ContactSection.vue';
import WorkSection from '@/components/Studio/WorkSection.vue';
import { useSite } from '@/composables/useSite';
import type { FounderPageProps } from '@/types';

/**
 * The founder's personal portfolio (/founder).
 */
defineProps<FounderPageProps>();

const { founder } = useSite();
</script>

<template>
    <Head :title="founder.meta.title || founder.name">
        <meta
            v-if="founder.meta.description"
            name="description"
            :content="founder.meta.description"
        />
        <meta
            property="og:title"
            :content="founder.meta.title || founder.name"
        />
        <meta
            v-if="founder.og_image_url"
            property="og:image"
            :content="founder.og_image_url"
        />
    </Head>

    <FounderHero :content="content" :stats="stats" />

    <FounderAbout :copy="content.sections.about" />

    <SkillsSection
        v-if="skillGroups.length"
        :copy="content.sections.skills"
        :groups="skillGroups"
    />

    <WorkSection
        id="projects"
        :eyebrow="content.sections.projects.eyebrow"
        :title="content.sections.projects.title"
        :highlight="content.sections.projects.highlight"
        :description="content.sections.projects.description"
        :projects="projects"
    />

    <ExperienceSection
        v-if="experiences.length || educations.length"
        :copy="content.sections.experience"
        :experiences="experiences"
        :educations="educations"
    />

    <ContactSection
        site="founder"
        :eyebrow="content.sections.contact.eyebrow"
        :title="content.sections.contact.title"
        :highlight="content.sections.contact.highlight"
        :description="content.sections.contact.description"
        :email="founder.public_email"
        :phone="founder.phone"
        :location="founder.location"
        :socials="founder.socials"
    />
</template>
