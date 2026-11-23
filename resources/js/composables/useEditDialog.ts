import { ref } from 'vue';
import type { Ref } from 'vue';
import type { UseEditDialogReturn } from '@/types';

/**
 * State of an admin list's create/edit dialog. The same dialog is used for
 * both: `editing` is null when it creates a new record, and holds the record
 * when it edits one. The list stays in view behind the dialog.
 */
export function useEditDialog<T>(): UseEditDialogReturn<T> {
    const dialogOpen = ref(false);
    const editing = ref(null) as Ref<T | null>;

    function openCreate(): void {
        editing.value = null;
        dialogOpen.value = true;
    }

    function openEdit(record: T): void {
        editing.value = record;
        dialogOpen.value = true;
    }

    function closeDialog(): void {
        dialogOpen.value = false;
    }

    return { dialogOpen, editing, openCreate, openEdit, closeDialog };
}
