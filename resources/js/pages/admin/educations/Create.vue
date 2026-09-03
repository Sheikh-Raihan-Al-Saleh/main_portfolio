<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import EducationController from '@/actions/App/Http/Controllers/Admin/EducationController';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import educations from '@/routes/admin/educations';
import EducationForm from './EducationForm.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Education', href: educations.index() },
            { title: 'New entry', href: educations.create() },
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
            :action="EducationController.store.form()"
            submit-label="Create entry"
        />
    </div>
</template>
