<script setup lang="ts">
import { computed } from 'vue';
import ContactSection from '@/components/site/ContactSection.vue';
import { contentConfig } from '@/lib/content';
import type { Profile } from '@/types';

/** The founder's contact section; copy from Admin → Founder → Content. */
type Props = {
    profile: Profile;
};

const props = defineProps<Props>();

const content = computed(() => contentConfig(props.profile));
const copy = computed(() => content.value.sections.contact);
</script>

<template>
    <ContactSection
        :eyebrow="copy.eyebrow"
        :title="copy.title"
        :highlight="copy.highlight"
        :description="copy.description"
        :email="profile.public_email"
        :phone="profile.phone"
        :location="profile.location"
        :socials="profile.socials"
        :toast-title="content.contact.toast_title"
        :toast-description="content.contact.toast_description"
    />
</template>
