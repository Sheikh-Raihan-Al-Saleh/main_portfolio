<script lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight } from '@lucide/vue';
import { computed } from 'vue';
import DepthCard from '@/components/motion/DepthCard.vue';
import FloatingObject from '@/components/motion/FloatingObject.vue';
import MotionSection from '@/components/motion/MotionSection.vue';
import Scene3D from '@/components/motion/Scene3D.vue';
import ScrollReveal from '@/components/motion/ScrollReveal.vue';
import { useStudioContent } from '@/composables/useStudioContent';
import { resolveIcon } from '@/lib/iconResolver';
import type { Company } from '@/types';

/**
 * What the studio actually builds.
 *
 * A list of services is the least convincing page on any studio site, so this
 * presents capability as a *product* instead: one layered stack you can look
 * into, then the surfaces the studio builds on top of it.
 *
 * Every string and item renders from `company.studio_content.capabilities`,
 * edited in the admin's Home Sections page; the composable's fallback only
 * covers callers without a company prop.
 */
</script>

<script setup lang="ts">
type Props = {
    company: Company;
};

const props = defineProps<Props>();

const content = useStudioContent(props.company, 'capabilities');

const capabilities = computed(() =>
    content.value.items.map((item, index) => ({
        ...item,
        key: `${item.icon}-${index}`,
        icon: resolveIcon(item.icon),
    })),
);

const stack = computed(() => content.value.stack);

/**
 * Printed in the panel so it reads as the studio's own statement rather than a
 * generic claim.
 */
const intro = computed(
    () => props.company.tagline ?? content.value.stack_intro,
);
</script>

<template>
    <MotionSection
        id="capabilities"
        index="01"
        :eyebrow="content.eyebrow"
        :title="content.title"
        :highlight="content.highlight"
        :description="content.description"
    >
        <!-- ── Anatomy of a product ── -->
        <ScrollReveal :distance="40" :blur="6" class="mb-10">
            <div class="studio-stage">
                <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-14">
                    <div class="flex flex-col gap-5">
                        <p
                            class="font-mono text-xs tracking-[0.18em] text-brand uppercase"
                        >
                            {{ content.kicker }}
                        </p>

                        <h3
                            class="font-display text-2xl font-bold tracking-tight text-balance sm:text-3xl"
                        >
                            {{ content.stack_heading }}
                        </h3>

                        <p
                            class="leading-relaxed text-pretty text-muted-foreground"
                        >
                            {{ intro }}
                        </p>

                        <ul class="flex flex-wrap gap-2">
                            <li
                                v-for="layer in stack"
                                :key="layer.label"
                                class="studio-chip"
                            >
                                {{ layer.label.toLowerCase() }}
                            </li>
                        </ul>

                        <Link
                            href="#contact"
                            class="btn-laravel group inline-flex w-fit items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold"
                        >
                            describe your project
                            <ArrowUpRight
                                class="size-4 text-brand transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                                aria-hidden="true"
                            />
                        </Link>
                    </div>

                    <!-- The stack itself. Placed in a scene so the slabs hold
                         real depth and lean with the pointer. -->
                    <Scene3D :max-rotate="8" :perspective="1100">
                        <FloatingObject :amplitude="9" :duration="9" :rotate="0.6">
                            <div class="studio-stack">
                                <div
                                    class="studio-stack-beam"
                                    aria-hidden="true"
                                />

                                <div
                                    v-for="(layer, index) in stack"
                                    :key="layer.label"
                                    class="studio-stack-layer"
                                    :style="{ '--layer': index }"
                                >
                                    <span class="studio-stack-label">{{
                                        layer.label
                                    }}</span>
                                    <span class="studio-stack-meta">{{
                                        layer.meta
                                    }}</span>
                                </div>
                            </div>
                        </FloatingObject>
                    </Scene3D>
                </div>
            </div>
        </ScrollReveal>

        <!-- ── The surfaces we build ── -->
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <DepthCard
                v-for="(capability, index) in capabilities"
                :key="capability.key"
                class="h-full"
            >
                <article class="studio-panel studio-panel-pad group">
                    <div class="flex h-full flex-col gap-4">
                        <div class="flex items-start justify-between gap-3">
                            <span class="studio-icon">
                                <component
                                    :is="capability.icon"
                                    class="size-5"
                                />
                            </span>
                            <span
                                class="font-mono text-xs text-muted-foreground/60"
                            >
                                {{ String(index + 1).padStart(2, '0') }}
                            </span>
                        </div>

                        <div class="flex flex-col gap-2">
                            <h3 class="text-base font-semibold tracking-tight">
                                {{ capability.title }}
                            </h3>
                            <p
                                class="text-sm leading-relaxed text-pretty text-muted-foreground"
                            >
                                {{ capability.summary }}
                            </p>
                        </div>

                        <ul class="mt-auto flex flex-wrap gap-1.5 pt-1">
                            <li
                                v-for="tech in capability.tech"
                                :key="tech"
                                class="studio-chip"
                            >
                                {{ tech }}
                            </li>
                        </ul>
                    </div>
                </article>
            </DepthCard>
        </div>

        <ScrollReveal
            :distance="20"
            :blur="0"
            :delay="0.1"
            class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-2 font-mono text-xs text-muted-foreground"
        >
            <span class="inline-flex items-center gap-2">
                {{ content.footer_note }}
            </span>
            <Link
                href="#contact"
                class="text-brand underline-offset-4 hover:underline"
            >
                {{ content.footer_link }}
            </Link>
        </ScrollReveal>
    </MotionSection>
</template>
