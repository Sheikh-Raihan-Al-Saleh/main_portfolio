<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ExternalLink } from '@lucide/vue';
import ProjectController from '@/actions/App/Http/Controllers/Admin/ProjectController';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import { Button } from '@/components/ui/button';
import projects from '@/routes/admin/projects';
import type { Project } from '@/types';
import ProjectForm from './ProjectForm.vue';

type Props = {
    project: Project;
    /** `ProjectContext::options()`, for the context select. */
    contexts: { value: string; label: string }[];
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Projects', href: projects.index() }],
    },
});
</script>

<template>
    <Head :title="`Edit ${project.title}`" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader title="Edit project" :description="project.title">
            <template #actions>
                <Button
                    v-if="project.is_published"
                    as-child
                    variant="outline"
                    size="sm"
                >
                    <a
                        :href="`/projects/${project.slug}`"
                        target="_blank"
                        rel="noopener"
                    >
                        <ExternalLink class="size-4" />
                        View
                    </a>
                </Button>
            </template>
        </AdminPageHeader>

        <ProjectForm
            :project="project"
            :action="ProjectController.update.form(project.slug)"
            :contexts="contexts"
            submit-label="Save changes"
        />
    </div>
</template>
