<script setup lang="ts">
import { motion } from 'motion-v';
import { computed } from 'vue';
import DeviceFrame from '@/components/landing/frames/DeviceFrame.vue';
import { fadeUp } from '@/lib/motion';

type Props = {
    images: string[];
};

const props = defineProps<Props>();

// Ensure we have at least 3 images, fill with empty if needed
const devices = computed(() => [
    { variant: 'laptop' as const, image: props.images[0] ?? null },
    { variant: 'tablet' as const, image: props.images[1] ?? null },
    { variant: 'phone' as const, image: props.images[2] ?? null },
]);

// Rotation positions for the 3 devices (anti-clockwise)
// Each device is 120 degrees apart (360 / 3)
const devicePositions = [
    { index: 0, rotate: 0, zIndex: 30 },      // Top (Laptop)
    { index: 1, rotate: 120, zIndex: 20 },    // Bottom-left (Tablet)
    { index: 2, rotate: 240, zIndex: 10 },    // Bottom-right (Phone)
];
</script>

<template>
    <!-- Rotating carousel container -->
    <motion.div
        :variants="fadeUp"
        class="relative mx-auto w-full max-w-3xl"
    >
        <!-- Rotation container -->
        <motion.div
            class="relative mx-auto h-[600px]"
            :animate="{ rotate: -360 }"
            :transition="{
                duration: 20,
                repeat: Infinity,
                ease: 'linear',
            }"
        >
            <!-- Individual device positions -->
            <div
                v-for="device in devicePositions"
                :key="device.index"
                class="absolute inset-0 flex items-center justify-center"
                :style="{ zIndex: device.zIndex }"
            >
                <motion.div
                    class="absolute"
                    :animate="{ rotate: device.rotate }"
                    :transition="{ duration: 0.1 }"
                    style="
                        transform-origin: 0 0;
                        left: 50%;
                        top: 50%;
                    "
                >
                    <div
                        class="absolute"
                        :style="{
                            transform: `translate(-50%, -50%) rotate(${device.rotate}deg) translateY(-200px)`,
                        }"
                    >
                        <DeviceFrame
                            :variant="devices[device.index].variant"
                            :class="[
                                device.variant === 'laptop'
                                    ? 'max-w-md'
                                    : device.variant === 'tablet'
                                        ? 'max-w-sm'
                                        : 'max-w-xs',
                            ]"
                        >
                            <img
                                v-if="devices[device.index].image"
                                :src="devices[device.index].image"
                                :alt="`Device ${device.index + 1}`"
                                class="aspect-video w-full object-cover"
                            />
                            <div
                                v-else
                                class="aspect-video w-full bg-muted"
                            />
                        </DeviceFrame>
                    </div>
                </motion.div>
            </div>
        </motion.div>

        <!-- Center dot (decorative) -->
        <div
            class="absolute inset-0 flex items-center justify-center pointer-events-none"
            aria-hidden="true"
        >
            <div
                class="size-2 rounded-full bg-brand/50"
            />
        </div>
    </motion.div>
</template>
