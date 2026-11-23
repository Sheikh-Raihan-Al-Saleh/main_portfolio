<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SectionList from '@/components/landing/SectionList.vue';
import ContactSection from '@/components/site/ContactSection.vue';
import AboutSection from '@/components/studio/AboutSection.vue';
import CapabilitiesSection from '@/components/studio/CapabilitiesSection.vue';
import ClientStrip from '@/components/studio/ClientStrip.vue';
import CtaBanner from '@/components/studio/CtaBanner.vue';
import FounderBridge from '@/components/studio/FounderBridge.vue';
import ProcessSection from '@/components/studio/ProcessSection.vue';
import StudioHero from '@/components/studio/StudioHero.vue';
import ValueSection from '@/components/studio/ValueSection.vue';
import WorkSection from '@/components/studio/WorkSection.vue';
import { useSite } from '@/composables/useSite';
import { founder as founderRoute } from '@/routes';
import projectRoutes from '@/routes/projects';
import type { PublicHomeIndexProps } from '@/types';

/**
 * The studio home page (/).
 */
defineProps<PublicHomeIndexProps>();

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
        :archive-href="projectRoutes.index({ query: { owner: 'studio' } }).url"
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
        :archive-href="founderRoute().url"
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
