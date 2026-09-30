<script lang="ts">
import { motion } from 'motion-v';
import { computed, nextTick, ref } from 'vue';
import MotionSection from '@/components/motion/MotionSection.vue';
import ParallaxLayer from '@/components/motion/ParallaxLayer.vue';
import PerspectiveGrid from '@/components/motion/PerspectiveGrid.vue';
import Scene3D from '@/components/motion/Scene3D.vue';
import ScrollReveal from '@/components/motion/ScrollReveal.vue';
import { useStudioContent } from '@/composables/useStudioContent';
import { resolveIcon } from '@/lib/iconResolver';
import { fadeUp } from '@/lib/motion';
import type { Company } from '@/types';

/**
 * How the studio's systems are put together.
 *
 * A logo grid says which tools a team has heard of; it says nothing about
 * engineering. This shows the shape of a build instead — the stages every
 * product passes through, connected, with the data visibly moving between
 * them — and lets the visitor pick a stage to read what happens there.
 *
 * The tabs implement the standard tab pattern (arrow keys move and focus,
 * only the selected tab is in the tab order) because it is a real
 * selection control, not a set of links.
 *
 * All copy renders from `company.studio_content.engineering`, edited in the
 * admin's Home Sections page.
 */
</script>

<script setup lang="ts">
type Props = {
    company: Company;
};

const props = defineProps<Props>();

const content = useStudioContent(props.company, 'engineering');

const stages = computed(() =>
    content.value.stages.map((stage, index) => ({
        ...stage,
        key: `${stage.label}-${index}`.toLowerCase().replace(/\s+/g, '-'),
        icon: resolveIcon(stage.icon),
    })),
);

const active = ref(0);
const tabs = ref<HTMLButtonElement[]>([]);

const activeStage = computed(() => stages.value[active.value] ?? stages.value[0]);

function select(index: number, focus = false) {
    const count = stages.value.length;

    if (count === 0) {
        return;
    }

    active.value = ((index % count) + count) % count;

    if (focus) {
        nextTick(() => tabs.value[active.value]?.focus());
    }
}

function onKeydown(event: KeyboardEvent) {
    switch (event.key) {
        case 'ArrowRight':
        case 'ArrowDown':
            event.preventDefault();
            select(active.value + 1, true);
            break;
        case 'ArrowLeft':
        case 'ArrowUp':
            event.preventDefault();
            select(active.value - 1, true);
            break;
        case 'Home':
            event.preventDefault();
            select(0, true);
            break;
        case 'End':
            event.preventDefault();
            select(stages.value.length - 1, true);
            break;
        default:
            break;
    }
}

function setTabRef(element: unknown, index: number) {
    if (element instanceof HTMLButtonElement) {
        tabs.value[index] = element;
    }
}
</script>

<template>
    <MotionSection
        id="engineering"
        index="03"
        :eyebrow="content.eyebrow"
        :title="content.title"
        :highlight="content.highlight"
        :description="content.description"
    >
        <ScrollReveal :distance="36" :blur="6">
            <Scene3D :max-rotate="5" :perspective="1500">
                <div class="studio-stage overflow-hidden">
                    <!-- Depth cue: the floor grid drifts faster than the
                         content in front of it. -->
                    <ParallaxLayer
                        root-class="absolute inset-0"
                        :distance="90"
                        :fade-out="true"
                        class="opacity-70"
                    >
                        <PerspectiveGrid :size="58" :tilt="74" :duration="30" />
                    </ParallaxLayer>

                    <div class="relative">
                        <div
                            role="tablist"
                            aria-label="Engineering stages"
                            class="flex flex-col lg:flex-row lg:items-stretch"
                            @keydown="onKeydown"
                        >
                            <template
                                v-for="(stage, index) in stages"
                                :key="stage.key"
                            >
                                <button
                                    :ref="(element) => setTabRef(element, index)"
                                    type="button"
                                    role="tab"
                                    :id="`stage-tab-${stage.key}`"
                                    :aria-controls="`stage-panel-${stage.key}`"
                                    :aria-selected="active === index"
                                    :tabindex="active === index ? 0 : -1"
                                    class="flow-node group focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none lg:flex-1"
                                    @click="select(index)"
                                >
                                    <span class="studio-icon">
                                        <component
                                            :is="stage.icon"
                                            class="size-5"
                                        />
                                    </span>
                                    <span class="flex flex-col gap-0.5">
                                        <span
                                            class="font-mono text-[11px] text-muted-foreground"
                                        >
                                            {{ String(index + 1).padStart(2, '0') }}
                                        </span>
                                        <span
                                            class="text-sm font-semibold tracking-tight"
                                        >
                                            {{ stage.label }}
                                        </span>
                                        <span
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ stage.tagline }}
                                        </span>
                                    </span>
                                </button>

                                <!-- Connectors: vertical when stacked, horizontal
                                     once the pipeline can sit side by side. -->
                                <div
                                    v-if="index < stages.length - 1"
                                    aria-hidden="true"
                                    class="flow-link-vertical lg:hidden"
                                    :style="{ '--flow-delay': `${index * 0.4}s` }"
                                />
                                <div
                                    v-if="index < stages.length - 1"
                                    aria-hidden="true"
                                    class="flow-link hidden self-center lg:block"
                                    :style="{ '--flow-delay': `${index * 0.4}s` }"
                                />
                            </template>
                        </div>

                        <div
                            v-if="activeStage"
                            role="tabpanel"
                            :id="`stage-panel-${activeStage.key}`"
                            :aria-labelledby="`stage-tab-${activeStage.key}`"
                            class="mt-6 rounded-xl border border-border bg-background/85 p-5 sm:p-6"
                        >
                            <motion.div
                                :key="activeStage.key"
                                :variants="fadeUp"
                                initial="hidden"
                                animate="visible"
                                class="flex flex-col gap-4"
                            >
                                <div
                                    class="flex flex-wrap items-baseline gap-x-3 gap-y-1"
                                >
                                    <h3
                                        class="text-lg font-semibold tracking-tight"
                                    >
                                        {{ activeStage.label }}
                                    </h3>
                                    <span
                                        class="font-mono text-xs text-brand uppercase"
                                    >
                                        {{ activeStage.tagline }}
                                    </span>
                                </div>

                                <p
                                    class="max-w-3xl leading-relaxed text-pretty text-muted-foreground"
                                >
                                    {{ activeStage.detail }}
                                </p>

                                <ul class="flex flex-wrap gap-1.5">
                                    <li
                                        v-for="tech in activeStage.tech"
                                        :key="tech"
                                        class="studio-chip"
                                    >
                                        {{ tech }}
                                    </li>
                                </ul>
                            </motion.div>
                        </div>
                    </div>
                </div>
            </Scene3D>
        </ScrollReveal>
    </MotionSection>
</template>
