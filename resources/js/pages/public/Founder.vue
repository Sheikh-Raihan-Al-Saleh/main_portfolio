<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import AboutSection from '@/components/portfolio/AboutSection.vue';
import ContactSection from '@/components/portfolio/ContactSection.vue';
import ExperienceSection from '@/components/portfolio/ExperienceSection.vue';
import HeroSection from '@/components/portfolio/HeroSection.vue';
import ProjectsSection from '@/components/portfolio/ProjectsSection.vue';
import SkillsSection from '@/components/portfolio/SkillsSection.vue';
import type {
    Education,
    Experience,
    PortfolioStats,
    Profile,
    Project,
    SkillGroup,
} from '@/types';

type Props = {
    profile: Profile;
    projects: Project[];
    skillGroups: SkillGroup[];
    experiences: Experience[];
    educations: Education[];
    stats: PortfolioStats;
};

const props = defineProps<Props>();

const title = computed(
    () =>
        props.profile.meta_title ??
        `${props.profile.name} — ${props.profile.headline ?? 'About'}`,
);

/** A short slice of skill names to float as badges in the hero. */
const heroHighlights = computed(() =>
    props.skillGroups
        .flatMap((group) => group.skills.map((skill) => skill.name))
        .slice(0, 6),
);
</script>

<template>
    <Head :title="title">
        <meta
            v-if="profile.meta_description"
            name="description"
            :content="profile.meta_description"
        />
        <meta property="og:title" :content="title" />
        <meta
            v-if="profile.meta_description"
            property="og:description"
            :content="profile.meta_description"
        />
        <meta
            v-if="profile.og_image_url"
            property="og:image"
            :content="profile.og_image_url"
        />
        <meta name="twitter:card" content="summary_large_image" />
    </Head>

    <HeroSection
        :profile="profile"
        :stats="stats"
        :highlights="heroHighlights"
    />
    <AboutSection :profile="profile" />
    <SkillsSection :skill-groups="skillGroups" />
    <ProjectsSection :projects="projects" :archive-href="null" />
    <ExperienceSection :experiences="experiences" :educations="educations" />
    <ContactSection :profile="profile" />
</template>
