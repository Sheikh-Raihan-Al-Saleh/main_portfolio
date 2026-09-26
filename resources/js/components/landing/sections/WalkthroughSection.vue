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
function imageUrl(path: string) {
    return `/uploads/${path}`;
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
        <ol v-if="reduced" class="space-y-10">
            <li v-for="(step, index) in steps" :key="index" class="space-y-3">
                <h3 class="text-lg font-bold text-slate-900">
                    <span class="mr-2 text-[#2563eb]">{{ index + 1 }}.</span
                    >{{ step.title }}
                </h3>
                <p v-if="step.caption" class="text-sm text-slate-500">
                    {{ step.caption }}
                </p>
                <component :is="frameComponent" v-bind="frameProps"
                    ><img
                        :src="imageUrl(step.image_path)"
                        :alt="step.title"
                        loading="lazy"
                        class="w-full"
                /></component>
            </li>
        </ol>
        <div
            v-else
            class="grid gap-10 [perspective:1200px] lg:grid-cols-2 lg:gap-14"
        >
            <div
                class="order-1 [transform-style:preserve-3d] lg:sticky lg:top-28 lg:order-2 lg:h-fit"
            >
                <div class="card-3d p-2">
                    <component :is="frameComponent" v-bind="frameProps">
                        <div
                            class="relative aspect-[16/10] w-full overflow-hidden rounded-lg"
                        >
                            <AnimatePresence mode="popLayout">
                                <motion.img
                                    v-if="activeStep"
                                    :key="activeIndex"
                                    :src="imageUrl(activeStep.image_path)"
                                    :alt="activeStep.title"
                                    class="absolute inset-0 size-full object-cover object-top"
                                    :initial="{ opacity: 0, scale: 1.03 }"
                                    :animate="{ opacity: 1, scale: 1 }"
                                    :exit="{ opacity: 0 }"
                                    :transition="{
                                        duration: 0.4,
                                        ease: 'easeOut',
                                    }"
                                />
                            </AnimatePresence>
                        </div>
                    </component>
                </div>
            </div>
            <ol ref="track" class="order-2 space-y-3 lg:order-1">
                <li v-for="(step, index) in steps" :key="index">
                    <button
                        type="button"
                        :aria-current="
                            index === activeIndex ? 'step' : undefined
                        "
                        :class="
                            cn(
                                'w-full rounded-2xl border p-5 text-left transition-all duration-300 [transform-style:preserve-3d]',
                                index === activeIndex
                                    ? 'card-3d border-[#2563eb]/20 bg-white shadow-lg'
                                    : 'border-transparent bg-white/60 opacity-60 hover:bg-white hover:opacity-100',
                            )
                        "
                        @click="select(index)"
                        @focus="select(index)"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                :class="
                                    cn(
                                        'grid size-8 shrink-0 place-items-center rounded-full text-xs font-bold transition-colors',
                                        index === activeIndex
                                            ? 'bg-[#2563eb] text-white shadow-md'
                                            : 'bg-slate-100 text-slate-500',
                                    )
                                "
                            >
                                {{ index + 1 }}
                            </span>
                            <h3 class="text-base font-bold text-slate-900">
                                {{ step.title }}
                            </h3>
                        </div>
                        <p
                            v-if="step.caption"
                            class="mt-2 text-sm leading-relaxed text-slate-500"
                        >
                            {{ step.caption }}
                        </p>
                    </button>
                </li>
            </ol>
        </div>
    </SectionShell>
</template>
