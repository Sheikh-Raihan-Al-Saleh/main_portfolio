<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import EducationController from '@/actions/App/Http/Controllers/Admin/EducationController';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import educations from '@/routes/admin/educations';
import type { Education } from '@/types';
import EducationForm from './EducationForm.vue';

type Props = {
    education: Education;
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Education', href: educations.index() }],
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
            :action="EducationController.update.form(education.id)"
            submit-label="Save changes"
        />
    </div>
</template>
