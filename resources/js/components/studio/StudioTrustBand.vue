<script setup lang="ts">
import { Sparkles } from '@lucide/vue';
import { computed } from 'vue';
import { useStudioContent } from '@/composables/useStudioContent';
import type { Client, Company } from '@/types';

/**
 * The band under the hero: client logos drifting past, and the studio's
 * ticker phrases (Admin → Studio sections → Ticker) as a second, slower row.
 * Both rows are pure CSS marquees; each list is rendered twice so the loop is
 * seamless, with the second copy hidden from assistive tech.
 */
type Props = {
    clients: Client[];
    company: Company;
    items: string[];
};

const props = defineProps<Props>();

const content = useStudioContent(props.company, 'clients');

const phrases = computed(() =>
    props.items.map((item) => item.trim()).filter(Boolean),
);
</script>

<template>
    <section id="clients" class="ui-band relative scroll-mt-24 py-12">
        <div v-if="clients.length" class="site-container">
            <p
                class="text-center font-mono text-xs tracking-[0.2em] text-muted-foreground uppercase"
            >
                {{ content.eyebrow || 'Trusted by teams who ship' }}
            </p>
            <div class="ui-marquee-mask mt-6 overflow-hidden">
                <ul
                    class="ui-marquee items-center gap-8 [--marquee-duration:45s]"
                >
                    <template v-for="copy in 2" :key="copy">
                        <li
                            v-for="client in clients"
                            :key="`${copy}-${client.id}`"
                            :aria-hidden="copy === 2 ? 'true' : undefined"
                            class="group flex shrink-0 items-center"
                        >
                            <!--
                                Uploaded logos are usually dark-on-light
                                wordmarks, so they sit on a light chip in
                                both themes instead of being inverted, and the
                                name is only printed when there is no logo.
                            -->
                            <span
                                v-if="client.logo_url"
                                class="inline-flex items-center rounded-xl bg-white px-4 py-2.5 ring-1 ring-black/5"
                            >
                                <img
                                    :src="client.logo_url"
                                    :alt="copy === 1 ? client.name : ''"
                                    loading="lazy"
                                    decoding="async"
                                    class="h-8 w-auto max-w-44 object-contain opacity-80 grayscale transition duration-300 group-hover:opacity-100 group-hover:grayscale-0"
                                />
                            </span>
                            <span
                                v-else
                                class="text-xl font-bold tracking-tight whitespace-nowrap"
                                >{{ client.name }}</span
                            >
                        </li>
                    </template>
                </ul>
            </div>
        </div>

        <div
            v-if="phrases.length"
            class="ui-marquee-mask overflow-hidden"
            :class="clients.length ? 'mt-8' : ''"
        >
            <p class="sr-only">{{ phrases.join(', ') }}</p>
            <div
                class="ui-marquee gap-3 [--marquee-duration:60s] [animation-direction:reverse]"
                aria-hidden="true"
            >
                <template v-for="copy in 2" :key="copy">
                    <span
                        v-for="(phrase, i) in phrases"
                        :key="`${copy}-${i}`"
                        class="ui-pill shrink-0 px-4 py-2 text-sm"
                    >
                        <Sparkles class="size-3.5 text-brand" />
                        {{ phrase }}
                    </span>
                </template>
            </div>
        </div>
    </section>
</template>
