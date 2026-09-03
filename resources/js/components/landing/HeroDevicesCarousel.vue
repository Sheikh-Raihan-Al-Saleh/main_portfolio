<script setup lang="ts">
import { motion } from 'motion-v';
import { computed } from 'vue';
import DeviceFrame from '@/components/landing/frames/DeviceFrame.vue';
import { fadeUp, stagger } from '@/lib/motion';

type Props = {
    images: string[];
};

const props = defineProps<Props>();

// Ensure we have at least 3 images, fill with empty if needed
const devices = computed(() => [
    { variant: 'laptop' as const, image: props.images[0] ?? null, label: 'Desktop', delay: 0 },
    { variant: 'tablet' as const, image: props.images[1] ?? null, label: 'Tablet', delay: 0.1 },
    { variant: 'phone' as const, image: props.images[2] ?? null, label: 'Mobile', delay: 0.2 },
]);
</script>

<template>
    <!-- Rotating carousel container - all devices face forward -->
    <motion.div
        :variants="fadeUp"
        class="relative mx-auto w-full max-w-6xl"
    >
        <!-- Rotation container - spins the entire group -->
        <motion.div
            class="flex items-center justify-center gap-8 py-12 px-4"
            :animate="{ rotateY: 360 }"
            :transition="{
                duration: 20,
                repeat: Infinity,
                ease: 'linear',
            }"
            style="perspective: 1200px; transform-style: preserve-3d"
        >
            <!-- Laptop - Desktop (Left/Tall) -->
            <motion.div
                :variants="stagger(0.1)"
                initial="hidden"
                animate="visible"
                class="flex justify-center"
            >
                <div class="transform" style="transform-origin: center">
                    <DeviceFrame
                        variant="laptop"
                        class="max-w-xs"
                    >
                        <!-- Desktop/Laptop screen -->
                        <div class="w-full bg-black">
                            <img
                                v-if="devices[0].image"
                                :src="devices[0].image"
                                :alt="devices[0].label"
                                class="w-full h-auto block"
                            />
                            <div
                                v-else
                                class="w-full aspect-video bg-muted flex items-center justify-center"
                            >
                                <span class="text-sm text-muted-foreground">
                                    {{ devices[0].label }}
                                </span>
                            </div>
                        </div>
                    </DeviceFrame>
                </div>
            </motion.div>

            <!-- Tablet - Center (Medium) -->
            <motion.div
                :variants="stagger(0.2)"
                initial="hidden"
                animate="visible"
                class="flex justify-center"
            >
                <div class="transform" style="transform-origin: center">
                    <DeviceFrame
                        variant="tablet"
                        class="max-w-xs"
                    >
                        <!-- Tablet screen -->
                        <div class="w-full bg-black">
                            <img
                                v-if="devices[1].image"
                                :src="devices[1].image"
                                :alt="devices[1].label"
                                class="w-full h-auto block"
                            />
                            <div
                                v-else
                                class="w-full aspect-video bg-muted flex items-center justify-center"
                            >
                                <span class="text-sm text-muted-foreground">
                                    {{ devices[1].label }}
                                </span>
                            </div>
                        </div>
                    </DeviceFrame>
                </div>
            </motion.div>

            <!-- Mobile - Phone (Right/Small) -->
            <motion.div
                :variants="stagger(0.3)"
                initial="hidden"
                animate="visible"
                class="flex justify-center"
            >
                <div class="transform" style="transform-origin: center">
                    <DeviceFrame
                        variant="phone"
                        class="max-w-[240px]"
                    >
                        <!-- Mobile phone screen -->
                        <div class="w-full bg-black">
                            <img
                                v-if="devices[2].image"
                                :src="devices[2].image"
                                :alt="devices[2].label"
                                class="w-full h-auto block"
                            />
                            <div
                                v-else
                                class="w-full aspect-[9/16] bg-muted flex items-center justify-center"
                            >
                                <span class="text-xs text-muted-foreground">
                                    {{ devices[2].label }}
                                </span>
                            </div>
                        </div>
                    </DeviceFrame>
                </div>
            </motion.div>
        </motion.div>
    </motion.div>
</template>

<style scoped>
/* Ensure smooth 3D rotation */
:deep(div) {
    backface-visibility: hidden;
}
</style>
