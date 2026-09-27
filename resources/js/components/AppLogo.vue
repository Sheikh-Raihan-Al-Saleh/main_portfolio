<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const name = usePage().props.name;

/** Website logo from Admin → Founder; the studio logo is the fallback. */
const logoUrl = computed(
    () =>
        (
            usePage().props.profile as
                | { logo_url?: string | null }
                | undefined
        )?.logo_url ??
        (
            usePage().props.company as
                | { logo_url?: string | null }
                | undefined
        )?.logo_url ??
        null,
);
</script>

<template>
    <!--
        Wide website logo. Hidden when the sidebar collapses to icons, where
        the square mark takes over below.
    -->
    <div
        v-if="logoUrl"
        class="flex h-10 items-center overflow-hidden rounded-md bg-white px-2 ring-1 ring-sidebar-border group-data-[collapsible=icon]:hidden"
    >
        <img
            :src="logoUrl"
            alt=""
            class="h-8 w-auto max-w-36 object-contain"
        />
    </div>
    <!--
        Icon mark: the default brand, and the collapsed-sidebar fallback when a
        wide logo is set.
    -->
    <div
        class="aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
        :class="logoUrl ? 'hidden group-data-[collapsible=icon]:flex' : 'flex'"
    >
        <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
    </div>
    <div
        v-if="!logoUrl"
        class="ml-1 grid flex-1 text-left text-sm group-data-[collapsible=icon]:hidden"
    >
        <span class="mb-0.5 truncate leading-tight font-semibold">{{
            name
        }}</span>
    </div>
</template>
