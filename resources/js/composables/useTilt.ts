import { computed, ref } from 'vue';
import type { ComputedRef, Ref } from 'vue';
import {
    useFinePointer,
    usePrefersReducedMotion,
} from '@/composables/usePrefersReducedMotion';

type TiltOptions = {
    /** Maximum rotation on each axis, in degrees. */
    maxTilt?: number;
    /** Scale applied while the pointer is over the element. */
    hoverScale?: number;
};

/**
 * 3D card tilt driven by pointer position, plus the glare coordinates used by
 * the `.spotlight` utility. Rotation is inverted on the X axis so the card
 * appears to lean *toward* the cursor rather than away from it.
 */
export function useTilt(options: TiltOptions = {}): {
    elementRef: Ref<HTMLElement | null>;
    rotateX: ComputedRef<number>;
    rotateY: ComputedRef<number>;
    scale: ComputedRef<number>;
    glareStyle: ComputedRef<Record<string, string>>;
    onPointerMove: (event: PointerEvent) => void;
    onPointerEnter: () => void;
    onPointerLeave: () => void;
} {
    const { maxTilt = 8, hoverScale = 1.02 } = options;

    const elementRef = ref<HTMLElement | null>(null);
    // Pointer position within the element, 0..1 on both axes.
    const localX = ref(0.5);
    const localY = ref(0.5);
    const hovering = ref(false);

    const reduced = usePrefersReducedMotion();
    const finePointer = useFinePointer();

    const enabled = computed(() => !reduced.value && finePointer.value);

    function onPointerMove(event: PointerEvent) {
        const element = elementRef.value;

        if (!enabled.value || !element) {
            return;
        }

        const rect = element.getBoundingClientRect();
        localX.value = (event.clientX - rect.left) / rect.width;
        localY.value = (event.clientY - rect.top) / rect.height;
    }

    function onPointerEnter() {
        if (enabled.value) {
            hovering.value = true;
        }
    }

    function onPointerLeave() {
        hovering.value = false;
        localX.value = 0.5;
        localY.value = 0.5;
    }

    const rotateX = computed(() =>
        hovering.value ? (0.5 - localY.value) * 2 * maxTilt : 0,
    );
    const rotateY = computed(() =>
        hovering.value ? (localX.value - 0.5) * 2 * maxTilt : 0,
    );
    const scale = computed(() => (hovering.value ? hoverScale : 1));

    const glareStyle = computed(() => ({
        '--spot-x': `${localX.value * 100}%`,
        '--spot-y': `${localY.value * 100}%`,
    }));

    return {
        elementRef,
        rotateX,
        rotateY,
        scale,
        glareStyle,
        onPointerMove,
        onPointerEnter,
        onPointerLeave,
    };
}
