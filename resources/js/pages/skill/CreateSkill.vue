<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminPageHeader from '@/components/AdminPageHeader.vue';
import SkillForm from '@/components/Skill/SkillForm.vue';
import { route, routeForm } from '@/composables/useRoute';
import type { CreateSkillProps } from '@/types';

defineProps<CreateSkillProps>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Skills', href: route('skillIndex') },
            { title: 'New skill', href: route('skillCreate') },
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
            :category-options="categoryOptions"
            :action="routeForm('skillStore')"
            submit-label="Create skill"
        />
    </div>
</template>
