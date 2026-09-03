<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { AlertTriangle, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';

type Props = {
    /** Wayfinder route definition for the destroy action. */
    url: string;
    label: string;
    description?: string;
};

const props = defineProps<Props>();

const open = ref(false);
const processing = ref(false);

function confirmDelete() {
    processing.value = true;

    router.delete(props.url, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
            open.value = false;
        },
    });
}
</script>

<template>
    <AlertDialog v-model:open="open">
        <AlertDialogTrigger as-child>
            <Button
                variant="ghost"
                size="icon-sm"
                class="text-muted-foreground transition-colors hover:bg-red-500/10 hover:text-red-600 dark:hover:text-red-400"
                :aria-label="`Delete ${label}`"
            >
                <Trash2 class="size-4" />
            </Button>
        </AlertDialogTrigger>

        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle class="flex items-center gap-2">
                    <span class="rounded-lg bg-red-500/10 p-1.5">
                        <AlertTriangle
                            class="size-4 text-red-600 dark:text-red-400"
                        />
                    </span>
                    Delete {{ label }}?
                </AlertDialogTitle>
                <AlertDialogDescription>
                    {{
                        description ??
                        'This permanently removes the record and any uploaded images. This cannot be undone.'
                    }}
                </AlertDialogDescription>
            </AlertDialogHeader>

            <AlertDialogFooter>
                <AlertDialogCancel :disabled="processing"
                    >Cancel</AlertDialogCancel
                >
                <Button
                    variant="destructive"
                    :disabled="processing"
                    @click="confirmDelete"
                    class="bg-red-600 text-white hover:bg-red-700"
                >
                    <Trash2 class="size-4" />
                    {{ processing ? 'Deleting…' : 'Delete' }}
                </Button>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
