<script setup lang="ts">
import { AlertCircle } from '@lucide/vue';
import { computed } from 'vue';

/**
 * Lists validation errors under a key prefix (e.g. `footer.`), for nested
 * editors whose fields have no InputError of their own.
 */
type Props = {
    errors: Record<string, string>;
    prefix: string;
};

const props = defineProps<Props>();

const messages = computed(() =>
    Object.entries(props.errors).filter(([key]) =>
        key.startsWith(props.prefix),
    ),
);
</script>

<template>
    <div
        v-if="messages.length"
        class="flex gap-3 rounded-xl border border-destructive/40 bg-destructive/5 p-3"
        role="alert"
    >
        <AlertCircle class="mt-0.5 size-4 shrink-0 text-destructive" />
        <ul class="space-y-1 text-xs text-destructive">
            <li v-for="[key, message] in messages" :key="key">{{ message }}</li>
        </ul>
    </div>
</template>
