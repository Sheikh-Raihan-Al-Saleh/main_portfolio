<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

/**
 * A display headline whose words rise out of a mask one after another.
 *
 * Pure CSS (.ui-word in site.css), so it is server-rendered with the real
 * text, costs no JavaScript while animating, and is switched off under
 * reduced motion. The last `highlight` words carry the animated gradient.
 */
type Props = {
    text: string;
    highlight?: number;
    /** Delay before the first word, in ms. */
    delay?: number;
    as?: 'h1' | 'h2';
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    highlight: 2,
    delay: 0,
    as: 'h1',
    class: undefined,
});

const words = computed(() => {
    const list = props.text.split(/\s+/).filter(Boolean);
    const cut = Math.max(0, list.length - props.highlight);

    return list.map((word, i) => ({ word, mark: i >= cut }));
});
</script>

<template>
    <component
        :is="as"
        :class="cn('ui-display', props.class)"
        :aria-label="text"
        :style="{ '--base': `${delay}ms` }"
    >
        <template v-for="(item, i) in words" :key="i">
            <span class="ui-word" aria-hidden="true"
                ><span
                    :class="item.mark ? 'text-gradient' : undefined"
                    :style="{ '--i': i }"
                    >{{ item.word }}</span
                ></span
            >{{ i < words.length - 1 ? ' ' : '' }}
        </template>
    </component>
</template>
