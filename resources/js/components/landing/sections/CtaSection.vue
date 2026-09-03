<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import { computed } from 'vue';
import SectionShell from '@/components/landing/SectionShell.vue';
import { Button } from '@/components/ui/button';
import type { LandingSection } from '@/types';

type Props = {
    section: Extract<LandingSection, { type: 'cta' }>;
    anchor?: string;
};

const props = defineProps<Props>();

const data = computed(() => props.section.data);

const isInternal = computed(() => data.value.url.startsWith('/'));
</script>

<template>
    <SectionShell :anchor="props.anchor">
        <div
            class="bg-noise bg-red-glow relative overflow-hidden rounded-lg px-6 py-16 text-center sm:px-12"
        >
            <div class="corner-dot corner-dot-tl" aria-hidden="true" />
            <div class="corner-dot corner-dot-tr" aria-hidden="true" />
            <div class="corner-dot corner-dot-bl" aria-hidden="true" />
            <div class="corner-dot corner-dot-br" aria-hidden="true" />

            <div class="relative">
                <p
                    v-if="section.eyebrow"
                    class="mb-3 font-mono text-xs text-brand uppercase"
                >
                    {{ section.eyebrow }}
                </p>

                <h2
                    v-if="section.heading"
                    class="mx-auto max-w-2xl font-display text-3xl font-bold tracking-tight text-balance sm:text-4xl"
                >
                    {{ section.heading }}
                </h2>

                <p
                    v-if="section.subheading"
                    class="mx-auto mt-4 max-w-xl text-lg text-pretty text-muted-foreground"
                >
                    {{ section.subheading }}
                </p>

                <div class="mt-8 flex justify-center">
                    <Button as-child size="lg" class="btn-laravel-primary">
                        <Link v-if="isInternal" :href="data.url">
                            {{ data.label }}
                            <ArrowRight class="size-4" />
                        </Link>
                        <a
                            v-else
                            :href="data.url"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {{ data.label }}
                            <ArrowRight class="size-4" />
                        </a>
                    </Button>
                </div>

                <p v-if="data.note" class="mt-4 text-sm text-muted-foreground">
                    {{ data.note }}
                </p>
            </div>
        </div>
    </SectionShell>
</template>
