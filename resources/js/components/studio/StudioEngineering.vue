<script setup lang="ts">
import { AnimatePresence, motion } from 'motion-v';
import { computed, nextTick, ref } from 'vue';
import Reveal from '@/components/site/Reveal.vue';
import SectionHeader from '@/components/site/SectionHeader.vue';
import { useStudioContent } from '@/composables/useStudioContent';
import { resolveIcon } from '@/lib/iconResolver';
import { EASE_OUT } from '@/lib/motion';
import { cn } from '@/lib/utils';
import type { Company } from '@/types';

/**
 * How it is engineered: the five stages as a pipeline of tabs, with the
 * selected stage's detail in a panel below. Arrow keys move between tabs.
 */
type Props = {
    company: Company;
};

const props = defineProps<Props>();

const content = useStudioContent(props.company, 'engineering');

const stages = computed(() => content.value.stages ?? []);
const active = ref(0);
const current = computed(() => stages.value[active.value]);
const tablist = ref<HTMLElement | null>(null);

function onKeydown(event: KeyboardEvent, index: number) {
    if (
        !['ArrowRight', 'ArrowLeft', 'ArrowDown', 'ArrowUp'].includes(event.key)
    ) {
        return;
    }

    event.preventDefault();

    const step =
        event.key === 'ArrowRight' || event.key === 'ArrowDown' ? 1 : -1;
    const next = (index + step + stages.value.length) % stages.value.length;

    active.value = next;
    nextTick(() => {
        tablist.value
            ?.querySelectorAll<HTMLElement>('[role="tab"]')
            [next]?.focus();
    });
}

const panel = {
    hidden: { opacity: 0, y: 16 },
    visible: {
        opacity: 1,
        y: 0,
        transition: { duration: 0.5, ease: EASE_OUT },
    },
    exit: { opacity: 0, y: -12, transition: { duration: 0.2 } },
};
</script>

<template>
    <section id="engineering" class="site-section scroll-mt-24 bg-card/60">
        <div class="site-container">
            <SectionHeader
                :eyebrow="content.eyebrow"
                :title="content.title"
                :highlight="content.highlight"
                :description="content.description"
            />

            <Reveal class="mt-14">
                <div
                    ref="tablist"
                    role="tablist"
                    aria-label="Engineering stages"
                    class="relative grid grid-cols-2 gap-3 sm:grid-cols-5"
                >
                    <!-- Pipeline line -->
                    <div
                        class="absolute top-7 right-[10%] left-[10%] hidden h-px bg-border sm:block"
                        aria-hidden="true"
                    >
                        <div
                            class="h-full origin-left bg-gradient-to-r from-[var(--grad-from)] to-[var(--grad-to)] transition-transform duration-700"
                            :style="{
                                transform: `scaleX(${stages.length > 1 ? active / (stages.length - 1) : 1})`,
                            }"
                        />
                        <!-- Requests flowing through the stages -->
                        <span class="ui-packet" />
                        <span class="ui-packet [animation-delay:-1.1s]" />
                        <span class="ui-packet [animation-delay:-2.2s]" />
                    </div>

                    <button
                        v-for="(stage, i) in stages"
                        :id="`stage-tab-${i}`"
                        :key="stage.label"
                        type="button"
                        role="tab"
                        :aria-selected="active === i"
                        :aria-controls="`stage-panel-${i}`"
                        :tabindex="active === i ? 0 : -1"
                        class="group relative flex flex-col items-center gap-3 rounded-2xl p-3 text-center"
                        @click="active = i"
                        @keydown="onKeydown($event, i)"
                    >
                        <span
                            :class="
                                cn(
                                    'relative grid size-14 place-items-center rounded-2xl border transition-all duration-500',
                                    i <= active
                                        ? 'border-transparent bg-gradient-to-br from-[var(--grad-from)] to-[var(--grad-to)] text-white shadow-[0_12px_28px_-12px_var(--brand-glow)]'
                                        : 'border-border bg-card text-muted-foreground group-hover:text-foreground',
                                )
                            "
                        >
                            <component
                                :is="resolveIcon(stage.icon)"
                                class="size-5"
                                aria-hidden="true"
                            />
                        </span>
                        <span
                            :class="
                                cn(
                                    'text-sm font-semibold',
                                    active === i
                                        ? 'text-foreground'
                                        : 'text-muted-foreground',
                                )
                            "
                        >
                            {{ stage.label }}
                        </span>
                        <span
                            class="hidden text-xs text-muted-foreground sm:block"
                            >{{ stage.tagline }}</span
                        >
                    </button>
                </div>

                <div class="ui-card mt-8 min-h-56 overflow-hidden p-8 lg:p-10">
                    <AnimatePresence mode="wait">
                        <motion.div
                            v-if="current"
                            :id="`stage-panel-${active}`"
                            :key="active"
                            role="tabpanel"
                            :aria-labelledby="`stage-tab-${active}`"
                            initial="hidden"
                            animate="visible"
                            exit="exit"
                            :variants="panel"
                            class="grid gap-8 lg:grid-cols-12"
                        >
                            <div class="lg:col-span-4">
                                <p class="text-sm font-medium text-brand">
                                    Stage {{ active + 1 }} of
                                    {{ stages.length }}
                                </p>
                                <h3 class="ui-display mt-2 text-3xl">
                                    {{ current.label }}
                                </h3>
                                <p class="mt-1 text-muted-foreground">
                                    {{ current.tagline }}
                                </p>
                            </div>
                            <div class="flex flex-col gap-5 lg:col-span-8">
                                <p class="text-lg leading-relaxed text-pretty">
                                    {{ current.detail }}
                                </p>
                                <ul
                                    v-if="current.tech?.length"
                                    class="flex flex-wrap gap-2"
                                >
                                    <li
                                        v-for="tech in current.tech"
                                        :key="tech"
                                        class="ui-pill px-3 py-1.5 text-sm"
                                    >
                                        {{ tech }}
                                    </li>
                                </ul>
                            </div>
                        </motion.div>
                    </AnimatePresence>
                </div>
            </Reveal>
        </div>
    </section>
</template>
