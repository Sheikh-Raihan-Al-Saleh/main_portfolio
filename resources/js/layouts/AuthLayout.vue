<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthEditorialLayout.vue';
import type { AuthLayoutProps } from '@/types';

const { title = '', description = '' } = defineProps<AuthLayoutProps>();
</script>

<template>
    <AuthLayout :title="title" :description="description">
        <slot />
    </AuthLayout>
</template>
