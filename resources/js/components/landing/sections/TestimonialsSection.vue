<script setup lang="ts">
import { Quote } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import SectionShell from '@/components/landing/SectionShell.vue';
import { inViewOnce, perspectiveCard, stagger } from '@/lib/motion';
import type { LandingSection } from '@/types';

type Props = {
    section: Extract<LandingSection, { type: 'testimonials' }>;
    anchor?: string;
};
const props = defineProps<Props>();
const items = computed(() => props.section.data.items ?? []);
function initials(name: string) {
    return name
        .split(' ')
        .slice(0, 2)
        .map((p) => p.charAt(0).toUpperCase())
        .join('');
}
</script>

<template>
    <SectionShell
        :eyebrow="section.eyebrow"
        :heading="section.heading"
        :subheading="section.subheading"
        :anchor="props.anchor"
    >
        <motion.div
            :variants="stagger(0.07)"
            initial="hidden"
            while-in-view="visible"
            :in-view-options="inViewOnce"
            class="grid gap-5 [perspective:1200px] md:grid-cols-2 lg:grid-cols-3"
        >
            <motion.div
                v-for="(item, idx) in items"
                :key="idx"
                :variants="perspectiveCard"
                class="[transform-style:preserve-3d]"
            >
                <figure class="card-3d flex h-full flex-col p-6">
                    <div class="mb-3 flex items-center gap-2">
                        <span
                            class="grid size-8 place-items-center rounded-full bg-slate-900 text-white"
                            ><Quote class="size-4"
                        /></span>
                        <span class="flex gap-0.5 text-amber-400">
                            <span v-for="i in 5" :key="i">★</span>
                        </span>
                    </div>
                    <blockquote
                        class="flex-1 text-sm leading-relaxed text-slate-600"
                    >
                        “{{ item.quote }}”
                    </blockquote>
                    <figcaption
                        class="mt-5 flex items-center gap-3 border-t border-slate-100 pt-4"
                    >
                        <img
                            v-if="item.avatar_path"
                            :src="`/uploads/${item.avatar_path}`"
                            :alt="item.name"
                            loading="lazy"
                            class="size-9 rounded-full object-cover"
                        />
                        <span
                            v-else
                            class="grid size-9 place-items-center rounded-full bg-slate-900 text-xs font-bold text-white"
                            >{{ initials(item.name) }}</span
                        >
                        <span>
                            <span
                                class="block text-sm font-semibold text-slate-900"
                                >{{ item.name }}</span
                            >
                            <span
                                v-if="item.role"
                                class="block text-xs text-slate-500"
                                >{{ item.role }}</span
                            >
                        </span>
                    </figcaption>
                </figure>
            </motion.div>
        </motion.div>
    </SectionShell>
</template>
