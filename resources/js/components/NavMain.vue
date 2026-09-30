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
        <SidebarGroupLabel
            class="mb-2 text-sm font-medium text-muted-foreground"
            >{{ label }}</SidebarGroupLabel
        >
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isCurrentUrl(item.href)"
                    :tooltip="item.title"
                    class="flex items-center gap-3 rounded-md px-3 py-2 transition-colors hover:bg-primary/20 hover:text-primary data-[state=open]:bg-primary/30 data-[state=open]:text-primary"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" class="size-4" />
                        <span>{{ item.title }}</span>
                        <span
                            v-if="item.badge"
                            class="ml-auto rounded-full bg-primary px-1.5 py-0.5 text-xs leading-none font-medium text-white tabular-nums group-data-[collapsible=icon]:hidden"
                        >
                            {{ item.badge }}
                        </span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
