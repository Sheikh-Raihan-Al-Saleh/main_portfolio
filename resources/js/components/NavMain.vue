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
import type { NavMainProps } from '@/types';

const { label = 'Platform' } = defineProps<NavMainProps>();

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel
            class="h-7 font-mono text-xs font-normal text-muted-foreground"
        >
            <span aria-hidden="true" class="mr-1.5">▾</span>{{ label }}
        </SidebarGroupLabel>
        <SidebarMenu class="gap-px">
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isCurrentUrl(item.href)"
                    :tooltip="item.title"
                    class="h-9 gap-2.5 rounded-md px-3 font-mono text-[13px] data-[active=true]:font-medium [&>svg]:text-muted-foreground data-[active=true]:[&>svg]:text-primary"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" class="size-4" />
                        <span class="truncate">{{ item.title }}</span>
                        <span
                            v-if="item.badge"
                            class="ml-auto rounded bg-primary px-1.5 font-mono text-[11px] leading-5 font-bold text-primary-foreground tabular-nums group-data-[collapsible=icon]:hidden"
                        >
                            {{ item.badge }}
                        </span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
