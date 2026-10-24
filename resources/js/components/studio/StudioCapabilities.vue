<script setup lang="ts">
import { ArrowRight } from '@lucide/vue';
import Reveal from '@/components/site/Reveal.vue';
import SectionHeader from '@/components/site/SectionHeader.vue';
import { useStudioContent } from '@/composables/useStudioContent';
import { resolveIcon } from '@/lib/iconResolver';
import type { Company } from '@/types';

/**
 * What the studio builds: a grid of capability cards, then the four-layer
 * stack drawn as layered slabs beside its explanation.
 */
type Props = {
    company: Company;
};

const props = defineProps<Props>();

const content = useStudioContent(props.company, 'capabilities');
</script>

<template>
    <section id="capabilities" class="site-section scroll-mt-24">
        <div class="site-container">
            <SectionHeader
                :eyebrow="content.eyebrow"
                :title="content.title"
                :highlight="content.highlight"
                :description="content.description"
            />

            <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <Reveal
                    v-for="(item, i) in content.items ?? []"
                    :key="item.title"
                    :delay="(i % 3) * 0.08"
                >
                    <article
                        class="group ui-card ui-card-hover flex h-full flex-col gap-4 p-7"
                    >
                        <span
                            class="ui-icon-tile transition-transform duration-500 group-hover:scale-110 group-hover:-rotate-6"
                        >
                            <component
                                :is="resolveIcon(item.icon)"
                                class="size-5"
                                aria-hidden="true"
                            />
                        </span>
                        <h3 class="ui-h3">{{ item.title }}</h3>
                        <p
                            class="text-sm leading-relaxed text-muted-foreground"
                        >
                            {{ item.summary }}
                        </p>
                        <ul
                            v-if="item.tech?.length"
                            class="mt-auto flex flex-wrap gap-1.5 pt-2"
                        >
                            <li
                                v-for="tech in item.tech"
                                :key="tech"
                                class="ui-pill"
                            >
                                {{ tech }}
                            </li>
                        </ul>
                    </article>
                </Reveal>
            </div>

            <!-- The stack -->
            <Reveal v-if="content.stack?.length" class="mt-20">
                <div
                    class="ui-card grid gap-10 overflow-hidden p-8 lg:grid-cols-2 lg:items-center lg:p-12"
                >
                    <div class="flex flex-col gap-5">
                        <span v-if="content.kicker" class="ui-eyebrow w-fit">{{
                            content.kicker
                        }}</span>
                        <h3 class="ui-display text-3xl sm:text-4xl">
                            {{ content.stack_heading }}
                        </h3>
                        <p class="leading-relaxed text-muted-foreground">
                            {{ content.stack_intro }}
                        </p>
                        <a
                            v-if="content.footer_link"
                            href="#contact"
                            class="inline-flex flex-wrap items-center gap-x-2 text-sm"
                        >
                            <span class="text-muted-foreground">{{
                                content.footer_note
                            }}</span>
                            <span class="ui-link font-semibold text-brand">{{
                                content.footer_link
                            }}</span>
                            <ArrowRight class="size-4 text-brand" />
                        </a>
                    </div>

                    <ol class="flex flex-col gap-3 [perspective:1200px]">
                        <li
                            v-for="(layer, i) in [...content.stack].reverse()"
                            :key="layer.label"
                            class="flex items-center justify-between gap-4 rounded-2xl border border-border bg-background px-5 py-4 shadow-[var(--shadow-soft)] transition-transform duration-500 hover:-translate-y-1 lg:[transform:rotateX(14deg)]"
                            :style="{
                                marginInline: `${(content.stack.length - 1 - i) * 4}%`,
                            }"
                        >
                            <span class="flex items-center gap-3">
                                <span
                                    class="size-2.5 rounded-full bg-gradient-to-br from-[var(--grad-from)] to-[var(--grad-to)]"
                                    :style="{ opacity: 1 - i * 0.18 }"
                                />
                                <span class="font-semibold">{{
                                    layer.label
                                }}</span>
                            </span>
                            <span
                                class="text-right text-xs text-muted-foreground"
                                >{{ layer.meta }}</span
                            >
                        </li>
                    </ol>
                </div>
            </Reveal>
        </div>
    </section>
</template>
