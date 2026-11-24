import { computed } from 'vue';
import { useDebouncedSearch } from '@/composables/useDebouncedSearch';
import { useReorder } from '@/composables/useReorder';
import { route } from '@/composables/useRoute';
import type { Client, ClientIndexProps, UseClientListReturn } from '@/types';

/**
 * The admin client list: the clients from the page props in their logo-strip
 * order, moving one up or down, the search box and how many are visible on
 * the public site. A search reloads only the `clients` and `filters` props.
 */
export function useClientList(props: ClientIndexProps): UseClientListReturn {
    const {
        items: clientList,
        move,
        saving,
    } = useReorder<Client>(() => props.clients, route('clientReorder'));

    const { search } = useDebouncedSearch(
        route('clientIndex'),
        props.filters.search,
        ['clients', 'filters'],
    );

    const visibleCount = computed(
        () => clientList.value.filter((client) => client.is_visible).length,
    );

    return { clientList, move, saving, search, visibleCount };
}
