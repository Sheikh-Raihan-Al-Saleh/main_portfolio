import { onBeforeUnmount, onMounted } from 'vue';

/**
 * Mirrors a layout's `data-site` scope onto <body>.
 *
 * Layouts set `data-site` on their wrapper so the server-rendered HTML already
 * carries the right tokens. Dialogs, sheets and toasts portal out of that
 * wrapper into <body>, so the attribute is copied there on the client too,
 * and removed again when the layout unmounts.
 */
export function useSiteScope(site: string): void {
    onMounted(() => {
        document.body.dataset.site = site;
    });

    onBeforeUnmount(() => {
        if (document.body.dataset.site === site) {
            delete document.body.dataset.site;
        }
    });
}
