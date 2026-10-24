<script setup lang="ts">
import ContactSection from '@/components/site/ContactSection.vue';
import { useStudioContent } from '@/composables/useStudioContent';
import type { Company } from '@/types';

/** The studio's contact section; copy from Admin → Studio sections → Contact. */
type Props = {
    company: Company;
};

const props = defineProps<Props>();

const content = useStudioContent(props.company, 'contact');
</script>

<template>
    <ContactSection
        :eyebrow="content.eyebrow"
        :title="content.title"
        :highlight="content.highlight"
        :description="content.description"
        :email="company.public_email"
        :phone="company.phone"
        :location="company.location"
        :socials="company.socials"
        :message-placeholder="content.form_placeholder"
        :submit-label="content.form_submit"
        :reply-note="content.form_reply_note"
        :toast-description="`Thanks for reaching out — ${company.name} will get back to you.`"
    />
</template>
