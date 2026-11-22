<script setup lang="ts">
import Reveal from '@/components/site/Reveal.vue';
import SectionHeading from '@/components/site/SectionHeading.vue';
import { resolveIcon } from '@/lib/iconResolver';
import type { CapabilitiesSectionProps } from '@/types';

/**
 * 01 — What the studio builds: a grid of capabilities and the four layers
 * every product is built from.
 */
defineProps<CapabilitiesSectionProps>();
</script>

<template>
    <section id="capabilities" class="section scroll-mt-24">
        <div class="container-site">
            <SectionHeading
                index="01"
                :eyebrow="content.eyebrow"
                :title="content.title"
                :highlight="content.highlight"
                :description="content.description"
                align="split"
            />

            <ul
                class="mt-16 grid border-t border-l border-line sm:grid-cols-2 lg:grid-cols-3"
            >
                <li
                    v-for="(item, i) in content.items"
                    :key="item.title"
                    class="group border-r border-b border-line"
                >
                    <Reveal
                        :delay="(i % 3) * 0.08"
                        class="flex h-full flex-col p-7 sm:p-9"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="grid size-12 place-items-center rounded-full border border-line transition-colors duration-500 group-hover:border-brand group-hover:bg-brand group-hover:text-brand-foreground"
                            >
                                <component
                                    :is="resolveIcon(item.icon)"
                                    class="size-5"
                                    aria-hidden="true"
                                />
                            </span>
                            <span class="meta">{{
                                String(i + 1).padStart(2, '0')
                            }}</span>
                        </div>

                        <h3 class="display mt-10 text-3xl">{{ item.title }}</h3>
                        <p class="body-copy mt-3">{{ item.summary }}</p>

                        <ul
                            v-if="item.tech?.length"
                            class="mt-auto flex flex-wrap gap-2 pt-8"
                        >
                            <li
                                v-for="tech in item.tech"
                                :key="tech"
                                class="tag"
                            >
                                {{ tech }}
                            </li>
                        </ul>
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
                        v-for="(layer, i) in [...content.stack].reverse()"
                        :key="layer.label"
                        as="li"
                        :delay="i * 0.08"
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
