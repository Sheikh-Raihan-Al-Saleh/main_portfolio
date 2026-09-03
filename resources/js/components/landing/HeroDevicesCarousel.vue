<script setup lang="ts">
import { motion } from 'motion-v';
import { computed } from 'vue';
import DeviceFrame from '@/components/landing/frames/DeviceFrame.vue';
import { fadeUp } from '@/lib/motion';

type Props = {
    images: string[];
};

const props = defineProps<Props>();

// Devices with 3D positioning
const devices = computed(() => [
    {
        variant: 'tablet' as const,
        label: 'Tablet',
        imageIndex: 1,
        rotateY: 20,
        translateX: -120,
        zIndex: 10,
        scale: 0.9,
    },
    {
        variant: 'laptop' as const,
        label: 'Desktop',
        imageIndex: 0,
        rotateY: 0,
        translateX: 0,
        zIndex: 20,
        scale: 1,
    },
    {
        variant: 'phone' as const,
        label: 'Mobile',
        imageIndex: 2,
        rotateY: -20,
        translateX: 120,
        zIndex: 15,
        scale: 0.85,
    },
]);

function getImageForDevice(imageIndex: number) {
    return props.images[imageIndex] ?? null;
}
</script>

<template>
    <!-- 3D Device Showcase -->
    <div class="w-full flex justify-end py-8">
        <motion.div
            :variants="fadeUp"
            class="relative"
            style="perspective: 1200px; width: 100%; max-width: 900px"
        >
            <!-- Rotating 3D container - all devices rotate together -->
            <motion.div
                class="relative flex items-center justify-center"
                style="height: 500px; transform-style: preserve-3d"
                :animate="{ rotateY: 360 }"
                :transition="{
                    duration: 12,
                    repeat: Infinity,
                    ease: 'linear',
                }"
            >
                <!-- Each device positioned in 3D space -->
                <div
                    v-for="(device, index) in devices"
                    :key="device.variant"
                    class="absolute flex items-center justify-center"
                    :style="{
                        transform: `translateX(${device.translateX}px) rotateY(${device.rotateY}deg) scale(${device.scale})`,
                        zIndex: device.zIndex,
                        transformStyle: 'preserve-3d',
                    }"
                >
                    <motion.div
                        :initial="{ opacity: 0 }"
                        :animate="{ opacity: 1 }"
                        :transition="{ delay: index * 0.2, duration: 0.8 }"
                    >
                        <DeviceFrame
                            :variant="device.variant"
                            :class="[
                                device.variant === 'laptop'
                                    ? 'max-w-md'
                                    : device.variant === 'tablet'
                                        ? 'max-w-xs'
                                        : 'max-w-[260px]',
                            ]"
                        >
                            <div class="w-full bg-black">
                                <img
                                    v-if="getImageForDevice(device.imageIndex)"
                                    :src="getImageForDevice(device.imageIndex)"
                                    :alt="device.label"
                                    class="w-full h-auto block"
                                />
                                <div
                                    v-else
                                    :class="[
                                        'w-full flex items-center justify-center bg-muted',
                                        device.variant === 'phone'
                                            ? 'aspect-[9/16]'
                                            : 'aspect-video',
                                    ]"
                                >
                                    <span class="text-sm text-muted-foreground">
                                        {{ device.label }}
                                    </span>
                                </div>
                            </div>
                        </DeviceFrame>
                    </motion.div>
                </div>
            </motion.div>
        </motion.div>
    </div>
</template>

<style scoped>
/* 3D support */
:deep(div) {
    backface-visibility: hidden;
}
</style>
