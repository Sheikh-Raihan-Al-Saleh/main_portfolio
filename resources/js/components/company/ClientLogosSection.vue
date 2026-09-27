<script setup lang="ts">
import { motion } from 'motion-v';
import SectionHeading from '@/components/portfolio/SectionHeading.vue';
import { fadeUp, inViewOnce, stagger } from '@/lib/motion';
import type { Client } from '@/types';

type Props = {
    clients: Client[];
    /** Eyebrow above the heading, e.g. "Trusted by". */
    eyebrow?: string;
    heading?: string;
    description?: string;
    /**
     * Chapter number. Left empty by default: a logo wall is social proof that
     * sits under the hero, not a numbered chapter, and the rest of the home page
     * numbers itself 01, 02, 03 from the work section down.
     */
    index?: string;
    /** Renders the block edge to edge without the dashed frame. */
    bare?: boolean;
};

withDefaults(defineProps<Props>(), {
    eyebrow: 'Trusted by',
    heading: 'Teams we build for',
    description:
        'The studios, founders and product teams who trusted a small team with their platform.',
    index: '',
    bare: false,
});
</script>

<template>
    <section
        v-if="clients.length"
        id="clients"
        class="bg-noise scroll-mt-20 border-b border-border py-16 sm:py-20"
    >
        <div :class="bare ? 'container-laravel' : 'container-laravel section-dashed-xl'">
            <SectionHeading
                :index="index"
                :eyebrow="eyebrow"
                :title="heading"
                :highlight="heading.split(' ').pop() ?? ''"
                :description="description"
            />

            <motion.ul
                :variants="stagger(0.06)"
                initial="hidden"
                while-in-view="visible"
                :in-view-options="inViewOnce"
                class="grid grid-cols-2 gap-px overflow-hidden rounded-xl border border-border bg-border sm:grid-cols-3 lg:grid-cols-4"
            >
                <motion.li
                    v-for="client in clients"
                    :key="client.id"
                    :variants="fadeUp"
                    class="group relative flex aspect-16/10 flex-col items-center justify-center gap-3 bg-background p-4 text-center sm:p-6"
                >
                    <img
                        v-if="client.logo_url"
                        :src="client.logo_url"
                        :alt="client.name"
                        loading="lazy"
                        class="max-h-12 w-auto max-w-[70%] object-contain opacity-70 transition duration-300 group-hover:opacity-100"
                    />
                    <span
                        v-else
                        class="font-display text-base font-bold tracking-tight sm:text-lg"
                    >
                        {{ client.name }}
                    </span>

                    <span
                        class="text-[0.7rem] tracking-widest text-muted-foreground uppercase opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                    >
                        {{ client.industry ?? client.name }}
                    </span>

                    <a
                        v-if="client.website_url"
                        :href="client.website_url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="absolute inset-0"
                        :aria-label="`${client.name} website`"
                    >
                        <span class="sr-only">{{ client.name }}</span>
                    </a>
                </motion.li>
            </motion.ul>
        </div>
    </section>
</template>
