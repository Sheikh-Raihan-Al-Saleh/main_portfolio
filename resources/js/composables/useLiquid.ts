/**
 * Lazy access to the shared liquid WebGL engine (lib/liquid/engine.ts).
 *
 * three.js is only downloaded when the browser is idle, and never for
 * visitors who asked for reduced motion or whose browser has no WebGL; for
 * them every LiquidImage stays a plain <img> and every LiquidBlob keeps its
 * CSS gradient.
 */
import type * as LiquidEngine from '@/composables/liquidEngine';

type Engine = typeof LiquidEngine;

let enginePromise: Promise<Engine | null> | null = null;

function allowed(): boolean {
    if (typeof window === 'undefined') {
        return false;
    }

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

function whenIdle(callback: () => void): void {
    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(callback, { timeout: 1500 });
    } else {
        setTimeout(callback, 300);
    }
}

/** Resolves to the engine, or null when the liquid layer should not run. */
export function loadLiquid(): Promise<Engine | null> {
    if (enginePromise) {
        return enginePromise;
    }

    enginePromise = allowed()
        ? new Promise((resolve) => {
              whenIdle(() => {
                  import('@/composables/liquidEngine')
                      .then((engine) =>
                          resolve(engine.supported() ? engine : null),
                      )
                      .catch(() => resolve(null));
              });
          })
        : Promise.resolve(null);

    return enginePromise;
}
