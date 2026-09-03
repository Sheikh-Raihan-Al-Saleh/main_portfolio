<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ExperienceController from '@/actions/App/Http/Controllers/Admin/ExperienceController';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import experiences from '@/routes/admin/experiences';
import type { Experience } from '@/types';
import ExperienceForm from './ExperienceForm.vue';

type Props = {
    experience: Experience;
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Experience', href: experiences.index() }],
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
            :action="ExperienceController.update.form(experience.id)"
            submit-label="Save changes"
        />
    </div>
</template>
