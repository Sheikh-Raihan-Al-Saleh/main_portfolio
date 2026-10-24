<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

type Props = {
    items: NavItem[];
    label?: string;
};

const { label = 'Platform' } = defineProps<Props>();

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel class="mb-1 text-muted-foreground">{{
            label
        }}</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isCurrentUrl(item.href)"
                    :tooltip="item.title"
                    class="flex items-center gap-3 px-3"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" class="size-4" />
                        <span>{{ item.title }}</span>
                        <span
                            v-if="item.badge"
                            class="ml-auto rounded-full bg-gradient-to-r from-[var(--signal)] to-[var(--grad-to)] px-1.5 py-0.5 text-[0.68rem] leading-none font-semibold text-white tabular-nums shadow-sm group-data-[collapsible=icon]:hidden"
                        >
                            {{ item.badge }}
                        </span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
