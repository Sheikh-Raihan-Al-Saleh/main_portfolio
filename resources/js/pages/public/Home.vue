<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import SectionList from '@/components/landing/SectionList.vue';
import StudioAbout from '@/components/studio/StudioAbout.vue';
import StudioCapabilities from '@/components/studio/StudioCapabilities.vue';
import StudioContact from '@/components/studio/StudioContact.vue';
import StudioCta from '@/components/studio/StudioCta.vue';
import StudioEngineering from '@/components/studio/StudioEngineering.vue';
import StudioFounderBridge from '@/components/studio/StudioFounderBridge.vue';
import StudioHero from '@/components/studio/StudioHero.vue';
import StudioTrustBand from '@/components/studio/StudioTrustBand.vue';
import StudioValue from '@/components/studio/StudioValue.vue';
import StudioWork from '@/components/studio/StudioWork.vue';
import type {
    Client,
    Company,
    CompanyStats,
    LandingSection,
    Profile,
    Project,
} from '@/types';

type Props = {
    company: Company;
    profile: Profile;
    projects: Project[];
    totalProjects: number;
    personalProjects: Project[];
    totalPersonalProjects: number;
    clients: Client[];
    sections: LandingSection[];
    stats: CompanyStats;
};

const props = defineProps<Props>();

const title = computed(
    () =>
        props.company.meta_title ??
        `${props.company.name} — ${props.company.headline ?? 'Software Studio'}`,
);

/**
 * Builder blocks are named after the company (`features-9`, `stats-10`) so their
 * ids are stable, unique and meaningful in the DOM.
 */
function anchorFor(section: LandingSection): string {
    return `${section.type}-${section.id}`;
}

/**
 * The phrases on the marquee under the hero. The admin-authored list drives
 * it; the studio's live stats are appended so the band carries real numbers.
 * Blanks are dropped by the marquee.
 */
const tickerItems = computed(() => {
    const projects = props.stats.projects;
    const stored = props.company.studio_content.ticker.items ?? [];

    return [
        props.company.headline ?? 'Software studio',
        ...stored,
        props.company.accepting_projects ? 'Available for new projects' : '',
        projects !== null && projects > 0
            ? `${projects}+ products shipped`
            : '',
    ];
});
</script>

<template>
    <Head :title="title">
        <meta
            v-if="company.meta_description"
            name="description"
            :content="company.meta_description"
        />
        <meta property="og:title" :content="title" />
        <meta
            v-if="company.meta_description"
            property="og:description"
            :content="company.meta_description"
        />
        <meta
            v-if="company.og_image_url"
            property="og:image"
            :content="company.og_image_url"
        />
        <meta name="twitter:card" content="summary_large_image" />
    </Head>

    <StudioHero :company="company" :stats="stats" />

    <StudioTrustBand
        :clients="clients"
        :company="company"
        :items="tickerItems"
    />

    <!--
        The studio's story, in the order it has to be told: what we build,
        what we have built, how it is engineered, why us, who you are talking
        to, and finally the ask.
    -->
    <StudioCapabilities :company="company" />

    <StudioWork
        :company="company"
        :projects="projects"
        :total-projects="totalProjects"
    />

    <StudioEngineering :company="company" />

    <StudioValue :company="company" />

    <StudioAbout :company="company" :profile="profile" />

    <StudioFounderBridge :company="company" :profile="profile" />

    <StudioWork
        :company="company"
        :projects="personalProjects"
        :total-projects="totalPersonalProjects"
        section-id="founder-work"
        content-key="founder_work"
        archive-href="/founder#projects"
        muted
    />

    <!-- Composed blocks from the admin section builder -->
    <SectionList :sections="sections" :anchor-for="anchorFor" />

    <StudioCta :company="company" />

    <StudioContact :company="company" />
</template>
