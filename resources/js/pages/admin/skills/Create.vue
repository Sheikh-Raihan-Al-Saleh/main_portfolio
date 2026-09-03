<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SkillController from '@/actions/App/Http/Controllers/Admin/SkillController';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import skills from '@/routes/admin/skills';
import SkillForm from './SkillForm.vue';

type Props = {
    categories: Record<string, string>;
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Skills', href: skills.index() },
            { title: 'New skill', href: skills.create() },
        ],
    },
});
</script>

<template>
    <Head title="New skill" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="New skill"
            description="Add a technology to your stack."
        />

        <SkillForm
            :categories="categories"
            :action="SkillController.store.form()"
            submit-label="Create skill"
        />
    </div>
</template>
