import { computed, ref } from 'vue';
import type { ComputedRef, Ref } from 'vue';
import {
    useFinePointer,
    usePrefersReducedMotion,
} from '@/composables/usePrefersReducedMotion';

type MagneticOptions = {
    /** How far the element may travel from its origin, in pixels. */
    strength?: number;
    /** Distance outside the element that still counts as a pull, in pixels. */
    padding?: number;
};

/**
 * "Magnetic" pointer physics: the element leans toward the cursor while it is
 * nearby and springs back on leave. Returns raw offsets rather than a style
 * string so the caller can hand them to a `motion` component and get the
 * spring for free.
 *
 * Disabled entirely for reduced-motion users and coarse pointers, in which
 * case the offsets stay pinned at zero.
 */
export function useMagnetic(options: MagneticOptions = {}): {
    elementRef: Ref<HTMLElement | null>;
    offset: ComputedRef<{ x: number; y: number }>;
    onPointerMove: (event: PointerEvent) => void;
    onPointerLeave: () => void;
} {
    const { strength = 0.35, padding = 0 } = options;

    const elementRef = ref<HTMLElement | null>(null);
    const x = ref(0);
    const y = ref(0);

    const reduced = usePrefersReducedMotion();
    const finePointer = useFinePointer();

    const enabled = computed(() => !reduced.value && finePointer.value);

    function onPointerMove(event: PointerEvent) {
        const element = elementRef.value;

        if (!enabled.value || !element) {
            return;
        }

        const rect = element.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;

        const distanceX = event.clientX - centerX;
        const distanceY = event.clientY - centerY;

        // Clamp so a fast cursor can never fling the element across the page.
        const maxX = rect.width / 2 + padding;
        const maxY = rect.height / 2 + padding;

        x.value = clamp(distanceX * strength, -maxX, maxX);
        y.value = clamp(distanceY * strength, -maxY, maxY);
    }

    function onPointerLeave() {
        x.value = 0;
        y.value = 0;
    }

    const offset = computed(() => ({ x: x.value, y: y.value }));

    return { elementRef, offset, onPointerMove, onPointerLeave };
}

function clamp(value: number, min: number, max: number): number {
    return Math.min(Math.max(value, min), max);
}
