<script setup lang="ts">
import { Eye, EyeOff, Settings2 } from '@lucide/vue';
import DeleteAction from '@/components/admin/DeleteAction.vue';
import ReorderButtons from '@/components/admin/ReorderButtons.vue';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { LandingSection } from '@/types';

/**
 * One row of a sortable section list, shared by the company home page and a
 * project's landing page. On phones the status and actions drop to a second
 * line so the title keeps the full width.
 */
type Props = {
    section: LandingSection;
    index: number;
    total: number;
    typeLabel: string;
    disabled?: boolean;
    /** What the admin calls a section on this page ("section" or "block"). */
    noun?: string;
};

const { noun = 'section', disabled = false } = defineProps<Props>();

const emit = defineEmits<{
    move: [index: number, direction: -1 | 1];
    edit: [];
}>();
</script>

<template>
    <li
        class="ui-card ui-card-hover flex flex-wrap items-center gap-x-3 gap-y-2 p-3 sm:flex-nowrap sm:gap-4 sm:p-4"
    >
        <ReorderButtons
            :index="index"
            :total="total"
            :disabled="disabled"
            @move="(i, direction) => emit('move', i, direction)"
        />

        <span
            class="grid size-9 shrink-0 place-items-center rounded-xl bg-muted text-xs font-semibold text-muted-foreground"
            >{{ index + 1 }}</span
        >

        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-semibold">
                {{ section.heading || typeLabel }}
            </p>
            <p class="truncate text-xs text-muted-foreground">
                {{ typeLabel }}
            </p>
        </div>

        <div class="flex w-full items-center gap-2 sm:w-auto sm:gap-4">
            <span
                :class="
                    cn(
                        'mr-auto inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium sm:mr-0',
                        section.is_visible
                            ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                            : 'bg-muted text-muted-foreground',
                    )
                "
            >
                <Eye v-if="section.is_visible" class="size-3.5" />
                <EyeOff v-else class="size-3.5" />
                {{ section.is_visible ? 'Visible' : 'Hidden' }}
            </span>

            <Button
                variant="outline"
                size="sm"
                :aria-label="`Edit ${typeLabel} ${noun}`"
                @click="emit('edit')"
            >
                <Settings2 class="size-4" />
                Edit
            </Button>

            <DeleteAction
                :url="`/admin/landing-sections/${section.id}`"
                :label="`Delete ${noun}`"
                :description="`This removes the ${noun} and any images it uploaded.`"
            />
        </div>
    </li>
</template>
