<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight } from '@lucide/vue';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import SmartImage from '@/components/media/SmartImage.vue';
import Reveal from '@/components/site/Reveal.vue';
import { useSite } from '@/composables/useSite';
import { fillTokens } from '@/lib/text';
import { about, founder as founderRoute } from '@/routes';
import type { FounderBridgeProps } from '@/types';

/**
 * Two doors side by side: the studio (who you contract with) and the
 * founder (who writes the code), each linking to its own page.
 */
defineProps<FounderBridgeProps>();

const { studio, founder } = useSite();
</script>

<template>
    <section class="section-tight">
        <div class="container-site grid gap-5 md:grid-cols-2">
            <Reveal>
                <Link
                    :href="about().url"
                    class="paper-card group flex h-full flex-col p-8 transition-colors duration-500 hover:border-ink sm:p-10"
                >
                    <div class="flex items-center justify-between">
                        <p class="eyebrow">{{ content.studio_label }}</p>
                        <BrandLogo variant="mark" size="md" />
                    </div>
                    <h3 class="display display-sm mt-10">
                        {{
                            fillTokens(content.studio_heading, {
                                name: studio.name,
                            })
                        }}
                    </h3>
                    <p class="body-copy mt-4">{{ content.studio_text }}</p>
                    <span
                        class="mt-auto inline-flex items-center gap-2 pt-10 font-medium"
                    >
                        <span class="link-underline">{{
                            content.studio_link
                        }}</span>
                        <ArrowUpRight
                            class="size-4 transition-transform group-hover:translate-x-1"
                            aria-hidden="true"
                        />
                    </span>
                </Link>
            </Reveal>

            <Reveal :delay="0.1">
                <Link
                    :href="founderRoute().url"
                    data-tone="dark"
                    class="group flex h-full flex-col overflow-hidden rounded-[1.25rem] bg-ink p-8 text-paper sm:p-10"
                >
                    <div class="flex items-center justify-between">
                        <p class="eyebrow text-paper/60">
                            {{ content.founder_label }}
                        </p>
                        <SmartImage
                            :src="founder.avatar_url"
                            :alt="founder.name"
                            class="size-16 rounded-full bg-paper/10"
                        >
                            <template #fallback>
                                <span class="display text-2xl text-paper">{{
                                    founder.name.charAt(0)
                                }}</span>
                            </template>
                        </SmartImage>
                    </div>
                    <h3 class="display display-sm mt-10">
                        {{
                            fillTokens(content.founder_heading, {
                                name: founder.name,
                            })
                        }}
                    </h3>
                    <p class="mt-4 leading-relaxed text-paper/70">
                        {{
                            fillTokens(content.founder_text, {
                                role: founder.headline ?? 'Founder',
                                name: founder.name,
                            })
                        }}
                    </p>
                    <span
                        class="mt-auto inline-flex items-center gap-2 pt-10 font-medium text-brand-text"
                    >
                        <span class="link-underline">{{
                            content.founder_link
                        }}</span>
                        <ArrowUpRight
                            class="size-4 transition-transform group-hover:translate-x-1"
                            aria-hidden="true"
                        />
                    </span>
                </Link>
            </Reveal>
        </div>
    </section>
</template>
