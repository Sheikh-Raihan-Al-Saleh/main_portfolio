<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
} from '@/components/ui/breadcrumb';
import type { BreadcrumbsProps } from '@/types';

defineProps<BreadcrumbsProps>();
</script>

<template>
    <Breadcrumb class="min-w-0">
        <BreadcrumbList
            class="flex-nowrap gap-1.5 font-mono text-xs sm:gap-1.5"
        >
            <li
                aria-hidden="true"
                class="hidden items-center gap-1.5 text-muted-foreground md:inline-flex"
            >
                admin <span class="opacity-50">/</span>
            </li>
            <template v-for="(item, index) in breadcrumbs" :key="index">
                <BreadcrumbItem class="min-w-0">
                    <BreadcrumbPage
                        v-if="index === breadcrumbs.length - 1"
                        class="truncate text-foreground lowercase"
                        >{{ item.title }}</BreadcrumbPage
                    >
                    <BreadcrumbLink v-else as-child>
                        <Link
                            :href="item.href"
                            class="truncate lowercase hover:text-primary"
                            >{{ item.title }}</Link
                        >
                    </BreadcrumbLink>
                </BreadcrumbItem>
                <li
                    v-if="index !== breadcrumbs.length - 1"
                    role="presentation"
                    aria-hidden="true"
                    class="text-muted-foreground/50"
                >
                    /
                </li>
            </template>
        </BreadcrumbList>
    </Breadcrumb>
</template>
