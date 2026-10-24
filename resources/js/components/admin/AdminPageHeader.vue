<script setup lang="ts">
import CircuitBackground from '@/components/site/CircuitBackground.vue';

/**
 * The banner every admin page opens with: title and description on a soft
 * card carrying the public site's circuit motif, with the page's actions on
 * the right.
 */
type Props = {
    title: string;
    description?: string;
    eyebrow?: string;
};

defineProps<Props>();
</script>

<template>
    <div class="ui-card relative mb-8 overflow-hidden px-6 py-7 sm:px-8">
        <div
            class="pointer-events-none absolute inset-y-0 right-0 left-1/2 [mask-image:linear-gradient(to_right,transparent,black_40%)] opacity-70"
            aria-hidden="true"
        >
            <CircuitBackground />
            <div
                class="ui-blob ui-blob-a -top-24 -right-16 size-72 opacity-25"
            />
        </div>

        <div class="relative flex flex-wrap items-end justify-between gap-4">
            <div class="space-y-2">
                <span v-if="eyebrow" class="ui-eyebrow">{{ eyebrow }}</span>
                <h1 class="admin-title text-2xl font-bold sm:text-3xl">
                    {{ title }}
                </h1>
                <p
                    v-if="description"
                    class="max-w-2xl text-sm leading-relaxed text-muted-foreground"
                >
                    {{ description }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <slot name="actions" />
            </div>
        </div>
    </div>
</template>
