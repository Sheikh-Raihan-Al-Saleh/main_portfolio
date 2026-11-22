<script setup lang="ts">
import { ArrowRight } from '@lucide/vue';
import Reveal from '@/components/site/Reveal.vue';
import SectionHeader from '@/components/site/SectionHeader.vue';
import { useStudioContent } from '@/composables/useStudioContent';
import { resolveIcon } from '@/lib/iconResolver';
import type { Company } from '@/types';

/**
 * What the studio builds, as a numbered editorial list: a giant number, the
 * capability in condensed type, then its summary and stack, each row on a
 * hairline that fills with the accent on hover. Then the four-layer stack as
 * a printed spec table beside its explanation.
 */
type Props = {
    company: Company;
    /** Chapter number for the section header, e.g. "01". */
    index?: string;
};

const props = defineProps<Props>();

const content = useStudioContent(props.company, 'capabilities');
</script>

<template>
    <section id="capabilities" class="site-section scroll-mt-24">
        <div class="site-container">
            <SectionHeader
                :index="index"
                :eyebrow="content.eyebrow"
                :title="content.title"
                :highlight="content.highlight"
                :description="content.description"
            />

            <ol class="mt-16 border-t border-border">
                <li
                    v-for="(item, i) in content.items ?? []"
                    :key="item.title"
                    class="group relative border-b border-border"
                >
                    <Reveal
                        :delay="0.04"
                        class="grid gap-4 py-8 sm:py-10 lg:grid-cols-12 lg:items-start lg:gap-8"
                    >
                        <!-- Accent wash that rises on hover -->
                        <span
                            class="pointer-events-none absolute inset-0 origin-bottom scale-y-0 bg-[var(--accent-fill)] transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-y-100"
                            aria-hidden="true"
                        />

                        <span
                            class="ui-display relative text-6xl text-muted-foreground transition-colors duration-500 group-hover:text-[var(--accent-ink)] sm:text-7xl lg:col-span-2"
                            >{{ String(i + 1).padStart(2, '0') }}</span
                        >

                        <div
                            class="relative flex items-start gap-4 lg:col-span-5"
                        >
                            <component
                                :is="resolveIcon(item.icon)"
                                class="mt-2 size-6 shrink-0 transition-colors duration-500 group-hover:text-[var(--accent-ink)]"
                                aria-hidden="true"
                            />
                            <h3
                                class="ui-display text-[clamp(2.25rem,4.5vw,4rem)] transition-colors duration-500 group-hover:text-[var(--accent-ink)]"
                            >
                                {{ item.title }}
                            </h3>
                        </div>

                        <div class="relative flex flex-col gap-4 lg:col-span-5">
                            <p
                                class="text-lg leading-relaxed text-muted-foreground transition-colors duration-500 group-hover:text-[var(--accent-ink)]"
                            >
                                {{ item.summary }}
                            </p>
                            <ul
                                v-if="item.tech?.length"
                                class="flex flex-wrap gap-1.5"
                            >
                                <li
                                    v-for="tech in item.tech"
                                    :key="tech"
                                    class="ui-pill transition-colors duration-500 group-hover:border-[var(--accent-ink)]/40 group-hover:text-[var(--accent-ink)]"
                                >
                                    {{ tech }}
                                </li>
                            </ul>
                        </div>
                    </Reveal>
                </li>
            </ol>

            <!-- The stack, as a spec table -->
            <Reveal
                v-if="content.stack?.length"
                class="mt-24 grid gap-10 lg:grid-cols-12"
            >
                <div class="flex flex-col gap-6 lg:col-span-5">
                    <span v-if="content.kicker" class="ui-eyebrow">{{
                        content.kicker
                    }}</span>
                    <h3 class="ui-display text-[clamp(2.5rem,5vw,4.5rem)]">
                        {{ content.stack_heading }}
                    </h3>
                    <p class="text-lg leading-relaxed text-muted-foreground">
                        {{ content.stack_intro }}
                    </p>
                    <a
                        v-if="content.footer_link"
                        href="#contact"
                        class="inline-flex flex-wrap items-center gap-x-2"
                    >
                        <span class="text-muted-foreground">{{
                            content.footer_note
                        }}</span>
                        <span class="ui-link ui-accent font-semibold">{{
                            content.footer_link
                        }}</span>
                        <ArrowRight class="size-4" />
                    </a>
                </div>

                <ol
                    class="border-t border-foreground lg:col-span-6 lg:col-start-7"
                >
                    <li
                        v-for="(layer, i) in [...content.stack].reverse()"
                        :key="layer.label"
                        class="grid grid-cols-[auto_1fr_auto] items-baseline gap-6 border-b border-border py-5"
                    >
                        <span class="ui-label text-muted-foreground"
                            >L{{ content.stack.length - i }}</span
                        >
                        <span class="text-xl font-semibold tracking-tight">{{
                            layer.label
                        }}</span>
                        <span
                            class="ui-label text-right text-muted-foreground"
                            >{{ layer.meta }}</span
                        >
                    </li>
                </ol>
            </Reveal>
        </div>
    </section>
</template>
