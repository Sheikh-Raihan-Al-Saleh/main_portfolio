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
            class="relative overflow-hidden rounded-[24px] border border-slate-200 bg-slate-900 px-6 py-14 text-center shadow-2xl sm:px-10 sm:py-16"
        >
            <!-- glow -->
            <div
                class="pointer-events-none absolute inset-0"
                aria-hidden="true"
            >
                <div
                    class="absolute -top-24 right-0 size-[420px] rounded-full bg-[#2563eb]/20 blur-3xl"
                />
                <div
                    class="absolute -bottom-24 left-0 size-[380px] rounded-full bg-[#7c3aed]/15 blur-3xl"
                />
            </div>
            <div class="relative">
                <p
                    v-if="section.eyebrow"
                    class="mb-3 inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold tracking-widest text-white/80 uppercase"
                >
                    <Sparkles class="size-3.5" /> {{ section.eyebrow }}
                </p>
                <h2
                    v-if="section.heading"
                    class="mx-auto max-w-2xl text-3xl font-extrabold tracking-tight text-white sm:text-4xl"
                >
                    {{ section.heading }}
                </h2>
                <p
                    v-if="section.subheading"
                    class="mx-auto mt-3 max-w-xl text-base text-slate-300"
                >
                    {{ section.subheading }}
                </p>
                <div class="mt-8 flex justify-center">
                    <Button
                        as-child
                        size="lg"
                        class="h-11 rounded-full bg-white px-8 font-semibold text-slate-900 shadow-lg hover:bg-slate-100"
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
                <p v-if="data.note" class="mt-3 text-sm text-slate-400">
                    {{ data.note }}
                </p>
            </div>
        </motion.div>
    </SectionShell>
</template>
