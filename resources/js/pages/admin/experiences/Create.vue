<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ExperienceController from '@/actions/App/Http/Controllers/Admin/ExperienceController';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import experiences from '@/routes/admin/experiences';
import ExperienceForm from './ExperienceForm.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Experience', href: experiences.index() },
            { title: 'New role', href: experiences.create() },
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
            :action="ExperienceController.store.form()"
            submit-label="Create role"
        />
    </div>
</template>
