import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import type { Ref } from 'vue';
import { reorder } from '@/routes/admin';

type Identifiable = { id: number };

/** The sortable lists (App\Services\ReorderService::TYPES). */
export type ReorderList =
    | 'projects'
    | 'project-images'
    | 'landing-sections'
    | 'clients'
    | 'skill-categories'
    | 'skills'
    | 'experiences'
    | 'educations';

/**
 * Keeps a locally reorderable copy of a list and saves the new order through
 * the shared reorder endpoint. The list updates optimistically so the move
 * feels instant.
 */
export function useReorder<T extends Identifiable>(
    source: () => T[],
    list: ReorderList,
): {
    items: Ref<T[]>;
    move: (index: number, direction: -1 | 1) => void;
    saving: Ref<boolean>;
} {
    const items = ref([...source()]) as Ref<T[]>;
    const saving = ref(false);

    // Re-sync whenever the server sends a fresh list (create, delete, filter).
    watch(source, (next) => {
        items.value = [...next];
    });

    function move(index: number, direction: -1 | 1) {
        const target = index + direction;

        if (target < 0 || target >= items.value.length) {
            return;
        }

        const next = [...items.value];
        const [moved] = next.splice(index, 1);
        next.splice(target, 0, moved);
        items.value = next;

        saving.value = true;

        router.post(
            reorder(list).url,
            { ids: next.map((item) => item.id) },
            {
                preserveScroll: true,
                preserveState: true,
                onFinish: () => {
                    saving.value = false;
                },
            },
        );
    }

    return { items, move, saving };
}
