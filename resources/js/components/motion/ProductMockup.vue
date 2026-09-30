<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

/**
 * A product UI in a browser window.
 *
 * The Studio presents work as products rather than screenshots, so every case
 * study renders through this frame: window chrome, an address pill, the screen
 * itself, and — optionally — a second panel stacked behind it in Z, which is
 * what gives the composition depth when it is placed inside `Scene3D`.
 *
 * The frame is presentation only. The screen accepts either a cover image or
 * slotted markup for a hand-built interface.
 */
type Props = {
    imageUrl?: string | null;
    alt?: string;
    /** Text in the address pill. Reads as the product's real URL. */
    url?: string;
    /** Window title, shown beside the traffic lights. */
    label?: string;
    /** Aspect ratio of the screen. */
    aspect?: '16/9' | '4/3' | '21/9' | '3/4';
    /** Stack a second, offset panel behind the window. */
    layered?: boolean;
    /** Deep brand-tinted shadow under the window. */
    elevated?: boolean;
    /** Rounds the shell further, for device-like compositions. */
    radius?: 'lg' | 'xl';
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    imageUrl: null,
    alt: '',
    url: 'localhost:8000',
    label: undefined,
    aspect: '16/9',
    layered: false,
    elevated: true,
    radius: 'lg',
    class: undefined,
});

const ASPECT: Record<NonNullable<Props['aspect']>, string> = {
    '16/9': 'aspect-video',
    '4/3': 'aspect-4/3',
    '21/9': 'aspect-21/9',
    '3/4': 'aspect-3/4',
};

const screenClass = computed(() => cn('img-shine', ASPECT[props.aspect]));

const shellClass = computed(() =>
    cn(
        'window-chrome relative z-10 overflow-hidden',
        props.radius === 'xl' ? 'rounded-xl' : 'rounded-lg',
        props.elevated && 'glow-card',
    ),
);

/** Shortens a URL for the pill without letting it break the window chrome. */
const displayUrl = computed(() =>
    props.url.replace(/^https?:\/\//, '').replace(/\/$/, ''),
);
</script>

<template>
    <div :class="cn('relative', props.class)">
        <!-- Second panel behind the window: same product, one screen back. -->
        <div
            v-if="props.layered"
            aria-hidden="true"
            class="pointer-events-none absolute -top-6 -right-5 hidden w-[38%] overflow-hidden rounded-lg border border-border bg-card shadow-xl sm:block"
            style="transform: translate3d(0, 0, -60px) rotateY(-6deg)"
        >
            <div class="title-bar py-1.5">
                <div class="traffic-light">
                    <span class="bg-syn-variable/50" />
                    <span class="bg-syn-number/50" />
                    <span class="bg-syn-string/50" />
                </div>
            </div>
            <div class="bg-mesh aspect-4/3 w-full" />
        </div>

        <div :class="shellClass">
            <div class="title-bar">
                <div class="traffic-light">
                    <span class="bg-syn-variable/70" />
                    <span class="bg-syn-number/70" />
                    <span class="bg-syn-string/70" />
                </div>

                <span
                    class="ml-2 hidden min-w-0 flex-1 truncate rounded bg-background/70 px-2 py-0.5 text-[10px] ring-1 ring-border/60 sm:block"
                >
                    {{ displayUrl }}
                </span>

                <span
                    v-if="props.label"
                    class="ml-auto shrink-0 font-mono text-[10px] text-muted-foreground sm:ml-0"
                >
                    {{ props.label }}
                </span>
            </div>

            <div :class="screenClass">
                <img
                    v-if="props.imageUrl"
                    :src="props.imageUrl"
                    :alt="props.alt"
                    loading="lazy"
                    decoding="async"
                    class="size-full object-cover"
                />

                <div v-else class="size-full">
                    <slot>
                        <div
                            class="bg-mesh grid size-full place-items-center text-muted-foreground/30"
                        >
                            <span class="font-mono text-sm"
                                >// no preview yet</span
                            >
                        </div>
                    </slot>
                </div>
            </div>
        </div>
    </div>
</template>
