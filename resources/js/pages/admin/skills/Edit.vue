<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SkillController from '@/actions/App/Http/Controllers/Admin/SkillController';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import skills from '@/routes/admin/skills';
import type { Skill } from '@/types';
import SkillForm from './SkillForm.vue';

type Props = {
    skill: Skill;
    categories: Record<string, string>;
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Skills', href: skills.index() }],
    },
});
</script>

<template>
    <Head :title="`Edit ${skill.name}`" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader title="Edit skill" :description="skill.name" />

        <SkillForm
            :skill="skill"
            :categories="categories"
            :action="SkillController.update.form(skill.id)"
            submit-label="Save changes"
        />
    </div>
</template>
