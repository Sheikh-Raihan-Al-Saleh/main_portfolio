<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { toUrl } from '@/lib/utils';
import type { NavItem } from '@/types';

type Props = {
    items: NavItem[];
    class?: string;
};

defineProps<Props>();
</script>

<template>
    <SidebarGroup
        :class="`group-data-[collapsible=icon]:p-0 ${$props.class || ''}`"
    >
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in items" :key="item.title">
                    <SidebarMenuButton
                        as-child
                        class="flex items-center gap-2 px-3"
                    >
                        <a
                            :href="toUrl(item.href)"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <component :is="item.icon" class="size-4" />
                            <span>{{ item.title }}</span>
                        </a>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>
