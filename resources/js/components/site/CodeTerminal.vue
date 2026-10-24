<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { cn } from '@/lib/utils';

/**
 * A small terminal window whose lines type themselves in, one after another.
 *
 * Every character is in the markup from the start — the typing is a CSS
 * width reveal — so the server-rendered page and visitors without
 * JavaScript read the whole log, and nothing hydrates differently.
 */
type Line = {
    text: string;
    /** `cmd` lines get a prompt; `ok` lines are green; `dim` lines are muted. */
    kind?: 'cmd' | 'ok' | 'dim' | 'out';
};

type Props = {
    title?: string;
    lines: Line[];
    /** Seconds per line. */
    pace?: number;
    /** Restart the whole log after it finishes. */
    loop?: boolean;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    title: 'zsh',
    pace: 0.9,
    loop: true,
    class: undefined,
});

const total = () => props.lines.length * props.pace + 2.5;

/** Bumped to replay the log; the lines are keyed by it. */
const cycle = ref(0);
let timer: number | null = null;

onMounted(() => {
    if (
        !props.loop ||
        window.matchMedia('(prefers-reduced-motion: reduce)').matches
    ) {
        return;
    }

    timer = window.setInterval(() => {
        cycle.value += 1;
    }, total() * 1000);
});

onBeforeUnmount(() => {
    if (timer !== null) {
        window.clearInterval(timer);
    }
});
</script>

<template>
    <div
        :class="
            cn(
                'overflow-hidden rounded-2xl border border-white/10 bg-[#0d1117] text-[#e6edf3] shadow-[var(--shadow-lift)]',
                props.class,
            )
        "
    >
        <div
            class="flex items-center gap-1.5 border-b border-white/10 px-3.5 py-2.5"
        >
            <span class="size-2.5 rounded-full bg-[#ff5f57]" />
            <span class="size-2.5 rounded-full bg-[#febc2e]" />
            <span class="size-2.5 rounded-full bg-[#28c840]" />
            <span class="ml-2 font-mono text-[0.68rem] text-white/50">{{
                title
            }}</span>
        </div>
        <div
            class="flex flex-col gap-1 px-4 py-3.5 font-mono text-[0.78rem] leading-relaxed"
        >
            <p
                v-for="(line, i) in lines"
                :key="`${cycle}-${i}`"
                class="ui-log-line"
                :style="{
                    '--chars': line.text.length + 2,
                    '--start': `${i * pace}s`,
                }"
            >
                <span v-if="line.kind === 'cmd'" class="text-[#79c0ff]"
                    >$
                </span>
                <span
                    :class="{
                        'text-[#3fb950]': line.kind === 'ok',
                        'text-white/45': line.kind === 'dim',
                    }"
                    >{{ line.text }}</span
                >
            </p>
        </div>
    </div>
</template>
