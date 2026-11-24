<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminPageHeader from '@/components/AdminPageHeader.vue';
import EducationForm from '@/components/Education/EducationForm.vue';
import { route, routeForm } from '@/composables/useRoute';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Education', href: route('educationIndex') },
            { title: 'New entry', href: route('educationCreate') },
        ],
    },
});
</script>

<template>
    <Head title="New education entry" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="New education entry"
            description="Add a degree or qualification."
        />

        <EducationForm
            :action="routeForm('educationStore')"
            submit-label="Create entry"
        />
    </div>
</template>
