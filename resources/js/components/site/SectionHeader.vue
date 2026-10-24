<script setup lang="ts">
import { computed } from 'vue';
import Reveal from '@/components/site/Reveal.vue';
import { cn } from '@/lib/utils';

/**
 * The heading every public section opens with: an eyebrow pill, a title
 * whose highlighted phrase carries the face's gradient, and a short lead.
 */
type Props = {
    eyebrow?: string;
    title: string;
    highlight?: string;
    description?: string;
    align?: 'center' | 'left';
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    eyebrow: '',
    highlight: '',
    description: '',
    align: 'center',
    class: undefined,
});

const parts = computed<{ text: string; mark: boolean }[]>(() => {
    const { title, highlight } = props;

    if (!highlight || !title.includes(highlight)) {
        return [{ text: title, mark: false }];
    }

    const [before, ...rest] = title.split(highlight);

    return [
        { text: before ?? '', mark: false },
        { text: highlight, mark: true },
        { text: rest.join(highlight), mark: false },
    ].filter((part) => part.text.length > 0);
});
</script>

<template>
    <div
        :class="
            cn(
                'flex max-w-3xl flex-col gap-5',
                align === 'center'
                    ? 'mx-auto items-center text-center'
                    : 'items-start',
                props.class,
            )
        "
    >
        <Reveal v-if="eyebrow">
            <span class="ui-eyebrow">{{ eyebrow }}</span>
        </Reveal>
        <Reveal :delay="0.05">
            <h2 class="ui-display ui-h2">
                <template v-for="(part, i) in parts" :key="i">
                    <span v-if="part.mark" class="text-gradient">{{
                        part.text
                    }}</span>
                    <template v-else>{{ part.text }}</template>
                </template>
            </h2>
        </Reveal>
        <Reveal v-if="description" :delay="0.1">
            <p
                class="text-lg leading-relaxed text-pretty text-muted-foreground"
            >
                {{ description }}
            </p>
        </Reveal>
        <slot />
    </div>
</template>
