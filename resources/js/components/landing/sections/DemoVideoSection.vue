<script setup lang="ts">
import { Play, Volume2, VolumeX } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import BrowserFrame from '@/components/landing/frames/BrowserFrame.vue';
import SectionShell from '@/components/landing/SectionShell.vue';
import type { LandingSection } from '@/types';

type Props = {
    section: Extract<LandingSection, { type: 'demo_video' }>;
    anchor?: string;
};

const props = defineProps<Props>();

const data = computed(() => props.section.data);

const storageUrl = (path?: string | null) => (path ? `/media/${path}` : null);

const posterUrl = computed(() => storageUrl(data.value.poster_path));
const uploadUrl = computed(() => storageUrl(data.value.video_path));

/**
 * Third-party players are rendered as a facade: poster plus a play button,
 * with the iframe only injected on click. A landing page should not pay
 * YouTube's script cost — or set its cookies — for a video nobody watched.
 */
const activated = ref(false);

const embedUrl = computed(() => {
    const url = data.value.video_url;

    if (!url) {
        return null;
    }

    if (data.value.provider === 'youtube') {
        const id = youtubeId(url);

        // nocookie host: no tracking cookie until the visitor opts in by playing.
        return id
            ? `https://www.youtube-nocookie.com/embed/${id}?autoplay=1&rel=0`
            : null;
    }

    if (data.value.provider === 'vimeo') {
        const id = url.split('/').filter(Boolean).pop();

        return id ? `https://player.vimeo.com/video/${id}?autoplay=1` : null;
    }

    return null;
});

function youtubeId(url: string): string | null {
    try {
        const parsed = new URL(url);

        if (parsed.hostname === 'youtu.be') {
            return parsed.pathname.slice(1) || null;
        }

        return parsed.searchParams.get('v');
    } catch {
        return null;
    }
}

/** Fallback thumbnail when no poster was uploaded for a YouTube video. */
const facadePoster = computed(() => {
    if (posterUrl.value) {
        return posterUrl.value;
    }

    const id = data.value.video_url ? youtubeId(data.value.video_url) : null;

    return id ? `https://i.ytimg.com/vi/${id}/maxresdefault.jpg` : null;
});

// --- Uploaded video: autoplay muted while on screen, pause when it leaves. ---

const videoElement = ref<HTMLVideoElement | null>(null);
const muted = ref(true);
let observer: IntersectionObserver | null = null;

onMounted(() => {
    const element = videoElement.value;

    if (!element) {
        return;
    }

    const reduced = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;

    if (reduced || !data.value.autoplay) {
        return;
    }

    observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    // A rejected play() is fine — some browsers block it outright.
                    void element.play().catch(() => undefined);
                } else {
                    element.pause();
                }
            }
        },
        { threshold: 0.4 },
    );

    observer.observe(element);
});

onBeforeUnmount(() => {
    observer?.disconnect();
    observer = null;
});

function toggleMute() {
    const element = videoElement.value;

    if (!element) {
        return;
    }

    element.muted = !element.muted;
    muted.value = element.muted;
}
</script>

<template>
    <SectionShell
        :eyebrow="section.eyebrow"
        :heading="section.heading"
        :subheading="section.subheading"
        :anchor="props.anchor"
    >
        <figure class="space-y-4">
            <BrowserFrame :url="data.video_url">
                <!-- Uploaded file -->
                <div
                    v-if="data.provider === 'upload' && uploadUrl"
                    class="relative"
                >
                    <video
                        ref="videoElement"
                        :src="uploadUrl"
                        :poster="posterUrl ?? undefined"
                        class="aspect-video w-full bg-black object-cover"
                        preload="metadata"
                        playsinline
                        muted
                        loop
                        controls
                    />
                    <button
                        v-if="data.autoplay"
                        type="button"
                        class="absolute right-4 bottom-16 grid size-10 place-items-center rounded-full border border-border bg-card/80 backdrop-blur-sm"
                        :aria-label="muted ? 'Unmute video' : 'Mute video'"
                        @click="toggleMute"
                    >
                        <VolumeX v-if="muted" class="size-4" />
                        <Volume2 v-else class="size-4" />
                    </button>
                </div>

                <!-- Third-party player, once activated -->
                <div
                    v-else-if="activated && embedUrl"
                    class="aspect-video w-full"
                >
                    <iframe
                        :src="embedUrl"
                        class="size-full"
                        :title="section.heading ?? 'Project demo video'"
                        allow="
                            accelerometer;
                            autoplay;
                            clipboard-write;
                            encrypted-media;
                            picture-in-picture;
                        "
                        allowfullscreen
                        referrerpolicy="no-referrer"
                    />
                </div>

                <!-- Facade -->
                <button
                    v-else-if="embedUrl"
                    type="button"
                    class="group relative block aspect-video w-full bg-black"
                    @click="activated = true"
                >
                    <img
                        v-if="facadePoster"
                        :src="facadePoster"
                        alt=""
                        loading="lazy"
                        class="size-full object-cover opacity-80 transition-opacity group-hover:opacity-100"
                    />
                    <span class="absolute inset-0 grid place-items-center">
                        <span
                            class="grid size-16 place-items-center rounded-full bg-brand text-brand-foreground shadow-2xl transition-transform duration-300 group-hover:scale-110"
                        >
                            <Play class="size-6 translate-x-0.5 fill-current" />
                        </span>
                    </span>
                    <span class="sr-only">Play the demo video</span>
                </button>

                <div
                    v-else
                    class="grid aspect-video w-full place-items-center text-sm text-muted-foreground"
                >
                    No video configured for this section.
                </div>
            </BrowserFrame>

            <figcaption
                v-if="data.caption"
                class="text-center text-sm text-muted-foreground"
            >
                {{ data.caption }}
            </figcaption>
        </figure>
    </SectionShell>
</template>
