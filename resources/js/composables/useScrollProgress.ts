import type { MaybeComputedElementRef } from '@vueuse/core';
import { useMotionValueEvent, useScroll, useSpring } from 'motion-v';
import type { MotionValue } from 'motion-v';
import { ref } from 'vue';
import type { Ref } from 'vue';

type ScrollProgressOptions = {
    /** Element to track. Omit to track the whole page. */
    target?: MaybeComputedElementRef;
    /**
     * Where tracking starts and ends relative to the target and viewport.
     * Defaults to "from the moment the element's top hits the viewport bottom
     * until its bottom hits the viewport top".
     */
    offset?: ['start end', 'end start'] | [string, string];
    /** Smooth the value with a spring. Off by default — scrubbing should be exact. */
    smooth?: boolean;
};

/**
 * Scroll progress as both a `MotionValue` (for binding straight to `motion`
 * styles without re-rendering) and a plain reactive ref (for `v-if`, indices
 * and anything that needs to read the number in template logic).
 */
export function useScrollProgress(options: ScrollProgressOptions = {}): {
    progress: MotionValue<number>;
    value: Ref<number>;
} {
    const {
        target,
        offset = ['start end', 'end start'],
        smooth = false,
    } = options;

    const { scrollYProgress } = useScroll({
        ...(target ? { target } : {}),
        offset: offset as UseScrollOffset,
    });

    const progress = smooth
        ? useSpring(scrollYProgress, { stiffness: 120, damping: 24, mass: 0.4 })
        : scrollYProgress;

    const value = ref(0);

    useMotionValueEvent(progress, 'change', (latest: number) => {
        value.value = latest;
    });

    return { progress, value };
}

/**
 * `useScroll`'s offset option is loosely typed upstream; this alias keeps the
 * cast above in one place rather than sprinkling `any` through callers.
 */
type UseScrollOffset = NonNullable<
    Parameters<typeof useScroll>[0] extends infer O
        ? O extends { offset?: infer U }
            ? U
            : never
        : never
>;
