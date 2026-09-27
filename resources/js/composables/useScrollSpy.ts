import { onBeforeUnmount, onMounted, ref, toValue, watch } from 'vue';
import type { MaybeRefOrGetter, Ref } from 'vue';

/**
 * Tracks which of the given section ids is currently in view so the public
 * navbar can highlight the matching link. Falls back gracefully when a
 * section is absent from the page (e.g. the Projects archive page).
 *
 * The list is accepted as a getter because the public layout shows different
 * sections depending on whether it is rendering the company site or the
 * founder's, and the layout survives Inertia navigations.
 */
export function useScrollSpy(
    ids: MaybeRefOrGetter<string[]>,
    offset = 120,
): Ref<string | null> {
    // Nothing is highlighted until a section is actually seen. Guessing the
    // first id lit a link on pages that do not even contain that section.
    const active = ref<string | null>(null);
    let observer: IntersectionObserver | null = null;

    function disconnect() {
        observer?.disconnect();
        observer = null;
    }

    function observe() {
        disconnect();

        const sections = toValue(ids)
            .map((id) => document.getElementById(id))
            .filter((el): el is HTMLElement => el !== null);

        if (sections.length === 0) {
            return;
        }

        observer = new IntersectionObserver(
            (entries) => {
                // Pick the entry nearest the top of the viewport that is visible.
                const visible = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort(
                        (a, b) =>
                            a.boundingClientRect.top - b.boundingClientRect.top,
                    );

                if (visible[0]) {
                    active.value = visible[0].target.id;
                }
            },
            {
                rootMargin: `-${offset}px 0px -55% 0px`,
                threshold: 0,
            },
        );

        sections.forEach((section) => observer?.observe(section));
    }

    onMounted(observe);

    // Sections are rendered by the page, so re-scan once the DOM catches up
    // with a change of route or of the observed id list.
    watch(() => toValue(ids), observe);

    onBeforeUnmount(disconnect);

    return active;
}
