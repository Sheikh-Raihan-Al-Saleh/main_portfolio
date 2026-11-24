<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ExternalLink } from '@lucide/vue';
import AdminPageHeader from '@/components/AdminPageHeader.vue';
import ProjectForm from '@/components/Project/ProjectForm.vue';
import { Button } from '@/components/ui/button';
import { route, routeForm } from '@/composables/useRoute';
import type { EditProjectProps } from '@/types';

defineProps<EditProjectProps>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Projects', href: route('projectIndex') }],
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
            :action="routeForm('projectUpdate', project.slug)"
            :owners="owners"
            :technology-options="technologyOptions"
            submit-label="Save changes"
        />
    </div>
</template>
