import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import type { ComputedRef } from 'vue';
import {
    useFinePointer,
    usePrefersReducedMotion,
} from '@/composables/usePrefersReducedMotion';

/**
 * Normalised cursor position across the viewport, as -1..1 on both axes with
 * (0, 0) at the centre. Layered decorations multiply this by their own depth
 * to produce parallax, and the hero canvas uses it to steer the camera.
 *
 * The listener is passive and attached once per caller; it is skipped entirely
 * when motion is reduced, leaving the values at the neutral centre.
 */
export function usePointerParallax(): {
    x: ComputedRef<number>;
    y: ComputedRef<number>;
} {
    const rawX = ref(0);
    const rawY = ref(0);

    const reduced = usePrefersReducedMotion();
    const finePointer = useFinePointer();

    function onPointerMove(event: PointerEvent) {
        if (reduced.value || !finePointer.value) {
            return;
        }

        rawX.value = (event.clientX / window.innerWidth) * 2 - 1;
        rawY.value = (event.clientY / window.innerHeight) * 2 - 1;
    }

    onMounted(() => {
        window.addEventListener('pointermove', onPointerMove, {
            passive: true,
        });
    });

    onBeforeUnmount(() => {
        window.removeEventListener('pointermove', onPointerMove);
    });

    return {
        x: computed(() => (reduced.value ? 0 : rawX.value)),
        y: computed(() => (reduced.value ? 0 : rawY.value)),
    };
}
