<script setup lang="ts">
import { cn } from '@/composables/utils';
import type { FormSectionProps } from '@/types';

/**
 * A group of related fields in a card: a short title and hint on top, the
 * fields below. Keeps long forms scannable — each card answers one question.
 */

const props = withDefaults(defineProps<FormSectionProps>(), {
    title: '',
    description: '',
    columns: 1,
    class: undefined,
});
</script>

<template>
    <section :class="cn('ui-card p-5 sm:p-6', props.class)">
        <header
            v-if="title || description || $slots.actions"
            class="mb-5 flex items-start justify-between gap-4"
        >
            <div>
                <h3 v-if="title" class="text-sm font-semibold">{{ title }}</h3>
                <p
                    v-if="description"
                    class="mt-0.5 text-xs leading-relaxed text-muted-foreground"
                >
                    {{ description }}
                </p>
            </div>
            <slot name="actions" />
        </header>
        <div
            :class="
                cn(
                    'grid items-start gap-5',
                    columns === 2 && 'sm:grid-cols-2',
                    columns === 3 && 'sm:grid-cols-2 lg:grid-cols-3',
                )
            "
        >
            <slot />
        </div>
    </section>
</template>
