<script setup lang="ts">
import BrandLogo from '@/components/brand/BrandLogo.vue';
import { useBrand } from '@/composables/useSite';

/**
 * The company brand in the admin sidebar: logo mark and name when expanded,
 * just the mark when the sidebar collapses to icons.
 */
const brand = useBrand();
</script>

<template>
    <BrandLogo variant="mark" size="sm" class="shrink-0" />
    <div
        class="grid min-w-0 flex-1 text-left group-data-[collapsible=icon]:hidden"
    >
        <span class="truncate text-sm leading-tight font-semibold">{{
            brand.name
        }}</span>
        <span class="truncate text-xs text-muted-foreground"
            >Content Studio</span
        >
    </div>
</template>
