<script lang="ts">
import { computed } from 'vue';
import DepthCard from '@/components/motion/DepthCard.vue';
import MotionSection from '@/components/motion/MotionSection.vue';
import ScrollReveal from '@/components/motion/ScrollReveal.vue';
import { useStudioContent } from '@/composables/useStudioContent';
import { resolveIcon } from '@/lib/iconResolver';
import type { Company } from '@/types';

/**
 * Why a client should pick this studio.
 *
 * Practical, checkable claims only — the kind a reader can hold the studio to
 * later. All copy renders from `company.studio_content.value`, edited in the
 * admin's Home Sections page.
 */
</script>

<script setup lang="ts">
type Props = {
    company: Company;
};

const props = defineProps<Props>();

const content = useStudioContent(props.company, 'value');

const values = computed(() =>
    content.value.items.map((item, index) => ({
        ...item,
        key: `${item.icon}-${index}`,
        icon: resolveIcon(item.icon),
    })),
);
</script>

<template>
    <MotionSection
        id="why"
        index="04"
        :eyebrow="content.eyebrow"
        :title="content.title"
        :highlight="content.highlight"
        :description="content.description"
    >
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <DepthCard
                v-for="(value, index) in values"
                :key="value.key"
                class="h-full"
            >
                <article class="studio-panel studio-panel-pad">
                    <div class="flex h-full flex-col gap-4">
                        <div class="flex items-start justify-between gap-3">
                            <span class="studio-icon">
                                <component :is="value.icon" class="size-5" />
                            </span>
                            <span
                                class="font-mono text-xs text-muted-foreground/60"
                            >
                                {{ String(index + 1).padStart(2, '0') }}
                            </span>
                        </div>

                        <h3 class="text-base font-semibold tracking-tight">
                            {{ value.title }}
                        </h3>

                        <p
                            class="text-sm leading-relaxed text-pretty text-muted-foreground"
                        >
                            {{ value.summary }}
                        </p>
                    </div>
                </article>
            </DepthCard>
        </div>

        <!-- The promises, stated once, plainly. -->
        <ScrollReveal
            :distance="18"
            :delay="0.08"
            class="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 rounded-xl border border-dashed border-border px-5 py-4"
        >
            <span
                v-for="guarantee in content.guarantees"
                :key="guarantee"
                class="inline-flex items-center gap-2 font-mono text-xs text-muted-foreground"
            >
                <span
                    class="size-1.5 shrink-0 rounded-full bg-brand"
                    aria-hidden="true"
                />
                {{ guarantee }}
            </span>
        </ScrollReveal>
    </MotionSection>
</template>
