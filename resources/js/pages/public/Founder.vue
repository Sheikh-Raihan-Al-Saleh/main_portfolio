<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import FounderAbout from '@/components/founder/FounderAbout.vue';
import FounderContact from '@/components/founder/FounderContact.vue';
import FounderExperience from '@/components/founder/FounderExperience.vue';
import FounderHero from '@/components/founder/FounderHero.vue';
import FounderProjects from '@/components/founder/FounderProjects.vue';
import FounderSkills from '@/components/founder/FounderSkills.vue';
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

/** A short slice of skill names for the hero's portrait pane. */
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

    <FounderHero
        :profile="profile"
        :stats="stats"
        :highlights="heroHighlights"
    />
    <FounderAbout :profile="profile" />
    <FounderSkills :skill-groups="skillGroups" />
    <FounderProjects :projects="projects" />
    <FounderExperience :experiences="experiences" :educations="educations" />
    <FounderContact :profile="profile" />
</template>
