<script setup lang="ts">
import { motion } from 'motion-v';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import DeviceFrame from '@/components/landing/frames/DeviceFrame.vue';
import { fadeUp } from '@/lib/motion';

type Props = {
    images: string[];
};

const props = defineProps<Props>();

// Current image index
const currentIndex = ref(0);

// Auto-rotate images
let interval: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    interval = setInterval(() => {
        currentIndex.value = (currentIndex.value + 1) % props.images.length;
    }, 3000);
});

onBeforeUnmount(() => {
    if (interval) {
        clearInterval(interval);
    }
});

// Current image
const currentImage = computed(() => props.images[currentIndex.value] ?? null);

// Device labels
const deviceLabels = ['Desktop', 'Tablet', 'Mobile'];
</script>

<template>
    <!-- Simple carousel - 3 devices, same size -->
    <motion.div
        :variants="fadeUp"
        class="w-full flex justify-end"
    >
        <div class="flex items-center justify-end gap-6 py-8">
            <!-- Device 1 - Laptop -->
            <motion.div
                initial="hidden"
                animate="visible"
                :variants="fadeUp"
            >
                <DeviceFrame variant="laptop" class="max-w-xs">
                    <motion.div
                        class="w-full bg-black"
                        :key="`img-${currentIndex}`"
                        :initial="{ opacity: 0 }"
                        :animate="{ opacity: 1 }"
                        :transition="{ duration: 0.5 }"
                    >
                        <img
                            v-if="currentIndex === 0 && currentImage"
                            :src="currentImage"
                            :alt="deviceLabels[0]"
                            class="w-full h-auto block"
                        />
                        <div
                            v-else
                            class="w-full aspect-video bg-muted flex items-center justify-center"
                        >
                            <span class="text-sm text-muted-foreground">
                                {{ deviceLabels[0] }}
                            </span>
                        </div>
                    </motion.div>
                </DeviceFrame>
            </motion.div>

            <!-- Device 2 - Tablet -->
            <motion.div
                initial="hidden"
                animate="visible"
                :variants="fadeUp"
            >
                <DeviceFrame variant="tablet" class="max-w-xs">
                    <motion.div
                        class="w-full bg-black"
                        :key="`img-${currentIndex}`"
                        :initial="{ opacity: 0 }"
                        :animate="{ opacity: 1 }"
                        :transition="{ duration: 0.5 }"
                    >
                        <img
                            v-if="currentIndex === 1 && currentImage"
                            :src="currentImage"
                            :alt="deviceLabels[1]"
                            class="w-full h-auto block"
                        />
                        <div
                            v-else
                            class="w-full aspect-video bg-muted flex items-center justify-center"
                        >
                            <span class="text-sm text-muted-foreground">
                                {{ deviceLabels[1] }}
                            </span>
                        </div>
                    </motion.div>
                </DeviceFrame>
            </motion.div>

            <!-- Device 3 - Phone -->
            <motion.div
                initial="hidden"
                animate="visible"
                :variants="fadeUp"
            >
                <DeviceFrame variant="phone" class="max-w-[200px]">
                    <motion.div
                        class="w-full bg-black"
                        :key="`img-${currentIndex}`"
                        :initial="{ opacity: 0 }"
                        :animate="{ opacity: 1 }"
                        :transition="{ duration: 0.5 }"
                    >
                        <img
                            v-if="currentIndex === 2 && currentImage"
                            :src="currentImage"
                            :alt="deviceLabels[2]"
                            class="w-full h-auto block"
                        />
                        <div
                            v-else
                            class="w-full aspect-[9/16] bg-muted flex items-center justify-center"
                        >
                            <span class="text-xs text-muted-foreground">
                                {{ deviceLabels[2] }}
                            </span>
                        </div>
                    </motion.div>
                </DeviceFrame>
            </motion.div>
        </div>
    </motion.div>
</template>
