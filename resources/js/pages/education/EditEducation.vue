<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminPageHeader from '@/components/AdminPageHeader.vue';
import EducationForm from '@/components/Education/EducationForm.vue';
import { route, routeForm } from '@/composables/useRoute';
import type { EditEducationProps } from '@/types';

defineProps<EditEducationProps>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Education', href: route('educationIndex') }],
    },
});
</script>

<template>
    <Head :title="`Edit ${education.degree}`" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="Edit education entry"
            :description="`${education.degree} — ${education.institution}`"
        />

        <EducationForm
            :education="education"
            :action="routeForm('educationUpdate', education.id)"
            submit-label="Save changes"
        />
    </div>
</template>
