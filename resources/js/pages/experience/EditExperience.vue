<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminPageHeader from '@/components/AdminPageHeader.vue';
import ExperienceForm from '@/components/Experience/ExperienceForm.vue';
import { route, routeForm } from '@/composables/useRoute';
import type { EditExperienceProps } from '@/types';

defineProps<EditExperienceProps>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Experience', href: route('experienceIndex') }],
    },
});
</script>

<template>
    <Head :title="`Edit ${experience.role}`" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="Edit role"
            :description="`${experience.role} at ${experience.company}`"
        />

        <ExperienceForm
            :experience="experience"
            :action="routeForm('experienceUpdate', experience.id)"
            submit-label="Save changes"
        />
    </div>
</template>
