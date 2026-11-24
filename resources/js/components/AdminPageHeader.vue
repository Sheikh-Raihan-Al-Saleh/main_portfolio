<script setup lang="ts">
import type { AdminPageHeaderProps } from '@/types';

/**
 * The banner every admin page opens with: a prompt-style title and the
 * description as a code comment, with the page's actions on the right.
 */

defineProps<AdminPageHeaderProps>();
</script>

<template>
    <div
        class="mb-6 flex flex-wrap items-end justify-between gap-4 border-b border-border pb-5"
    >
        <div class="min-w-0 space-y-1.5">
            <p
                v-if="eyebrow"
                class="font-mono text-xs tracking-wide text-muted-foreground"
            >
                <span class="text-syntax">#</span> {{ eyebrow }}
            </p>
            <h1 class="admin-title text-2xl sm:text-[1.75rem]">
                <span class="text-primary" aria-hidden="true">$ </span>{{ title
                }}<span
                    aria-hidden="true"
                    class="ui-cursor ml-0.5 font-normal text-primary"
                    >▍</span
                >
            </h1>
            <p
                v-if="description"
                class="max-w-2xl text-sm leading-relaxed text-muted-foreground"
            >
                <span
                    class="font-mono text-muted-foreground/70"
                    aria-hidden="true"
                    >// </span
                >{{ description }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
