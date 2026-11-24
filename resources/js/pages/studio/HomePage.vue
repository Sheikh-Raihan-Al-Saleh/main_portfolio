<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SectionList from '@/components/LandingPage/SectionList.vue';
import ContactSection from '@/components/Site/ContactSection.vue';
import AboutSection from '@/components/Studio/AboutSection.vue';
import CapabilitiesSection from '@/components/Studio/CapabilitiesSection.vue';
import ClientStrip from '@/components/Studio/ClientStrip.vue';
import CtaBanner from '@/components/Studio/CtaBanner.vue';
import FounderBridge from '@/components/Studio/FounderBridge.vue';
import ProcessSection from '@/components/Studio/ProcessSection.vue';
import StudioHero from '@/components/Studio/StudioHero.vue';
import ValueSection from '@/components/Studio/ValueSection.vue';
import WorkSection from '@/components/Studio/WorkSection.vue';
import { route } from '@/composables/useRoute';
import { useSite } from '@/composables/useSite';
import type { HomePageProps } from '@/types';

/**
 * The studio home page (/).
 */
defineProps<HomePageProps>();

const { studio } = useSite();
</script>

<template>
    <Head :title="studio.meta.title || studio.name">
        <meta
            v-if="studio.meta.description"
            name="description"
            :content="studio.meta.description"
        />
        <meta property="og:title" :content="studio.meta.title || studio.name" />
        <meta
            v-if="studio.og_image_url"
            property="og:image"
            :content="studio.og_image_url"
        />
    </Head>

    <StudioHero :stats="stats" :ticker="content.ticker.items" />

    <ClientStrip :clients="clients" :label="content.clients.eyebrow" />

    <CapabilitiesSection :content="content.capabilities" />

    <WorkSection
        id="work"
        :eyebrow="content.work.eyebrow"
        :title="content.work.title"
        :highlight="content.work.highlight"
        :description="content.work.description"
        :projects="projects"
        :total="totalProjects"
        :archive-href="route('projectArchive', { owner: 'studio' })"
        :archive-label="content.work.archive_label"
        :empty-text="content.work.empty_text"
    />

    <ProcessSection :content="content.engineering" />

    <ValueSection :content="content.value" />

    <AboutSection :content="content.about" />

    <FounderBridge :content="content.founder_bridge" />

    <WorkSection
        v-if="founderProjects.length"
        :eyebrow="content.founder_work.eyebrow"
        :title="content.founder_work.title"
        :highlight="content.founder_work.highlight"
        :description="content.founder_work.description"
        :projects="founderProjects"
        :total="totalFounderProjects"
        :archive-href="route('founder')"
        :archive-label="content.founder_work.archive_label"
        :feature="false"
        always-show-archive
    />

    <SectionList
        :sections="sections"
        :anchor-for="(section) => `block-${section.id}`"
    />

    <CtaBanner :content="content.cta" />

    <ContactSection
        site="studio"
        :eyebrow="content.contact.eyebrow"
        :title="content.contact.title"
        :highlight="content.contact.highlight"
        :description="content.contact.description"
        :email="studio.public_email"
        :phone="studio.phone"
        :location="studio.location"
        :socials="studio.socials"
        :email-heading="content.contact.email_heading"
        :email-note="content.contact.email_note"
        :placeholder="content.contact.form_placeholder"
        :submit-label="content.contact.form_submit"
        :reply-note="content.contact.form_reply_note"
    />
</template>
