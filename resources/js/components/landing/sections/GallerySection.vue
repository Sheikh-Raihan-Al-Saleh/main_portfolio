<script setup lang="ts">
import { motion } from 'motion-v';
import { computed, ref } from 'vue';
import SectionShell from '@/components/landing/SectionShell.vue';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { inViewOnce, perspectiveCard, stagger } from '@/lib/motion';
import type { LandingSection } from '@/types';

type Props = {
    section: Extract<LandingSection, { type: 'gallery' }>;
    anchor?: string;
};
const props = defineProps<Props>();
const images = computed(() => props.section.data.images ?? []);
const openIndex = ref<number | null>(null);
const open = computed({
    get: () => openIndex.value !== null,
    set: (v: boolean) => {
        if (!v) {
            openIndex.value = null;
        }
    },
});
const activeImage = computed(() =>
    openIndex.value === null ? null : (images.value[openIndex.value] ?? null),
);
function imageUrl(path: string) {
    return `/uploads/${path}`;
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
        <motion.div
            :variants="stagger(0.06)"
            initial="hidden"
            while-in-view="visible"
            :in-view-options="inViewOnce"
            class="grid gap-4 [perspective:1200px] sm:grid-cols-2 lg:grid-cols-3"
        >
            <motion.button
                v-for="(image, index) in images"
                :key="image"
                type="button"
                :variants="perspectiveCard"
                class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-1.5 text-left shadow-sm transition [transform-style:preserve-3d] hover:shadow-xl focus-visible:ring-2 focus-visible:ring-[#2563eb] focus-visible:outline-none"
                @click="openIndex = index"
            >
                <img
                    :src="imageUrl(image)"
                    :alt="`${section.heading ?? 'Gallery'} image ${index + 1}`"
                    loading="lazy"
                    class="aspect-[4/3] w-full rounded-xl object-cover transition duration-500 group-hover:scale-[1.02]"
                />
            </motion.button>
        </motion.div>
        <Dialog v-model:open="open">
            <DialogContent class="max-w-5xl p-2">
                <DialogTitle class="sr-only">{{
                    section.heading ?? 'Gallery image'
                }}</DialogTitle>
                <img
                    v-if="activeImage"
                    :src="imageUrl(activeImage)"
                    :alt="section.heading ?? 'Gallery image'"
                    class="h-auto w-full rounded-lg"
                />
            </DialogContent>
        </Dialog>
    </SectionShell>
</template>
