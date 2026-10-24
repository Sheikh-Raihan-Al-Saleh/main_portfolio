<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Mail, MapPin, Quote } from '@lucide/vue';
import { computed } from 'vue';
import AnimatedCounter from '@/components/portfolio/AnimatedCounter.vue';
import CircuitBackground from '@/components/site/CircuitBackground.vue';
import Reveal from '@/components/site/Reveal.vue';
import SectionHeader from '@/components/site/SectionHeader.vue';
import StudioContact from '@/components/studio/StudioContact.vue';
import StudioFounderCard from '@/components/studio/StudioFounderCard.vue';
import type { Client, Company, CompanyStats, Profile } from '@/types';

type Props = {
    company: Company;
    profile: Profile;
    clients: Client[];
    stats: CompanyStats;
};

const props = defineProps<Props>();

const title = computed(() =>
    props.company.meta_title
        ? `${props.company.meta_title} — About`
        : `About ${props.company.name}`,
);

type Figure = { value: number; suffix: string; label: string };

const figures = computed<Figure[]>(() =>
    [
        {
            value: props.stats.yearsInBusiness,
            suffix: '+',
            label: 'Years in business',
        },
        { value: props.stats.projects, suffix: '+', label: 'Products shipped' },
        { value: props.stats.clients, suffix: '', label: 'Clients served' },
    ].filter((figure): figure is Figure =>
        Number.isFinite(figure.value as number),
    ),
);

/** The bio is authored as blank-line separated paragraphs. */
const paragraphs = computed(() =>
    (props.company.bio ?? '')
        .split(/\n\s*\n/)
        .map((paragraph) => paragraph.trim())
        .filter(Boolean),
);
</script>

<template>
    <Head :title="title">
        <meta
            v-if="company.meta_description"
            name="description"
            :content="company.meta_description"
        />
        <meta property="og:title" :content="title" />
    </Head>

    <!-- Page header -->
    <section class="relative overflow-hidden pt-36 pb-16 lg:pt-44 lg:pb-20">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <CircuitBackground />
            <div class="ui-blob ui-blob-a -top-32 left-1/4 size-[30rem]" />
            <div class="ui-blob ui-blob-b -top-10 right-1/4 size-[24rem]" />
        </div>

        <div
            class="site-container relative flex flex-col items-center gap-6 text-center"
        >
            <Reveal><span class="ui-eyebrow">About us</span></Reveal>
            <Reveal :delay="0.05">
                <h1 class="ui-display ui-h1 max-w-4xl">
                    {{ company.headline ?? company.name }}
                </h1>
            </Reveal>
            <Reveal v-if="company.tagline" :delay="0.1">
                <p
                    class="max-w-2xl text-lg leading-relaxed text-muted-foreground sm:text-xl"
                >
                    {{ company.tagline }}
                </p>
            </Reveal>
            <Reveal :delay="0.15" class="flex flex-wrap justify-center gap-3">
                <a
                    v-if="company.public_email"
                    :href="`mailto:${company.public_email}`"
                    class="ui-btn ui-btn-secondary ui-btn-sm"
                >
                    <Mail class="size-4" />
                    {{ company.public_email }}
                </a>
                <span
                    v-if="company.location"
                    class="ui-btn ui-btn-secondary ui-btn-sm pointer-events-none"
                >
                    <MapPin class="size-4" />
                    {{ company.location }}
                </span>
            </Reveal>

            <Reveal
                v-if="figures.length"
                :delay="0.2"
                class="mt-6 w-full max-w-3xl"
            >
                <dl class="grid gap-4 sm:grid-cols-3">
                    <div
                        v-for="figure in figures"
                        :key="figure.label"
                        class="ui-card flex flex-col-reverse gap-1 p-6"
                    >
                        <dt class="text-sm text-muted-foreground">
                            {{ figure.label }}
                        </dt>
                        <dd class="ui-display text-gradient text-4xl">
                            <AnimatedCounter
                                :value="figure.value"
                                :suffix="figure.suffix"
                            />
                        </dd>
                    </div>
                </dl>
            </Reveal>
        </div>
    </section>

    <!-- Studio story -->
    <section class="site-section bg-card/60">
        <div class="site-container grid gap-12 lg:grid-cols-12 lg:gap-16">
            <SectionHeader
                align="left"
                eyebrow="Who we are"
                title="How the studio works"
                highlight="works"
                class="lg:col-span-5"
            />
            <div class="flex flex-col gap-5 lg:col-span-7">
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
                <Reveal v-if="company.mission">
                    <blockquote class="ui-card flex gap-4 p-6">
                        <span class="ui-icon-tile shrink-0"
                            ><Quote class="size-5"
                        /></span>
                        <div>
                            <p class="text-sm font-medium text-brand">
                                Our mission
                            </p>
                            <p class="mt-1 text-xl leading-snug font-semibold">
                                {{ company.mission }}
                            </p>
                        </div>
                    </blockquote>
                </Reveal>
                <Reveal>
                    <Link href="/projects" class="ui-btn ui-btn-primary w-fit">
                        See the work
                        <ArrowRight class="size-4" />
                    </Link>
                </Reveal>
            </div>
        </div>
    </section>

    <!-- Clients -->
    <section
        v-if="clients.length"
        id="clients"
        class="site-section scroll-mt-24"
    >
        <div class="site-container">
            <SectionHeader
                eyebrow="Clients"
                title="Who we work with"
                highlight="work with"
                description="The teams who trusted a small studio with their platform."
            />
            <div
                class="mt-14 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5"
            >
                <Reveal
                    v-for="(client, i) in clients"
                    :key="client.id"
                    :delay="(i % 5) * 0.05"
                >
                    <component
                        :is="client.website_url ? 'a' : 'div'"
                        :href="client.website_url ?? undefined"
                        :target="client.website_url ? '_blank' : undefined"
                        :rel="
                            client.website_url
                                ? 'noopener noreferrer'
                                : undefined
                        "
                        class="ui-card ui-card-hover flex h-full flex-col items-center justify-center gap-3 p-6 text-center"
                    >
                        <img
                            v-if="client.logo_url"
                            :src="client.logo_url"
                            :alt="client.name"
                            loading="lazy"
                            class="h-12 w-auto max-w-[80%] object-contain"
                        />
                        <span class="text-sm font-semibold">{{
                            client.name
                        }}</span>
                        <span
                            v-if="client.industry"
                            class="text-xs text-muted-foreground"
                            >{{ client.industry }}</span
                        >
                    </component>
                </Reveal>
            </div>
        </div>
    </section>

    <!-- Founder -->
    <section id="founder" class="site-section scroll-mt-24 bg-card/60">
        <div class="site-container">
            <SectionHeader
                eyebrow="The founder"
                title="Who you will actually talk to"
                highlight="talk to"
                description="The studio is deliberately small, so the person who scopes the work is the person who builds it."
            />
            <Reveal class="mx-auto mt-14 max-w-4xl">
                <StudioFounderCard :profile="profile" detailed />
            </Reveal>
        </div>
    </section>

    <StudioContact :company="company" />
</template>
