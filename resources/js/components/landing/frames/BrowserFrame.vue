<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

type Props = {
    /** Shown in the fake address bar. Host only — the full URL is noise. */
    url?: string | null;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    url: null,
    class: undefined,
});

const displayUrl = computed(() => {
    if (!props.url) {
        return null;
    }

    try {
        return new URL(props.url).host;
    } catch {
        return props.url;
    }
});
</script>

<template>
    <div
        :class="
            cn(
                'overflow-hidden rounded-xl border border-border/70 bg-card shadow-2xl',
                props.class,
            )
        "
    >
        <!-- Decorative chrome: it frames the demo, it is not an interface. -->
        <div
            class="flex items-center gap-2 border-b border-border/60 bg-muted/60 px-3 py-2.5"
            aria-hidden="true"
        >
            <div class="flex gap-1.5">
                <span class="size-2.5 rounded-full bg-red-400/70" />
                <span class="size-2.5 rounded-full bg-amber-400/70" />
                <span class="size-2.5 rounded-full bg-green-400/70" />
            </div>

            <div
                v-if="displayUrl"
                class="mx-auto max-w-[60%] truncate rounded-md bg-background/70 px-3 py-1 text-xs text-muted-foreground"
            >
                {{ displayUrl }}
            </div>
        </div>

        <div class="relative">
            <slot />
        </div>
    </div>
</template>
