<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import ClientLogosSection from '@/components/company/ClientLogosSection.vue';
import CompanyAboutSection from '@/components/company/CompanyAboutSection.vue';
import CompanyContactSection from '@/components/company/CompanyContactSection.vue';
import CompanyHero from '@/components/company/CompanyHero.vue';
import CompanyWorkSection from '@/components/company/CompanyWorkSection.vue';
import FounderWorkSection from '@/components/company/FounderWorkSection.vue';
import StudioCapabilitiesSection from '@/components/company/StudioCapabilitiesSection.vue';
import StudioCtaSection from '@/components/company/StudioCtaSection.vue';
import StudioFounderBridge from '@/components/company/StudioFounderBridge.vue';
import StudioStackSection from '@/components/company/StudioStackSection.vue';
import StudioValueSection from '@/components/company/StudioValueSection.vue';
import SectionList from '@/components/landing/SectionList.vue';
import ScrollTicker from '@/components/motion/ScrollTicker.vue';
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
 * The phrases that ride the scroll ticker under the hero, in the order they
 * read. The admin-authored list drives it; the studio's live stats are
 * appended so the band always carries real numbers. Blanks are dropped.
 */
const tickerItems = computed(() => {
    const projects = props.stats.projects;
    const stored = props.company.studio_content.ticker.items ?? [];

    return [
        props.company.name,
        props.company.headline ?? 'Software studio',
        props.company.location ?? '',
        ...stored,
        props.company.accepting_projects ? 'Available for new projects' : '',
        projects !== null && projects > 0 ? `${projects}+ products shipped` : '',
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

    <CompanyHero
        :company="company"
        :stats="stats"
        :brand-logo-url="profile.logo_url ?? company.logo_url"
    />

    <!-- Brand band: drifts with scroll, no JavaScript -->
    <ScrollTicker :items="tickerItems" />

    <!-- Social proof: who the studio has delivered for -->
    <ClientLogosSection :clients="clients" :company="company" />

    <!--
        The studio's story, in the order it has to be told: what we build,
        what we have built, how it is engineered, why us, who you are talking
        to, and finally the ask. Each section owns its own chapter number.
    -->
    <StudioCapabilitiesSection :company="company" />

    <CompanyWorkSection
        :company="company"
        :projects="projects"
        :total-projects="totalProjects"
    />

    <StudioStackSection :company="company" />

    <StudioValueSection :company="company" />

    <!-- About us, with the founder card linking out to his own portfolio -->
    <CompanyAboutSection :company="company" :profile="profile" compact />

    <!-- The seam between the company and the engineer behind it -->
    <StudioFounderBridge :company="company" :profile="profile" />

    <!-- A preview of the founder's own work, with the link to the rest -->
    <FounderWorkSection
        :company="company"
        :projects="personalProjects"
        :total-projects="totalPersonalProjects"
    />

    <!-- Composed blocks from the admin section builder -->
    <SectionList :sections="sections" :anchor-for="anchorFor" />

    <!-- The close, immediately above the form it points at -->
    <StudioCtaSection :company="company" />

    <CompanyContactSection :company="company" />
</template>
