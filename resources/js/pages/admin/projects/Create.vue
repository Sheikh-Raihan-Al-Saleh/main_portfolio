<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ProjectController from '@/actions/App/Http/Controllers/Admin/ProjectController';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import projects from '@/routes/admin/projects';
import ProjectForm from './ProjectForm.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Projects', href: projects.index() },
            { title: 'New project', href: projects.create() },
        ],
    },
});
</script>

<template>
    <Head title="New project" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="New project"
            description="Add a project to your portfolio."
        />

        <ProjectForm
            :action="ProjectController.store.form()"
            submit-label="Create project"
        />
    </div>
</template>
