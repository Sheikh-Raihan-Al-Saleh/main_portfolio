<script setup lang="ts">
import Reveal from '@/components/Site/Reveal.vue';
import SectionHeading from '@/components/Site/SectionHeading.vue';
import { resolveIcon } from '@/composables/iconResolver';
import type { CapabilitiesSectionProps } from '@/types';

/**
 * What the studio builds: a bento grid of capabilities and the four layers
 * every product is built from.
 */
const props = defineProps<CapabilitiesSectionProps>();

/**
 * Grid spans for the bento. The first capability leads across two columns;
 * the last one widens to close the final row, so no card sits alone beside
 * an empty gap (3 columns on large screens, 2 on medium ones).
 */
function bentoSpanClass(itemIndex: number): string[] {
    const itemCount = props.content.items.length;
    const lastIndex = itemCount - 1;
    const cellsWithLeadTile = itemCount + 1;
    const classes: string[] = [];

    if (itemIndex === 0) {
        classes.push('sm:col-span-2');
    }

    if (itemIndex === lastIndex && itemIndex !== 0) {
        if (cellsWithLeadTile % 2 === 1) {
            classes.push('sm:col-span-2');
        }

        const largeRemainder = cellsWithLeadTile % 3;

        if (largeRemainder === 1) {
            classes.push('lg:col-span-3');
        } else if (largeRemainder === 2) {
            classes.push('lg:col-span-2');
        } else {
            classes.push('lg:col-span-1');
        }
    }

    return classes;
}
</script>

<template>
    <section id="capabilities" class="section scroll-mt-24">
        <div class="container-site">
            <SectionHeading
                :eyebrow="content.eyebrow"
                :title="content.title"
                :highlight="content.highlight"
                :description="content.description"
                align="split"
            />

            <!-- A bento grid: the first capability leads as a wide forest tile. -->
            <ul class="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <li
                    v-for="(item, itemIndex) in content.items"
                    :key="item.title"
                    :class="bentoSpanClass(itemIndex)"
                >
                    <!-- The card sits inside Reveal: Reveal animates its own
                         transform, which would cancel the hover lift. -->
                    <Reveal :delay="(itemIndex % 3) * 0.08" class="h-full">
                        <div
                            :data-tone="itemIndex === 0 ? 'dark' : undefined"
                            :class="[
                                'group flex h-full flex-col p-7 transition-[transform,box-shadow] duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-1 sm:p-8',
                                itemIndex === 0
                                    ? 'forest-card'
                                    : 'paper-card hover:shadow-[var(--shadow-lift)]',
                            ]"
                        >
                            <span
                                :class="[
                                    'grid size-12 place-items-center rounded-2xl',
                                    itemIndex === 0
                                        ? 'bg-[var(--pop)] text-ink'
                                        : 'bg-brand-subtle text-brand-text',
                                ]"
                            >
                                <component
                                    :is="resolveIcon(item.icon)"
                                    class="size-5"
                                    aria-hidden="true"
                                />
                            </span>

                            <h3
                                :class="[
                                    'display mt-8',
                                    itemIndex === 0 ? 'text-4xl' : 'text-2xl',
                                ]"
                            >
                                {{ item.title }}
                            </h3>
                            <p
                                :class="[
                                    'mt-3 max-w-[52ch] leading-relaxed',
                                    itemIndex === 0
                                        ? 'text-lg text-paper/75'
                                        : 'body-copy',
                                ]"
                            >
                                {{ item.summary }}
                            </p>

                            <ul
                                v-if="item.tech?.length"
                                class="mt-auto flex flex-wrap gap-2 pt-7"
                            >
                                <li
                                    v-for="tech in item.tech"
                                    :key="tech"
                                    class="tag"
                                >
                                    {{ tech }}
                                </li>
                            </ul>
                        </div>
                    </Reveal>
                </li>
            </ul>

            <!-- The stack: the same four layers on every product. -->
            <div
                v-if="content.stack.length"
                class="mt-20 grid gap-10 lg:grid-cols-12"
            >
                <Reveal class="lg:col-span-5">
                    <p class="eyebrow eyebrow-plain">{{ content.kicker }}</p>
                    <h3 class="display display-sm mt-4">
                        {{ content.stack_heading }}
                    </h3>
                    <p class="body-copy mt-4">{{ content.stack_intro }}</p>
                </Reveal>

                <ol class="lg:col-span-6 lg:col-start-7">
                    <Reveal
                        v-for="(layer, layerIndex) in [
                            ...content.stack,
                        ].reverse()"
                        :key="layer.label"
                        as="li"
                        :delay="layerIndex * 0.08"
                        class="rule flex items-baseline justify-between gap-6 py-5 last:border-b last:border-line"
                    >
                        <span class="display text-3xl sm:text-4xl">{{
                            layer.label
                        }}</span>
                        <span class="meta text-right">{{ layer.meta }}</span>
                    </Reveal>
                </ol>
            </div>
        </div>
    </section>
</template>
