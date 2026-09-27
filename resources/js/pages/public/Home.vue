<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import ClientLogosSection from '@/components/company/ClientLogosSection.vue';
import CompanyAboutSection from '@/components/company/CompanyAboutSection.vue';
import CompanyContactSection from '@/components/company/CompanyContactSection.vue';
import CompanyHero from '@/components/company/CompanyHero.vue';
import CompanyWorkSection from '@/components/company/CompanyWorkSection.vue';
import FounderWorkSection from '@/components/company/FounderWorkSection.vue';
import SectionList from '@/components/landing/SectionList.vue';
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

    <CompanyHero :company="company" :stats="stats" />

    <!-- Social proof: who the studio has delivered for -->
    <ClientLogosSection :clients="clients" />

    <CompanyWorkSection
        :company="company"
        :projects="projects"
        :total-projects="totalProjects"
    />

    <!-- About us, with the founder card linking out to his own portfolio -->
    <CompanyAboutSection :company="company" :profile="profile" compact />

    <!-- A preview of the founder's own work, with the link to the rest -->
    <FounderWorkSection
        :projects="personalProjects"
        :total-projects="totalPersonalProjects"
    />

    <!-- Composed blocks from the admin section builder -->
    <SectionList :sections="sections" :anchor-for="anchorFor" />

    <CompanyContactSection :company="company" />
</template>
