<script setup lang="ts">
/**
 * The banner every admin page opens with: title and description, with the
 * page's actions on the right.
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
            class="ui-blob ui-blob-a -top-24 -right-16 size-72 opacity-15"
            aria-hidden="true"
        />

        <div class="relative flex flex-wrap items-end justify-between gap-4">
            <div class="space-y-2">
                <span v-if="eyebrow" class="ui-eyebrow">{{ eyebrow }}</span>
                <h1 class="admin-title text-3xl sm:text-4xl">
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
