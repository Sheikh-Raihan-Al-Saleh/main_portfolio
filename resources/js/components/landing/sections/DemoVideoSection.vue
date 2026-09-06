<script setup lang="ts">
import { Play, Volume2, VolumeX } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import SectionShell from '@/components/landing/SectionShell.vue';
import { scaleIn } from '@/lib/motion';
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
const activated = ref(false);
const embedUrl = computed(() => {
    const url = data.value.video_url;

    if (!url) {
        return null;
    }

    if (data.value.provider === 'youtube') {
        const id = youtubeId(url);

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
const facadePoster = computed(() => {
    if (posterUrl.value) {
        return posterUrl.value;
    }

    const id = data.value.video_url ? youtubeId(data.value.video_url) : null;

    return id ? `https://i.ytimg.com/vi/${id}/maxresdefault.jpg` : null;
});
const videoElement = ref<HTMLVideoElement | null>(null);
const muted = ref(true);
let observer: IntersectionObserver | null = null;
onMounted(() => {
    const el = videoElement.value;

    if (!el) {
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
                    void el.play().catch(() => undefined);
                } else {
                    el.pause();
                }
            }
        },
        { threshold: 0.4 },
    );
    observer.observe(el);
});
onBeforeUnmount(() => {
    observer?.disconnect();
    observer = null;
});
function toggleMute() {
    const el = videoElement.value;

    if (!el) {
        return;
    }

    el.muted = !el.muted;
    muted.value = el.muted;
}
</script>

<template>
    <SectionShell
        :eyebrow="section.eyebrow"
        :heading="section.heading"
        :subheading="section.subheading"
        :anchor="props.anchor"
    >
        <motion.figure
            :variants="scaleIn"
            initial="hidden"
            while-in-view="visible"
            :in-view-options="{ once: true, margin: '0px 0px -80px 0px' }"
            class="space-y-4 [perspective:1200px]"
        >
            <div
                class="card-3d overflow-hidden p-2 [transform-style:preserve-3d]"
            >
                <div class="overflow-hidden rounded-xl bg-black">
                    <div
                        v-if="data.provider === 'upload' && uploadUrl"
                        class="relative"
                    >
                        <video
                            ref="videoElement"
                            :src="uploadUrl"
                            :poster="posterUrl ?? undefined"
                            class="aspect-video w-full object-cover"
                            preload="metadata"
                            playsinline
                            muted
                            loop
                            controls
                        />
                        <button
                            v-if="data.autoplay"
                            type="button"
                            class="absolute right-4 bottom-16 grid size-10 place-items-center rounded-full border border-white/20 bg-white/90 backdrop-blur"
                            :aria-label="muted ? 'Unmute video' : 'Mute video'"
                            @click="toggleMute"
                        >
                            <VolumeX v-if="muted" class="size-4" />
                            <Volume2 v-else class="size-4" />
                        </button>
                    </div>
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
                            class="size-full object-cover opacity-80 transition group-hover:opacity-100"
                        />
                        <span class="absolute inset-0 grid place-items-center">
                            <span
                                class="grid size-16 place-items-center rounded-full bg-white text-slate-900 shadow-2xl transition group-hover:scale-110"
                            >
                                <Play
                                    class="size-6 translate-x-0.5 fill-current"
                                />
                            </span>
                        </span>
                        <span class="sr-only">Play the demo video</span>
                    </button>
                    <div
                        v-else
                        class="grid aspect-video w-full place-items-center text-sm text-white/60"
                    >
                        No video configured for this section.
                    </div>
                </div>
            </div>
            <figcaption
                v-if="data.caption"
                class="text-center text-sm text-slate-500"
            >
                {{ data.caption }}
            </figcaption>
        </motion.figure>
    </SectionShell>
</template>
