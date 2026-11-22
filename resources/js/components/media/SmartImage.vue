<script setup lang="ts">
import { ImageOff } from '@lucide/vue';
import { ref, watch } from 'vue';
import { cn } from '@/lib/utils';

/**
 * An image that always shows something sensible: a soft placeholder while it
 * loads, and a neutral fallback if the file is missing or broken, instead of
 * the browser's broken-image icon.
 */
type Props = {
    src: string | null | undefined;
    alt: string;
    /** CSS aspect-ratio for the frame, e.g. "16/10". Omit to size by content. */
    ratio?: string;
    /** object-fit for the image. */
    fit?: 'cover' | 'contain';
    eager?: boolean;
    class?: string;
    imgClass?: string;
};

const props = withDefaults(defineProps<Props>(), {
    ratio: undefined,
    fit: 'cover',
    eager: false,
    class: undefined,
    imgClass: undefined,
});

const loaded = ref(false);
const failed = ref(false);

// A new source gets a fresh chance to load.
watch(
    () => props.src,
    () => {
        loaded.value = false;
        failed.value = false;
    },
);
</script>

<template>
    <div
        :class="cn('relative overflow-hidden bg-paper-deep', props.class)"
        :style="ratio ? { aspectRatio: ratio } : undefined"
    >
        <img
            v-if="src && !failed"
            :src="src"
            :alt="alt"
            :loading="eager ? 'eager' : 'lazy'"
            :fetchpriority="eager ? 'high' : 'auto'"
            decoding="async"
            :class="
                cn(
                    'size-full transition-opacity duration-700',
                    fit === 'cover' ? 'object-cover' : 'object-contain',
                    loaded ? 'opacity-100' : 'opacity-0',
                    imgClass,
                )
            "
            @load="loaded = true"
            @error="failed = true"
        />

        <div
            v-if="!src || failed"
            class="absolute inset-0 grid place-items-center text-stone"
            role="img"
            :aria-label="alt"
        >
            <slot name="fallback">
                <ImageOff class="size-6 opacity-50" aria-hidden="true" />
            </slot>
        </div>

        <div
            v-else-if="!loaded"
            class="absolute inset-0 animate-pulse bg-paper-deep"
            aria-hidden="true"
        />
    </div>
</template>
