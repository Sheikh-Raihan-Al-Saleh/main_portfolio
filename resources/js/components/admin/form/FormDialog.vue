<script setup lang="ts">
import type { Component } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

/**
 * A modal for a short create / edit form, so the list it belongs to stays in
 * view. The form inside owns its footer; its body scrolls within the modal
 * while the header and the footer stay put.
 */
type Props = {
    title: string;
    description?: string;
    icon?: Component;
};

defineProps<Props>();

const open = defineModel<boolean>('open', { default: false });
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent
            class="flex max-h-[90svh] flex-col gap-0 overflow-hidden rounded-3xl p-0 sm:max-w-2xl"
        >
            <DialogHeader
                class="border-b border-border px-6 pt-6 pb-4 text-left"
            >
                <div class="flex items-center gap-3">
                    <span v-if="icon" class="ui-icon-tile shrink-0">
                        <component :is="icon" class="size-5" />
                    </span>
                    <div>
                        <DialogTitle class="admin-title text-lg font-bold">{{
                            title
                        }}</DialogTitle>
                        <DialogDescription v-if="description">{{
                            description
                        }}</DialogDescription>
                    </div>
                </div>
            </DialogHeader>
            <slot />
        </DialogContent>
    </Dialog>
</template>
