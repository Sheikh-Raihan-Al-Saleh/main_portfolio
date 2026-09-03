<script setup lang="ts">
import { AnimatePresence, motion } from 'motion-v';
import { computed, ref, watch } from 'vue';
import BrowserFrame from '@/components/landing/frames/BrowserFrame.vue';
import DeviceFrame from '@/components/landing/frames/DeviceFrame.vue';
import SectionShell from '@/components/landing/SectionShell.vue';
import { usePrefersReducedMotion } from '@/composables/usePrefersReducedMotion';
import { useScrollProgress } from '@/composables/useScrollProgress';
import { cn } from '@/lib/utils';
import type { LandingSection } from '@/types';

type Props = {
    section: Extract<LandingSection, { type: 'demo_walkthrough' }>;
    anchor?: string;
};

const props = defineProps<Props>();

const data = computed(() => props.section.data);
const steps = computed(() => data.value.steps ?? []);

const reduced = usePrefersReducedMotion();

const track = ref<HTMLElement | null>(null);

/**
 * Scroll position across the step list picks the active screenshot, so the
 * visual advances as the captions scroll past it.
 */
const { value: progress } = useScrollProgress({
    target: track,
    offset: ['start 65%', 'end 85%'],
});

const activeIndex = ref(0);

watch(progress, (latest) => {
    if (reduced.value || steps.value.length === 0) {
        return;
    }

    const index = Math.floor(latest * steps.value.length);

    activeIndex.value = Math.min(Math.max(index, 0), steps.value.length - 1);
});

const activeStep = computed(() => steps.value[activeIndex.value] ?? null);

const frameComponent = computed(() =>
    data.value.frame === 'browser' ? BrowserFrame : DeviceFrame,
);

const frameProps = computed(() =>
    data.value.frame === 'browser'
        ? {}
        : { variant: data.value.frame === 'phone' ? 'phone' : 'laptop' },
);

function imageUrl(path: string): string {
    return `/media/${path}`;
}

function select(index: number) {
    activeIndex.value = index;
}
</script>

<template>
    <SectionShell
        :eyebrow="section.eyebrow"
        :heading="section.heading"
        :subheading="section.subheading"
        :anchor="props.anchor"
        muted
    >
        <!--
          Reduced motion (and no-JS scroll positions) get a plain captioned
          list: every screenshot visible, nothing pinned, nothing swapping.
        -->
        <ol v-if="reduced" class="space-y-12">
            <li v-for="(step, index) in steps" :key="index" class="space-y-4">
                <h3 class="font-display text-xl font-semibold">
                    <span class="mr-2 text-brand">{{ index + 1 }}.</span
                    >{{ step.title }}
                </h3>
                <p v-if="step.caption" class="text-muted-foreground">
                    {{ step.caption }}
                </p>
                <component :is="frameComponent" v-bind="frameProps">
                    <img
                        :src="imageUrl(step.image_path)"
                        :alt="step.title"
                        loading="lazy"
                        class="w-full"
                    />
                </component>
            </li>
        </ol>

        <div v-else class="grid gap-12 lg:grid-cols-2 lg:gap-16">
            <!-- Sticky visual -->
            <div class="order-1 lg:sticky lg:top-28 lg:order-2 lg:h-fit">
                <component :is="frameComponent" v-bind="frameProps">
                    <div class="relative aspect-16/10 w-full overflow-hidden">
                        <AnimatePresence mode="popLayout">
                            <motion.img
                                v-if="activeStep"
                                :key="activeIndex"
                                :src="imageUrl(activeStep.image_path)"
                                :alt="activeStep.title"
                                class="absolute inset-0 size-full object-cover object-top"
                                :initial="{ opacity: 0, scale: 1.02 }"
                                :animate="{ opacity: 1, scale: 1 }"
                                :exit="{ opacity: 0 }"
                                :transition="{
                                    duration: 0.35,
                                    ease: 'easeOut',
                                }"
                            />
                        </AnimatePresence>
                    </div>
                </component>
            </div>

            <!-- Scrolling steps. Buttons rather than list items so the
                 walkthrough is fully operable from the keyboard without
                 scrolling, which scroll-driven UI otherwise breaks. -->
            <ol ref="track" class="order-2 space-y-4 lg:order-1">
                <li v-for="(step, index) in steps" :key="index">
                    <button
                        type="button"
                        :aria-current="
                            index === activeIndex ? 'step' : undefined
                        "
                        :class="
                            cn(
                                'w-full rounded-xl border p-6 text-left transition-colors duration-300',
                                index === activeIndex
                                    ? 'border-brand/40 bg-card shadow-lg'
                                    : 'border-transparent opacity-60 hover:opacity-100',
                            )
                        "
                        @click="select(index)"
                        @focus="select(index)"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                :class="
                                    cn(
                                        'grid size-7 shrink-0 place-items-center rounded-full text-xs font-bold transition-colors',
                                        index === activeIndex
                                            ? 'bg-brand text-brand-foreground'
                                            : 'bg-muted text-muted-foreground',
                                    )
                                "
                            >
                                {{ index + 1 }}
                            </span>
                            <h3 class="font-display text-lg font-semibold">
                                {{ step.title }}
                            </h3>
                        </div>
                        <p
                            v-if="step.caption"
                            class="mt-3 text-sm text-muted-foreground"
                        >
                            {{ step.caption }}
                        </p>
                    </button>
                </li>
            </ol>
        </div>
    </SectionShell>
</template>
