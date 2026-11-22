<script setup lang="ts">
import type { InputErrorProps } from '@/types';

defineProps<InputErrorProps>();
</script>

<template>
    <div v-show="message">
        <p class="text-sm text-red-600 dark:text-red-500">
            {{ message }}
        </p>
    </div>
</template>
