<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { AppLayoutProps } from '@/types';

const { breadcrumbs = [] } = defineProps<AppLayoutProps>();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
