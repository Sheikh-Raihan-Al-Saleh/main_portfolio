import { onMounted, onUnmounted, ref } from 'vue';
import type { CSSProperties } from 'vue';

type AnimateBy = 'words' | 'letters';
type Direction = 'top' | 'bottom';

export function useBlurText(
    elementRef: ReturnType<typeof ref<HTMLElement | null>>,
    options: {
        delay?: number;
        animateBy?: AnimateBy;
        direction?: Direction;
        threshold?: number;
    } = {},
) {
    const {
        delay = 50,
        animateBy = 'words',
        direction = 'top',
        threshold = 0.1,
    } = options;

    const inView = ref(false);
    let observer: IntersectionObserver | null = null;

    onMounted(() => {
        const el = elementRef.value;

        if (!el) {
            return;
        }

        observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    inView.value = true;
                }
            },
            { threshold },
        );

        observer.observe(el);
    });

    onUnmounted(() => {
        observer?.disconnect();
        observer = null;
    });

    function getSegments(text: string): string[] {
        return animateBy === 'words' ? text.split(' ') : text.split('');
    }

    function getSegmentStyle(index: number): CSSProperties {
        return {
            display: 'inline-block',
            filter: inView.value ? 'blur(0px)' : 'blur(10px)',
            opacity: inView.value ? 1 : 0,
            transform: inView.value
                ? 'translateY(0)'
                : `translateY(${direction === 'top' ? '-20px' : '20px'})`,
            transition: `all 0.5s ease-out ${index * delay}ms`,
        };
    }

    return {
        inView,
        getSegments,
        getSegmentStyle,
    };
}
