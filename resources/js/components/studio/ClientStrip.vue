<script setup lang="ts">
import SmartImage from '@/components/media/SmartImage.vue';
import Marquee from '@/components/site/Marquee.vue';
import type { ClientStripProps } from '@/types';

/**
 * "Trusted by": the client logos, scrolling. Logos without a file show the
 * client's name in the display face instead.
 */
defineProps<ClientStripProps>();
</script>

<template>
    <section v-if="clients.length" class="section-tight" aria-label="Clients">
        <div class="container-site grid items-center gap-8 lg:grid-cols-12">
            <p class="eyebrow lg:col-span-3">{{ label || 'Trusted by' }}</p>

            <div class="lg:col-span-9">
                <Marquee :duration="35" gap="4rem">
                    <component
                        :is="client.website_url ? 'a' : 'span'"
                        v-for="client in clients"
                        :key="client.id"
                        :href="client.website_url ?? undefined"
                        :target="client.website_url ? '_blank' : undefined"
                        :rel="
                            client.website_url
                                ? 'noopener noreferrer'
                                : undefined
                        "
                        class="flex h-14 shrink-0 items-center opacity-70 grayscale transition-all duration-500 hover:opacity-100 hover:grayscale-0"
                        :title="client.name"
                    >
                        <SmartImage
                            v-if="client.logo_url"
                            :src="client.logo_url"
                            :alt="client.name"
                            fit="contain"
                            class="h-12 w-40 bg-transparent"
                        >
                            <template #fallback>
                                <span class="display text-2xl text-ink">{{
                                    client.name
                                }}</span>
                            </template>
                        </SmartImage>
                        <span
                            v-else
                            class="display text-3xl whitespace-nowrap"
                            >{{ client.name }}</span
                        >
                    </component>
                </Marquee>
            </div>
        </div>
    </section>
</template>
