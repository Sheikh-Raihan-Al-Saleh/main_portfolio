<script setup lang="ts">
import { ExternalLink, Maximize2, MonitorPlay } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed, ref } from 'vue';
import BrowserFrame from '@/components/landing/frames/BrowserFrame.vue';
import DeviceFrame from '@/components/landing/frames/DeviceFrame.vue';
import SectionShell from '@/components/landing/SectionShell.vue';
import { Button } from '@/components/ui/button';
import { scaleIn } from '@/lib/motion';
import { cn } from '@/lib/utils';
import type { LandingSection } from '@/types';

type Props = {
    section: Extract<LandingSection, { type: 'demo_embed' }>;
    anchor?: string;
};
const props = defineProps<Props>();
const data = computed(() => props.section.data);
const activated = ref(false);
const wrapper = ref<HTMLElement | null>(null);
const aspectClass = computed(() => {
    switch (data.value.aspect) {
        case '4/3':
            return 'aspect-4/3';
        case 'mobile':
            return 'aspect-9/16';
        default:
            return 'aspect-video';
    }
});
async function requestFullscreen() {
    const el = wrapper.value;

    if (!el?.requestFullscreen) {
        return;
    }

    try {
        await el.requestFullscreen();
    } catch {
        /* ignore */
    }
}
</script>

<template>
    <SectionShell
        :eyebrow="section.eyebrow"
        :heading="section.heading"
        :subheading="section.subheading"
        :anchor="props.anchor"
        wide
    >
        <motion.div
            ref="wrapper"
            :variants="scaleIn"
            initial="hidden"
            while-in-view="visible"
            :in-view-options="{ once: true, margin: '0px 0px -80px 0px' }"
            class="relative [perspective:1200px]"
        >
            <div class="card-3d p-2 [transform-style:preserve-3d]">
                <component
                    :is="data.chrome === 'device' ? DeviceFrame : 'div'"
                    :variant="data.chrome === 'device' ? 'phone' : undefined"
                >
                    <BrowserFrame
                        v-if="data.chrome === 'browser'"
                        :url="data.url"
                    >
                        <div
                            :class="
                                cn('relative w-full bg-slate-50', aspectClass)
                            "
                        >
                            <iframe
                                v-if="activated"
                                :src="data.url"
                                class="size-full border-0"
                                :title="section.heading ?? 'Live project demo'"
                                loading="lazy"
                                referrerpolicy="no-referrer"
                                :allowfullscreen="
                                    data.allow_fullscreen ?? false
                                "
                                sandbox="allow-scripts allow-same-origin allow-forms allow-popups"
                            />
                            <div
                                v-else
                                class="absolute inset-0 grid place-items-center bg-white"
                            >
                                <div
                                    class="flex flex-col items-center gap-3 text-center"
                                >
                                    <MonitorPlay
                                        class="size-8 text-slate-400"
                                    />
                                    <Button
                                        type="button"
                                        size="lg"
                                        class="rounded-full bg-slate-900 px-6 text-white"
                                        @click="activated = true"
                                        >Load live demo</Button
                                    >
                                    <p class="max-w-xs text-xs text-slate-500">
                                        Loads the real application from
                                        {{ data.url }} in a sandboxed frame.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </BrowserFrame>
                    <div
                        v-else
                        :class="cn('relative w-full bg-slate-50', aspectClass)"
                    >
                        <iframe
                            v-if="activated"
                            :src="data.url"
                            class="size-full border-0"
                            :title="section.heading ?? 'Live project demo'"
                            loading="lazy"
                            referrerpolicy="no-referrer"
                            :allowfullscreen="data.allow_fullscreen ?? false"
                            sandbox="allow-scripts allow-same-origin allow-forms allow-popups"
                        />
                        <div
                            v-else
                            class="absolute inset-0 grid place-items-center bg-white"
                        >
                            <Button
                                type="button"
                                size="lg"
                                class="rounded-full bg-slate-900 px-6 text-white"
                                @click="activated = true"
                                >Load live demo</Button
                            >
                        </div>
                    </div>
                </component>
            </div>
            <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                <Button
                    v-if="activated && (data.allow_fullscreen ?? false)"
                    type="button"
                    variant="outline"
                    size="sm"
                    class="rounded-full"
                    @click="requestFullscreen"
                >
                    <Maximize2 class="size-4" /> Fullscreen
                </Button>
                <Button as-child variant="ghost" size="sm" class="rounded-full">
                    <a
                        :href="data.url"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <ExternalLink class="size-4" /> Open in a new tab
                    </a>
                </Button>
            </div>
        </motion.div>
    </SectionShell>
</template>
