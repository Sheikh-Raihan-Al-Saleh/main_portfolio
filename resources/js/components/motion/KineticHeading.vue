<script setup lang="ts">
import { motion } from 'motion-v';
import { computed } from 'vue';
import { characterReveal, stagger } from '@/lib/motion';
import { cn } from '@/lib/utils';

type Props = {
    text: string;
    /** Rendered element. Keep this semantic — usually h1 or h2. */
    as?: string;
    /** Seconds between each character. */
    speed?: number;
    /** Delay before the first character, in seconds. */
    delay?: number;
    /** Animate on mount (hero) rather than when scrolled into view. */
    immediate?: boolean;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    as: 'span',
    speed: 0.022,
    delay: 0,
    immediate: false,
    class: undefined,
});

/**
 * Split into words first, then characters, so a long headline still wraps at
 * word boundaries — splitting straight to characters lets lines break
 * mid-word, which looks broken at narrow widths.
 */
const words = computed(() =>
    props.text.split(' ').map((word) => ({
        word,
        characters: [...word],
    })),
);
</script>

<template>
    <!--
      The visible characters are decorative fragments: they are hidden from the
      accessibility tree and the full string is exposed once via aria-label, so
      a screen reader announces the heading normally.
    -->
    <component
        :is="props.as"
        :class="cn('font-display', props.class)"
        :aria-label="props.text"
    >
        <motion.span
            class="inline"
            aria-hidden="true"
            :variants="stagger(props.speed, props.delay)"
            :initial="'hidden'"
            :animate="props.immediate ? 'visible' : undefined"
            :while-in-view="props.immediate ? undefined : 'visible'"
            :in-view-options="
                props.immediate ? undefined : { once: true, amount: 0.4 }
            "
        >
            <span
                v-for="(entry, wordIndex) in words"
                :key="`${entry.word}-${wordIndex}`"
                class="inline-block whitespace-nowrap"
            >
                <motion.span
                    v-for="(character, characterIndex) in entry.characters"
                    :key="characterIndex"
                    class="inline-block will-change-transform"
                    :variants="characterReveal"
                    >{{ character }}</motion.span
                >
                <!-- Real space between words so text can wrap. -->
                <span v-if="wordIndex < words.length - 1">&nbsp;</span>
            </span>
        </motion.span>
    </component>
</template>
