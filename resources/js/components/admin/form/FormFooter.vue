<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Save } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

/**
 * The action row at the bottom of a short form. On a page it links back to
 * the list; in a modal it asks the modal to close instead.
 */
type Props = {
    processing?: boolean;
    submitLabel: string;
    mode?: 'page' | 'dialog';
    cancelHref?: string;
};

withDefaults(defineProps<Props>(), {
    processing: false,
    mode: 'page',
    cancelHref: undefined,
});

const emit = defineEmits<{ cancel: [] }>();
</script>

<template>
    <div
        :class="
            cn(
                'flex items-center justify-end gap-2',
                mode === 'dialog'
                    ? 'border-t border-border bg-muted/40 px-6 py-4'
                    : 'pt-2',
            )
        "
    >
        <Button
            v-if="mode === 'dialog'"
            type="button"
            variant="ghost"
            @click="emit('cancel')"
        >
            Cancel
        </Button>
        <Button v-else-if="cancelHref" as-child type="button" variant="ghost">
            <Link :href="cancelHref">Cancel</Link>
        </Button>
        <Button type="submit" :disabled="processing" class="min-w-32">
            <Save class="size-4" />
            {{ processing ? 'Saving…' : submitLabel }}
        </Button>
    </div>
</template>
