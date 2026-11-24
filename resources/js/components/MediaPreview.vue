<script setup lang="ts">
import { ImageOff } from '@lucide/vue';
import { ref, watch } from 'vue';
import { cn } from '@/composables/utils';
import type { MediaPreviewProps } from '@/types';

/**
 * Preview of an uploaded photo or video, shown whole: the frame scales with
 * the available width and the media is never cropped (object-contain), so
 * portrait, landscape and transparent images all show completely.
 *
 * By default the frame takes the media's own proportions, capped in height.
 * With `fill`, the media fills a frame sized by the parent (e.g. a fixed
 * aspect-ratio gallery tile) and is letterboxed inside it.
 */
const props = withDefaults(defineProps<MediaPreviewProps>(), {
    video: false,
    alt: '',
    fill: false,
    class: undefined,
});

const failed = ref(false);

// A new file gets a fresh chance to load.
watch(
    () => props.src,
    () => {
        failed.value = false;
    },
);
</script>

<template>
    <div
        :class="
            cn(
                'flex w-full items-center justify-center overflow-hidden rounded-lg border border-border bg-muted/60',
                fill ? 'h-full' : 'max-w-md',
                props.class,
            )
        "
    >
        <div
            v-if="failed"
            class="grid min-h-32 w-full place-items-center gap-1 p-4 text-center text-xs text-muted-foreground"
        >
            <ImageOff class="size-5" aria-hidden="true" />
            This file could not be shown.
        </div>

        <video
            v-else-if="video"
            :src="src"
            controls
            playsinline
            preload="metadata"
            :aria-label="alt || 'Video preview'"
            :class="
                cn(
                    'block bg-black object-contain',
                    fill ? 'size-full' : 'max-h-80 w-full',
                )
            "
            @error="failed = true"
        />

        <img
            v-else
            :src="src"
            :alt="alt"
            loading="lazy"
            decoding="async"
            :class="
                cn(
                    'block object-contain',
                    fill ? 'size-full' : 'h-auto max-h-80 w-auto max-w-full',
                )
            "
            @error="failed = true"
        />
    </div>
</template>
