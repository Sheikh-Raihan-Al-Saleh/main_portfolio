<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Sparkles } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import SectionShell from '@/components/landing/SectionShell.vue';
import { Button } from '@/components/ui/button';
import { scaleIn } from '@/lib/motion';
import type { LandingSection } from '@/types';

type Props = {
    section: Extract<LandingSection, { type: 'cta' }>;
    anchor?: string;
};
const props = defineProps<Props>();
const data = computed(() => props.section.data);
const isInternal = computed(() => data.value.url.startsWith('/'));
</script>

<template>
    <SectionShell :anchor="props.anchor">
        <motion.div
            :variants="scaleIn"
            initial="hidden"
            while-in-view="visible"
            :in-view-options="{ once: true, margin: '0px 0px -80px 0px' }"
            class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-[#0a0a0a] px-6 py-16 text-center text-white shadow-none sm:px-10 sm:py-20"
        >
            <!-- glow -->
            <div
                class="pointer-events-none absolute inset-0"
                aria-hidden="true"
            >
                <div
                    class="absolute -top-24 right-0 size-[420px] rounded-full bg-white opacity-[0.07] blur-3xl"
                />
                <div
                    class="absolute -bottom-24 left-0 size-[380px] rounded-full bg-white opacity-[0.05] blur-3xl"
                />
            </div>
            <div class="relative">
                <p
                    v-if="section.eyebrow"
                    class="mb-4 inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/10 px-3 py-1 font-mono text-[11px] font-semibold tracking-[0.14em] text-white uppercase"
                >
                    <Sparkles class="size-3.5" /> {{ section.eyebrow }}
                </p>
                <h2
                    v-if="section.heading"
                    class="ui-display mx-auto max-w-3xl text-[clamp(2.25rem,5vw,4rem)] text-white"
                >
                    {{ section.heading }}
                </h2>
                <p
                    v-if="section.subheading"
                    class="mx-auto mt-5 max-w-xl text-lg leading-relaxed text-white/80"
                >
                    {{ section.subheading }}
                </p>
                <div class="mt-8 flex justify-center">
                    <Button
                        as-child
                        size="lg"
                        class="h-12 rounded-full bg-white px-8 font-semibold text-[#0a0a0a] shadow-[0_16px_40px_-12px_rgb(255_255_255/0.45)] hover:bg-white/90"
                    >
                        <Link v-if="isInternal" :href="data.url">
                            {{ data.label }} <ArrowRight class="size-4" />
                        </Link>
                        <a
                            v-else
                            :href="data.url"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {{ data.label }} <ArrowRight class="size-4" />
                        </a>
                    </Button>
                </div>
                <p v-if="data.note" class="mt-4 text-sm text-white/70">
                    {{ data.note }}
                </p>
            </div>
        </motion.div>
    </SectionShell>
</template>
