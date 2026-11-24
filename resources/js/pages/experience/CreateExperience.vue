<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminPageHeader from '@/components/AdminPageHeader.vue';
import ExperienceForm from '@/components/Experience/ExperienceForm.vue';
import { route, routeForm } from '@/composables/useRoute';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Experience', href: route('experienceIndex') },
            { title: 'New role', href: route('experienceCreate') },
        ],
    },
});
</script>

<template>
    <Head title="New role" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="New role"
            description="Add a position to your work history."
        />

        <ExperienceForm
            :action="routeForm('experienceStore')"
            submit-label="Create role"
        />
    </div>
</template>
