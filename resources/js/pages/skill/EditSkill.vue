<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminPageHeader from '@/components/AdminPageHeader.vue';
import SkillForm from '@/components/Skill/SkillForm.vue';
import { route, routeForm } from '@/composables/useRoute';
import type { EditSkillProps } from '@/types';

defineProps<EditSkillProps>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Skills', href: route('skillIndex') }],
    },
});
</script>

<template>
    <Head :title="`Edit ${skill.name}`" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader title="Edit skill" :description="skill.name" />

        <SkillForm
            :skill="skill"
            :category-options="categoryOptions"
            :action="routeForm('skillUpdate', skill.id)"
            submit-label="Save changes"
        />
    </div>
</template>
