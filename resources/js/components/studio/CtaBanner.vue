<script setup lang="ts">
import { ArrowUpRight } from '@lucide/vue';
import { computed } from 'vue';
import Reveal from '@/components/site/Reveal.vue';
import { useSite } from '@/composables/useSite';
import { highlightParts } from '@/lib/text';
import type { CtaBannerProps } from '@/types';

/**
 * The closing call to action: one big line and one button.
 */
const props = defineProps<CtaBannerProps>();

const { studio } = useSite();

const parts = computed(() =>
    highlightParts(props.content.title, props.content.highlight),
);
</script>

<template>
    <section class="section">
        <div class="container-site">
            <Reveal
                class="relative overflow-hidden rounded-[2rem] bg-brand px-6 py-16 text-brand-foreground sm:px-14 sm:py-24"
            >
                <p
                    class="eyebrow text-brand-foreground [&::before]:bg-brand-foreground"
                >
                    {{ content.eyebrow }}
                </p>
                <h2 class="display display-lg mt-6 max-w-[16ch]">
                    <template v-for="(part, i) in parts" :key="i">
                        <em v-if="part.highlight" class="!text-paper">{{
                            part.text
                        }}</em>
                        <template v-else>{{ part.text }}</template>
                    </template>
                </h2>
                <p
                    class="mt-6 max-w-xl text-lg leading-relaxed text-brand-foreground"
                >
                    {{ content.description }}
                </p>

                <div class="mt-10 flex flex-wrap items-center gap-6">
                    <a
                        href="#contact"
                        class="btn bg-ink text-paper hover:bg-paper hover:text-ink"
                    >
                        {{ content.button_label }}
                        <ArrowUpRight
                            class="btn-arrow size-4"
                            aria-hidden="true"
                        />
                    </a>
                    <a
                        v-if="studio.public_email"
                        :href="`mailto:${studio.public_email}`"
                        class="link-underline font-medium"
                    >
                        {{ content.email_label }}: {{ studio.public_email }}
                    </a>
                </div>

                <p class="mt-10 inline-flex items-center gap-3 text-sm">
                    <span
                        class="status-dot bg-ink"
                        :class="
                            !studio.is_accepting_projects && 'status-dot-off'
                        "
                    />
                    {{
                        studio.is_accepting_projects
                            ? content.status_available
                            : content.status_unavailable
                    }}
                </p>
            </Reveal>
        </div>
    </section>
</template>
