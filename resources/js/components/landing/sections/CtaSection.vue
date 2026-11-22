<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight } from '@lucide/vue';
import { computed } from 'vue';
import SectionShell from '@/components/landing/SectionShell.vue';
import type { CtaSectionProps } from '@/types';

/**
 * A call-to-action block: a big headline on the accent colour and one button.
 * Internal URLs ("/...") are Inertia visits; anything else opens normally.
 */

const props = defineProps<CtaSectionProps>();

const data = computed(() => props.section.data);
const isInternal = computed(
    () => data.value.url.startsWith('/') && !data.value.url.startsWith('//'),
);
const isAnchor = computed(() => data.value.url.startsWith('#'));
</script>

<template>
    <SectionShell :anchor="props.anchor" wide>
        <div
            data-tone="dark"
            class="rounded-[2rem] bg-ink px-6 py-16 text-paper sm:px-14 sm:py-20"
        >
            <p v-if="section.eyebrow" class="eyebrow text-paper/60">
                {{ section.eyebrow }}
            </p>
            <h2
                v-if="section.heading"
                class="display display-lg mt-6 max-w-[18ch]"
            >
                {{ section.heading }}
            </h2>
            <p
                v-if="section.subheading"
                class="mt-6 max-w-xl text-lg leading-relaxed text-paper/70"
            >
                {{ section.subheading }}
            </p>

            <div class="mt-10 flex flex-wrap items-center gap-6">
                <Link v-if="isInternal" :href="data.url" class="btn btn-accent">
                    {{ data.label }}
                    <ArrowUpRight class="btn-arrow size-4" aria-hidden="true" />
                </Link>
                <a
                    v-else
                    :href="data.url"
                    :target="isAnchor ? undefined : '_blank'"
                    :rel="isAnchor ? undefined : 'noopener noreferrer'"
                    class="btn btn-accent"
                >
                    {{ data.label }}
                    <ArrowUpRight class="btn-arrow size-4" aria-hidden="true" />
                </a>
                <p v-if="data.note" class="meta text-paper/60">
                    {{ data.note }}
                </p>
            </div>
        </div>
    </SectionShell>
</template>
