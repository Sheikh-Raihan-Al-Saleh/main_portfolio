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
    <section
        id="clients"
        class="relative scroll-mt-24 border-y border-border bg-card/60 py-10"
    >
        <div v-if="clients.length" class="site-container">
            <p class="text-center text-sm font-medium text-muted-foreground">
                {{ content.eyebrow || 'Trusted by teams who ship' }}
            </p>
            <div class="ui-marquee-mask mt-6 overflow-hidden">
                <ul
                    class="ui-marquee items-center gap-14 [--marquee-duration:45s]"
                >
                    <template v-for="copy in 2" :key="copy">
                        <li
                            v-for="client in clients"
                            :key="`${copy}-${client.id}`"
                            :aria-hidden="copy === 2 ? 'true' : undefined"
                            class="flex shrink-0 items-center gap-3 opacity-70 grayscale transition hover:opacity-100 hover:grayscale-0"
                        >
                            <img
                                v-if="client.logo_url"
                                :src="client.logo_url"
                                :alt="copy === 1 ? client.name : ''"
                                loading="lazy"
                                class="h-9 w-auto max-w-36 object-contain dark:brightness-0 dark:invert"
                            />
                            <span
                                class="text-lg font-semibold tracking-tight whitespace-nowrap"
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
