<script setup lang="ts">
type Props = {
    label: string;
};

defineProps<Props>();
</script>

<template>
    <span
        class="inline-flex items-center rounded-full border border-border/70 bg-muted/50 px-2.5 py-0.5 text-xs font-medium text-muted-foreground"
    >
        {{ label }}
    </span>
</template>
