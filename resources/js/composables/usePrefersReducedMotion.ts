import { onBeforeUnmount, onMounted, ref } from 'vue';
import type { Ref } from 'vue';

/**
 * Reactive `prefers-reduced-motion` state, shared by every pointer-driven
 * composable so decorative motion can be switched off wholesale. Defaults to
 * `true` during SSR so the server never renders an animated first frame.
 */
export function usePrefersReducedMotion(): Ref<boolean> {
    const reduced = ref(true);
    let query: MediaQueryList | null = null;

    function sync(event: MediaQueryList | MediaQueryListEvent) {
        reduced.value = event.matches;
    }

    onMounted(() => {
        query = window.matchMedia('(prefers-reduced-motion: reduce)');
        sync(query);
        query.addEventListener('change', sync);
    });

    onBeforeUnmount(() => {
        query?.removeEventListener('change', sync);
        query = null;
    });

    return reduced;
}

/**
 * Coarse pointers (touch) get no hover-driven motion: there is no cursor to
 * follow, and the effects only fire on tap, which reads as a glitch.
 */
export function useFinePointer(): Ref<boolean> {
    const fine = ref(false);
    let query: MediaQueryList | null = null;

    function sync(event: MediaQueryList | MediaQueryListEvent) {
        fine.value = event.matches;
    }

    onMounted(() => {
        query = window.matchMedia('(pointer: fine)');
        sync(query);
        query.addEventListener('change', sync);
    });

    onBeforeUnmount(() => {
        query?.removeEventListener('change', sync);
        query = null;
    });

    return fine;
}
