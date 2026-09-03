<script setup lang="ts">
import { Monitor, Moon, Sun } from '@lucide/vue';
import { computed } from 'vue';
import { useAppearance } from '@/composables/useAppearance';
import { cn } from '@/lib/utils';

const { appearance, updateAppearance } = useAppearance();

const options = [
    { value: 'light', icon: Sun, label: 'Light' },
    { value: 'dark', icon: Moon, label: 'Dark' },
    { value: 'system', icon: Monitor, label: 'System' },
] as const;

const current = computed(() => appearance.value);
</script>

<template>
    <div
        class="inline-flex items-center rounded-full border border-border/60 bg-muted/40 p-0.5"
        role="radiogroup"
        aria-label="Colour theme"
    >
        <button
            v-for="option in options"
            :key="option.value"
            type="button"
            role="radio"
            :aria-checked="current === option.value"
            :aria-label="option.label"
            :title="option.label"
            :class="
                cn(
                    'inline-flex size-7 items-center justify-center rounded-full transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none',
                    current === option.value
                        ? 'bg-background text-foreground shadow-sm'
                        : 'text-muted-foreground hover:text-foreground',
                )
            "
            @click="updateAppearance(option.value)"
        >
            <component :is="option.icon" class="size-3.5" />
        </button>
    </div>
</template>
