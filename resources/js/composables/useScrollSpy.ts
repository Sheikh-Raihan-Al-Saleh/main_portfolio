import { onBeforeUnmount, onMounted, ref } from 'vue';
import type { Ref } from 'vue';

/**
 * Tracks which of the given section ids is currently in view so the public
 * navbar can highlight the matching link. Falls back gracefully when a
 * section is absent from the page (e.g. the Projects archive page).
 */
export function useScrollSpy(ids: string[], offset = 120): Ref<string | null> {
    const active = ref<string | null>(ids[0] ?? null);
    let observer: IntersectionObserver | null = null;

    onMounted(() => {
        const sections = ids
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
    });

    onBeforeUnmount(() => {
        observer?.disconnect();
        observer = null;
    });

    return active;
}
