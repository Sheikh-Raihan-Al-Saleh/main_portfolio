<script setup lang="ts">
import { ChevronDown, ChevronUp } from '@lucide/vue';
import { Button } from '@/components/ui/button';

type Props = {
    index: number;
    total: number;
    disabled?: boolean;
};

defineProps<Props>();

const emit = defineEmits<{
    move: [index: number, direction: -1 | 1];
}>();
</script>

<template>
    <div class="flex items-center">
        <Button
            variant="ghost"
            size="icon-xs"
            :disabled="index === 0 || disabled"
            aria-label="Move up"
            @click="emit('move', index, -1)"
        >
            <ChevronUp class="size-3.5" />
        </Button>
        <Button
            variant="ghost"
            size="icon-xs"
            :disabled="index === total - 1 || disabled"
            aria-label="Move down"
            @click="emit('move', index, 1)"
        >
            <ChevronDown class="size-3.5" />
        </Button>
    </div>
</template>
