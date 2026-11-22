<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import type { CodeToken } from '@/lib/code';
import { cn } from '@/lib/utils';

/**
 * A dark code-editor window: a tab bar, a line-number gutter and
 * syntax-coloured code that types itself out line by line, ending on a
 * blinking cursor, with a status bar underneath.
 *
 * The typing is the CSS `.ui-log-line` effect (site.css), so the full code is
 * server-rendered and simply shows complete under reduced motion. It replays
 * on a loop.
 */
type Props = {
    tabs: string[];
    lines: CodeToken[][];
    status?: string[];
    /** Seconds per character. */
    pace?: number;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    status: () => [],
    pace: 0.03,
    class: undefined,
});

/** One Night-Owl-ish palette, readable on the near-black editor. */
const palette: Record<NonNullable<CodeToken['kind']>, string> = {
    kw: 'text-[#c792ea]',
    str: 'text-[#c3e88d]',
    fn: 'text-[#82aaff]',
    num: 'text-[#f78c6c]',
    com: 'text-[#7f8bb3] italic',
    punc: 'text-[#89ddff]',
    prop: 'text-[#f07178]',
    type: 'text-[#ffcb6b]',
};

/** Each line with its length and the moment its typing starts. */
const rows = computed(() => {
    let start = 0.4;

    return props.lines.map((tokens) => {
        const chars = tokens.reduce((sum, token) => sum + token.text.length, 0);
        const row = { tokens, chars, start };
        // Blank lines still take a beat, like a real keystroke on Enter.
        start += Math.max(chars, 2) * props.pace + 0.12;

        return row;
    });
});

const total = computed(() => {
    const last = rows.value.at(-1);

    return last ? last.start + last.chars * props.pace : 0;
});

/** Bumped to replay the typing; the lines are keyed by it. */
const cycle = ref(0);
let timer: number | null = null;

onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    timer = window.setInterval(
        () => {
            cycle.value += 1;
        },
        (total.value + 6) * 1000,
    );
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
                'overflow-hidden rounded-md border border-white/10 bg-[#0d1117]/95 font-mono text-[13px] leading-6 text-[#e6edf3] shadow-2xl backdrop-blur sm:text-sm',
                props.class,
            )
        "
        data-testid="code-editor"
    >
        <!-- Title bar with tabs -->
        <div
            class="flex items-center gap-4 border-b border-white/10 bg-[#010409]/80 px-4"
        >
            <span class="flex gap-1.5 py-3" aria-hidden="true">
                <span class="size-3 rounded-full bg-[#ff5f57]" />
                <span class="size-3 rounded-full bg-[#febc2e]" />
                <span class="size-3 rounded-full bg-[#28c840]" />
            </span>
            <span class="flex min-w-0 overflow-hidden">
                <span
                    v-for="(tab, i) in tabs"
                    :key="tab"
                    class="shrink-0 border-x border-transparent px-3 py-3 text-xs"
                    :class="
                        i === 0
                            ? 'border-white/10 bg-[#0d1117] text-white shadow-[inset_0_2px_0_var(--accent-fill)]'
                            : 'text-white/45'
                    "
                    >{{ tab }}</span
                >
            </span>
        </div>

        <!-- Code -->
        <div class="flex py-4" :key="cycle">
            <ol
                class="shrink-0 pr-4 pl-4 text-right text-white/25 select-none"
                aria-hidden="true"
            >
                <li v-for="(_, i) in rows" :key="i">{{ i + 1 }}</li>
            </ol>
            <pre
                class="min-w-0 flex-1 overflow-hidden pr-4"
            ><code><template v-for="(row, i) in rows" :key="i"><span
                class="ui-log-line inline-block align-top"
                :style="{ '--chars': Math.max(row.chars, 1), '--start': `${row.start}s` }"
            ><span
                v-for="(token, t) in row.tokens"
                :key="t"
                :class="token.kind ? palette[token.kind] : undefined"
            >{{ token.text }}</span></span><span
                v-if="i === rows.length - 1"
                class="code-caret ml-px inline-block h-[1.1em] w-2 translate-y-[0.2em] bg-[var(--accent-fill)]"
                :style="{ '--caret-at': `${total}s` }"
                aria-hidden="true"
            />{{ '\n' }}</template></code></pre>
        </div>

        <!-- Status bar -->
        <div
            v-if="status.length"
            class="flex flex-wrap items-center gap-x-5 gap-y-1 border-t border-white/10 bg-[var(--accent-fill)] px-4 py-1.5 text-[11px] text-[var(--accent-ink)]"
        >
            <span v-for="item in status" :key="item">{{ item }}</span>
        </div>
    </div>
</template>

<style scoped>
/* The cursor only appears once the last line has been typed, then blinks. */
.code-caret {
    animation:
        code-caret-show 0.01s linear var(--caret-at, 0s) both,
        code-caret 1s steps(1) var(--caret-at, 0s) infinite;
}

@keyframes code-caret-show {
    from {
        visibility: hidden;
    }
    to {
        visibility: visible;
    }
}

@keyframes code-caret {
    50% {
        opacity: 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .code-caret {
        animation: none;
    }
}
</style>
