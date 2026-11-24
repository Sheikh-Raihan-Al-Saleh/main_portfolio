<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminPageHeader from '@/components/AdminPageHeader.vue';
import ProjectForm from '@/components/Project/ProjectForm.vue';
import { route, routeForm } from '@/composables/useRoute';
import type { CreateProjectProps } from '@/types';

defineProps<CreateProjectProps>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Projects', href: route('projectIndex') },
            { title: 'New project', href: route('projectCreate') },
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
            :action="routeForm('projectStore')"
            :owners="owners"
            :technology-options="technologyOptions"
            submit-label="Create project"
        />
    </div>
</template>
