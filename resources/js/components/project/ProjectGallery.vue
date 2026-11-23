<script setup lang="ts">
import { ChevronLeft, ChevronRight, X } from '@lucide/vue';
import { AnimatePresence, motion } from 'motion-v';
import { onBeforeUnmount, ref, watch } from 'vue';
import SmartImage from '@/components/media/SmartImage.vue';
import type { ProjectGalleryProps } from '@/types';

/**
 * A project's screenshot gallery with a keyboard-friendly lightbox.
 */
const props = defineProps<ProjectGalleryProps>();

const active = ref<number | null>(null);

function open(index: number): void {
    active.value = index;
}

function step(direction: 1 | -1): void {
    if (active.value === null) {
        return;
    }

    const count = props.images.length;
    active.value = (active.value + direction + count) % count;
}

function onKey(event: KeyboardEvent): void {
    if (active.value === null) {
        return;
    }

    if (event.key === 'Escape') {
        active.value = null;
    } else if (event.key === 'ArrowRight') {
        step(1);
    } else if (event.key === 'ArrowLeft') {
        step(-1);
    }
}

watch(active, (value) => {
    document.documentElement.style.overflow = value === null ? '' : 'hidden';

    if (value === null) {
        window.removeEventListener('keydown', onKey);
    } else {
        window.addEventListener('keydown', onKey);
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKey);
    document.documentElement.style.overflow = '';
});
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <button
            v-for="(image, imageIndex) in images"
            :key="image.id"
            type="button"
            class="group relative overflow-hidden rounded-2xl text-left"
            :class="imageIndex % 3 === 0 ? 'sm:col-span-2' : ''"
            :aria-label="`Open image ${imageIndex + 1}: ${image.alt}`"
            @click="open(imageIndex)"
        >
            <SmartImage
                :src="image.url"
                :alt="image.alt"
                :ratio="imageIndex % 3 === 0 ? '16/9' : '4/3'"
                img-class="transition-transform duration-700 group-hover:scale-[1.03]"
            />
            <span v-if="image.caption" class="meta mt-3 block">{{
                image.caption
            }}</span>
        </button>
    </div>

    <Teleport to="body">
        <AnimatePresence>
            <motion.div
                v-if="active !== null"
                class="fixed inset-0 z-[120] flex items-center justify-center bg-ink/95 p-4 sm:p-10"
                role="dialog"
                aria-modal="true"
                aria-label="Image viewer"
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :exit="{ opacity: 0 }"
                @click.self="active = null"
            >
                <img
                    :src="images[active].url"
                    :alt="images[active].alt"
                    class="max-h-full max-w-full rounded-lg object-contain"
                />

                <button
                    type="button"
                    class="absolute top-4 right-4 grid size-12 place-items-center rounded-full bg-paper text-ink"
                    aria-label="Close"
                    @click="active = null"
                >
                    <X class="size-5" />
                </button>

                <template v-if="images.length > 1">
                    <button
                        type="button"
                        class="absolute left-4 grid size-12 place-items-center rounded-full bg-paper/90 text-ink"
                        aria-label="Previous image"
                        @click="step(-1)"
                    >
                        <ChevronLeft class="size-5" />
                    </button>
                    <button
                        type="button"
                        class="absolute right-4 grid size-12 place-items-center rounded-full bg-paper/90 text-ink"
                        aria-label="Next image"
                        @click="step(1)"
                    >
                        <ChevronRight class="size-5" />
                    </button>
                </template>
            </motion.div>
        </AnimatePresence>
    </Teleport>
</template>
