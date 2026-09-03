<script setup lang="ts">
import { cn } from '@/lib/utils';

type Props = {
    variant?: 'phone' | 'laptop';
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    variant: 'phone',
    class: undefined,
});
</script>

<template>
    <div
        v-if="props.variant === 'phone'"
        :class="
            cn(
                'relative mx-auto w-full max-w-[300px] rounded-[2.25rem] border-[10px] border-border/80 bg-card p-0 shadow-2xl',
                props.class,
            )
        "
    >
        <!-- Notch. Decorative only. -->
        <div
            class="absolute top-2 left-1/2 z-10 h-1.5 w-16 -translate-x-1/2 rounded-full bg-border/80"
            aria-hidden="true"
        />
        <div class="overflow-hidden rounded-[1.6rem]">
            <slot />
        </div>
    </div>

    <div v-else :class="cn('mx-auto w-full', props.class)">
        <div
            class="overflow-hidden rounded-xl border-[8px] border-border/80 bg-card shadow-2xl"
        >
            <slot />
        </div>
        <!-- Laptop base: a trapezoid built from borders. Capped so the
                overhang never pokes past the container gutter on any viewport. -->
        <div
            class="mx-auto h-3 w-full max-w-none border-x-[14px] border-t-[12px] border-x-transparent border-t-border/80 sm:w-[108%]"
            aria-hidden="true"
        />
    </div>
</template>
