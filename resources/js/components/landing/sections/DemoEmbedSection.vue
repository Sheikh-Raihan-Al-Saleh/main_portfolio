<script setup lang="ts">
import { ExternalLink, Maximize2, MonitorPlay } from '@lucide/vue';
import { computed, ref } from 'vue';
import BrowserFrame from '@/components/landing/frames/BrowserFrame.vue';
import DeviceFrame from '@/components/landing/frames/DeviceFrame.vue';
import SectionShell from '@/components/landing/SectionShell.vue';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { LandingSection } from '@/types';

type Props = {
    section: Extract<LandingSection, { type: 'demo_embed' }>;
    anchor?: string;
};

const props = defineProps<Props>();

const data = computed(() => props.section.data);

/**
 * The iframe is only mounted after an explicit click. Framing a third-party
 * origin on page load costs a full extra page load and hands that origin the
 * visitor's request — an opt-in is both faster and more considerate.
 */
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
    const element = wrapper.value;

    if (!element?.requestFullscreen) {
        return;
    }

    try {
        await element.requestFullscreen();
    } catch {
        // Denied by the browser or already exiting; nothing to recover from.
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
        <div ref="wrapper" class="relative">
            <component
                :is="data.chrome === 'device' ? DeviceFrame : 'div'"
                :variant="data.chrome === 'device' ? 'phone' : undefined"
            >
                <BrowserFrame v-if="data.chrome === 'browser'" :url="data.url">
                    <div
                        :class="cn('relative w-full bg-black/40', aspectClass)"
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
                            class="absolute inset-0 grid place-items-center"
                        >
                            <div
                                class="flex flex-col items-center gap-3 text-center"
                            >
                                <MonitorPlay
                                    class="size-8 text-muted-foreground"
                                />
                                <Button
                                    type="button"
                                    size="lg"
                                    @click="activated = true"
                                >
                                    Load live demo
                                </Button>
                                <p
                                    class="max-w-xs text-xs text-muted-foreground"
                                >
                                    Loads the real application from
                                    {{ data.url }} in a sandboxed frame.
                                </p>
                            </div>
                        </div>
                    </div>
                </BrowserFrame>

                <div
                    v-else
                    :class="cn('relative w-full bg-black/40', aspectClass)"
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
                        class="absolute inset-0 grid place-items-center"
                    >
                        <Button
                            type="button"
                            size="lg"
                            @click="activated = true"
                        >
                            Load live demo
                        </Button>
                    </div>
                </div>
            </component>

            <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                <Button
                    v-if="activated && (data.allow_fullscreen ?? false)"
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="requestFullscreen"
                >
                    <Maximize2 class="size-4" />
                    Fullscreen
                </Button>

                <Button as-child variant="ghost" size="sm">
                    <a
                        :href="data.url"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <ExternalLink class="size-4" />
                        Open in a new tab
                    </a>
                </Button>
            </div>
        </div>
    </SectionShell>
</template>
