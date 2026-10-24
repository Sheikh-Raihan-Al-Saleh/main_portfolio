<script setup lang="ts">
import { Briefcase, Mail, MapPin, Phone, Quote } from '@lucide/vue';
import { computed } from 'vue';
import Reveal from '@/components/site/Reveal.vue';
import SectionHeader from '@/components/site/SectionHeader.vue';
import { contentConfig } from '@/lib/content';
import type { Profile } from '@/types';

/**
 * About the founder: the bio, the founder's note as a quote card, and the
 * contact facts as small tiles.
 */
type Props = {
    profile: Profile;
};

const props = defineProps<Props>();

const content = computed(() => contentConfig(props.profile));
const copy = computed(() => content.value.sections.about);

function split(text: string | null): string[] {
    return (text ?? '')
        .split(/\n\s*\n/)
        .map((paragraph) => paragraph.trim())
        .filter(Boolean);
}

const paragraphs = computed(() => split(props.profile.bio));
const message = computed(() => split(props.profile.founder_message));

const facts = computed(() =>
    [
        {
            icon: Briefcase,
            label: content.value.about.role_label,
            value: props.profile.headline,
            href: undefined,
        },
        {
            icon: MapPin,
            label: content.value.about.location_label,
            value: props.profile.location,
            href: undefined,
        },
        {
            icon: Mail,
            label: content.value.about.email_label,
            value: props.profile.public_email,
            href: props.profile.public_email
                ? `mailto:${props.profile.public_email}`
                : undefined,
        },
        {
            icon: Phone,
            label: content.value.about.phone_label,
            value: props.profile.phone,
            href: undefined,
        },
    ].filter((fact) => Boolean(fact.value)),
);
</script>

<template>
    <section id="about" class="site-section scroll-mt-24 bg-card/60">
        <div class="site-container grid gap-12 lg:grid-cols-12 lg:gap-16">
            <div class="flex flex-col gap-6 lg:col-span-6">
                <SectionHeader
                    align="left"
                    :eyebrow="copy.eyebrow"
                    :title="copy.title"
                    :highlight="copy.highlight"
                    :description="copy.description"
                />
                <Reveal
                    v-for="(paragraph, i) in paragraphs"
                    :key="paragraph.slice(0, 24)"
                    :delay="i * 0.05"
                >
                    <p
                        class="text-lg leading-relaxed text-pretty text-muted-foreground"
                    >
                        {{ paragraph }}
                    </p>
                </Reveal>

                <Reveal v-if="facts.length" class="grid gap-3 sm:grid-cols-2">
                    <component
                        :is="fact.href ? 'a' : 'div'"
                        v-for="fact in facts"
                        :key="fact.label"
                        :href="fact.href"
                        class="ui-card flex items-center gap-3 rounded-2xl p-4"
                        :class="fact.href ? 'ui-card-hover' : ''"
                    >
                        <span class="ui-icon-tile size-10 shrink-0"
                            ><component :is="fact.icon" class="size-4"
                        /></span>
                        <span class="min-w-0">
                            <span
                                class="block text-xs text-muted-foreground capitalize"
                                >{{ fact.label }}</span
                            >
                            <span
                                class="block truncate text-sm font-semibold"
                                >{{ fact.value }}</span
                            >
                        </span>
                    </component>
                </Reveal>
            </div>

            <Reveal v-if="message.length" :delay="0.1" class="lg:col-span-6">
                <figure
                    class="ui-card ui-glow-border relative flex h-full flex-col gap-5 overflow-hidden p-8 lg:p-10"
                >
                    <div
                        class="ui-blob ui-blob-a -top-24 -right-24 size-72 opacity-30"
                        aria-hidden="true"
                    />
                    <span class="ui-icon-tile relative"
                        ><Quote class="size-5"
                    /></span>
                    <p class="relative text-sm font-medium text-brand">
                        {{ copy.message_label ?? "Founder's note" }}
                    </p>
                    <blockquote
                        class="relative flex flex-col gap-4 text-lg leading-relaxed text-pretty"
                    >
                        <p v-for="(paragraph, i) in message" :key="i">
                            {{ paragraph }}
                        </p>
                    </blockquote>
                    <figcaption
                        class="relative mt-auto flex items-center gap-3 pt-2"
                    >
                        <img
                            v-if="profile.avatar_url"
                            :src="profile.avatar_url"
                            :alt="''"
                            class="size-10 rounded-full object-cover"
                        />
                        <span>
                            <span class="block text-sm font-semibold">{{
                                profile.name
                            }}</span>
                            <span
                                v-if="profile.headline"
                                class="block text-xs text-muted-foreground"
                                >{{ profile.headline }}</span
                            >
                        </span>
                    </figcaption>
                </figure>
            </Reveal>
        </div>
    </section>
</template>
