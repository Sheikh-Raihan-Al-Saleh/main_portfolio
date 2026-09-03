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
    { variant: 'laptop' as const, image: props.images[0] ?? null, label: 'Desktop' },
    { variant: 'tablet' as const, image: props.images[1] ?? null, label: 'Tablet' },
    { variant: 'phone' as const, image: props.images[2] ?? null, label: 'Mobile' },
]);

// Rotation positions for the 3 devices (anti-clockwise)
// Each device is 120 degrees apart (360 / 3)
const devicePositions = [
    { index: 0, angle: 0, zIndex: 30 },      // Top (Laptop)
    { index: 1, angle: 120, zIndex: 20 },    // Bottom-left (Tablet)
    { index: 2, angle: 240, zIndex: 10 },    // Bottom-right (Phone)
];

// Calculate the position of each device based on angle
function getDevicePosition(angle: number) {
    const radius = 180; // Distance from center
    const radians = (angle * Math.PI) / 180;
    const x = Math.cos(radians) * radius;
    const y = Math.sin(radians) * radius;
    return { x, y };
}
</script>

<template>
    <!-- Rotating carousel container -->
    <motion.div
        :variants="fadeUp"
        class="relative mx-auto w-full max-w-4xl"
    >
        <!-- Rotation container -->
        <motion.div
            class="relative mx-auto"
            style="height: 700px"
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
                class="absolute"
                :style="{
                    zIndex: device.zIndex,
                    left: '50%',
                    top: '50%',
                    transform: `translate(calc(-50% + ${getDevicePosition(device.angle).x}px), calc(-50% + ${getDevicePosition(device.angle).y}px))`,
                }"
            >
                <div class="flex items-center justify-center">
                    <DeviceFrame
                        :variant="devices[device.index].variant"
                        :class="[
                            device.variant === 'laptop'
                                ? 'max-w-sm'
                                : device.variant === 'tablet'
                                    ? 'max-w-xs'
                                    : 'max-w-[240px]',
                        ]"
                    >
                        <img
                            v-if="devices[device.index].image"
                            :src="devices[device.index].image"
                            :alt="`${devices[device.index].label} mockup`"
                            class="aspect-video w-full object-cover"
                            loading="lazy"
                        />
                        <div
                            v-else
                            class="aspect-video w-full bg-muted flex items-center justify-center"
                        >
                            <span class="text-xs text-muted-foreground">
                                {{ devices[device.index].label }}
                            </span>
                        </div>
                    </DeviceFrame>
                </div>
            </div>
        </motion.div>

        <!-- Center dot (decorative) -->
        <div
            class="absolute inset-0 flex items-center justify-center pointer-events-none"
            aria-hidden="true"
        >
            <div class="size-3 rounded-full bg-brand/60 shadow-lg" />
        </div>
    </motion.div>
</template>

<style scoped>
/* Ensure smooth perspective for 3D effect */
:deep(.relative) {
    perspective: 1000px;
}
</style>
