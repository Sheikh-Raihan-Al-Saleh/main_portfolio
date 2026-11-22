<script setup lang="ts">
import { Play, VideoOff } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { usePrefersReducedMotion } from '@/composables/usePrefersReducedMotion';
import { cn } from '@/lib/utils';

/**
 * A self-hosted video (mp4/webm upload).
 *
 * - Autoplay only when asked, only while on screen, and never when the visitor
 *   prefers reduced motion. Autoplaying video must be muted, so `muted` is set
 *   only in that case; a click-to-play video keeps its sound.
 * - `playsinline` stops iOS from forcing fullscreen.
 * - A missing or broken file shows a fallback instead of a black box.
 */
type Props = {
    src: string | null | undefined;
    poster?: string | null;
    autoplay?: boolean;
    loop?: boolean;
    ratio?: string;
    label?: string;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    poster: null,
    autoplay: false,
    loop: true,
    ratio: '16/9',
    label: 'Video',
    class: undefined,
});

const video = ref<HTMLVideoElement | null>(null);
const failed = ref(false);
const started = ref(false);
const reducedMotion = usePrefersReducedMotion();

const shouldAutoplay = computed(() => props.autoplay && !reducedMotion.value);

let observer: IntersectionObserver | null = null;

onMounted(() => {
    if (!shouldAutoplay.value || !video.value) {
        return;
    }

    observer = new IntersectionObserver(
        ([entry]) => {
            if (!video.value) {
                return;
            }

            if (entry.isIntersecting) {
                video.value.play().catch(() => {
                    // Autoplay can be refused by the browser; the controls remain.
                });
            } else {
                video.value.pause();
            }
        },
        { threshold: 0.35 },
    );
    observer.observe(video.value);
});

onBeforeUnmount(() => observer?.disconnect());

function play(): void {
    started.value = true;
    video.value?.play().catch(() => {});
}
</script>

<template>
    <div
        :class="cn('relative overflow-hidden bg-ink', props.class)"
        :style="{ aspectRatio: ratio }"
    >
        <video
            v-if="src && !failed"
            ref="video"
            class="size-full object-cover"
            :src="src"
            :poster="poster ?? undefined"
            :muted="shouldAutoplay"
            :loop="loop"
            :controls="!shouldAutoplay && started"
            playsinline
            preload="metadata"
            :aria-label="label"
            @play="started = true"
            @error="failed = true"
        />

        <button
            v-if="src && !failed && !shouldAutoplay && !started"
            type="button"
            class="group absolute inset-0 grid place-items-center bg-ink/20 transition-colors hover:bg-ink/30"
            :aria-label="`Play ${label}`"
            @click="play"
        >
            <span
                class="grid size-20 place-items-center rounded-full bg-paper text-ink shadow-xl transition-transform duration-500 group-hover:scale-110"
            >
                <Play class="ml-1 size-7 fill-current" aria-hidden="true" />
            </span>
        </button>

        <div
            v-if="!src || failed"
            class="absolute inset-0 grid place-items-center text-paper/60"
            role="img"
            :aria-label="`${label} unavailable`"
        >
            <VideoOff class="size-7" aria-hidden="true" />
        </div>
    </div>
</template>
