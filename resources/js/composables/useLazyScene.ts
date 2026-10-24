import { defineAsyncComponent, onMounted, ref, shallowRef } from 'vue';
import type { Component, Ref, ShallowRef } from 'vue';

/**
 * Loads the hero's WebGL scene only after mount, and only when the browser
 * can draw it and the visitor has not asked for reduced motion. three.js
 * therefore lands in its own chunk and never blocks first paint; when the
 * scene is skipped, the hero's CSS composition stands on its own.
 */
export function useLazyScene(): {
    scene: ShallowRef<Component | null>;
    ready: Ref<boolean>;
    fail: () => void;
} {
    const scene = shallowRef<Component | null>(null);
    const ready = ref(false);

    function canUseWebGl(): boolean {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return false;
        }

        try {
            const canvas = document.createElement('canvas');

            return Boolean(
                canvas.getContext('webgl2') ?? canvas.getContext('webgl'),
            );
        } catch {
            return false;
        }
    }

    onMounted(() => {
        if (canUseWebGl()) {
            scene.value = defineAsyncComponent(
                () => import('@/components/site/SoftwareScene.vue'),
            );
        }
    });

    function fail() {
        scene.value = null;
    }

    return { scene, ready, fail };
}
