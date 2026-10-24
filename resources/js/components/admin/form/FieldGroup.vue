<script setup lang="ts">
import { cn } from '@/lib/utils';

/**
 * A titled group of fields without a card — for forms inside a modal, where
 * a card per group would be too heavy. Groups are separated by a rule.
 */
type Props = {
    title: string;
    description?: string;
    columns?: 1 | 2;
};

withDefaults(defineProps<Props>(), { description: '', columns: 1 });
</script>

<template>
    <fieldset class="border-b border-border pb-6 last:border-0 last:pb-0">
        <legend class="mb-4">
            <span class="text-sm font-semibold">{{ title }}</span>
            <span
                v-if="description"
                class="mt-0.5 block text-xs text-muted-foreground"
                >{{ description }}</span
            >
        </legend>
        <div
            :class="
                cn('grid items-start gap-5', columns === 2 && 'sm:grid-cols-2')
            "
        >
            <slot />
        </div>
    </fieldset>
</template>
