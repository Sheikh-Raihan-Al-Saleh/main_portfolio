import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import type { UseDebouncedSearchReturn } from '@/types';

/** How long typing must pause before the search request is sent. */
const SEARCH_DELAY_MILLISECONDS = 300;

/**
 * A search box for an admin list. Typing reloads the page with `?search=`
 * once the visitor pauses, so there is no request per keystroke. The page
 * keeps its state and scroll position, and the history entry is replaced.
 *
 * A pending search is cancelled when the page unmounts, so leaving the page
 * right after typing never navigates back to it.
 */
export function useDebouncedSearch(
    listUrl: string,
    initialSearch: string | null | undefined,
): UseDebouncedSearchReturn {
    const search = ref(initialSearch ?? '');
    let searchTimerId: ReturnType<typeof setTimeout> | null = null;

    function cancelPendingSearch(): void {
        if (searchTimerId !== null) {
            clearTimeout(searchTimerId);
            searchTimerId = null;
        }
    }

    watch(search, (searchText) => {
        cancelPendingSearch();

        searchTimerId = setTimeout(() => {
            searchTimerId = null;
            router.get(listUrl, searchText ? { search: searchText } : {}, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        }, SEARCH_DELAY_MILLISECONDS);
    });

    onBeforeUnmount(cancelPendingSearch);

    return { search };
}
