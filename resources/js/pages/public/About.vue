<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowUpRight } from '@lucide/vue';
import { computed } from 'vue';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import SmartImage from '@/components/media/SmartImage.vue';
import AnimatedCounter from '@/components/site/AnimatedCounter.vue';
import ContactSection from '@/components/site/ContactSection.vue';
import Reveal from '@/components/site/Reveal.vue';
import ClientStrip from '@/components/studio/ClientStrip.vue';
import { useSite } from '@/composables/useSite';
import { paragraphs } from '@/lib/text';
import { founder as founderRoute } from '@/routes';
import type { Client, StudioStats } from '@/types';

/**
 * The studio's About page (/about): the story, the mission, the numbers,
 * the people behind it and the clients.
 */
const props = defineProps<{
    clients: Client[];
    stats: StudioStats;
}>();

const { studio, founder } = useSite();

const figures = computed(() =>
    [
        {
            value: props.stats.yearsInBusiness,
            label: 'Years in business',
            suffix: '+',
        },
        { value: props.stats.projects, label: 'Projects shipped', suffix: '' },
        { value: props.stats.clients, label: 'Clients served', suffix: '' },
    ].filter(
        (figure): figure is { value: number; label: string; suffix: string } =>
            figure.value !== null,
    ),
);
</script>

<template>
    <Head :title="`About ${studio.name}`">
        <meta
            v-if="studio.meta.description"
            name="description"
            :content="studio.meta.description"
        />
    </Head>

    <section class="container-site pt-10 pb-20 sm:pt-16">
        <Reveal>
            <p class="eyebrow">About the studio</p>
            <h1 class="display display-xl mt-8 max-w-[12ch]">
                {{ studio.headline || 'A small studio,' }}
                <em>{{ studio.short_name }}</em>
            </h1>
        </Reveal>

        <div class="mt-16 grid gap-14 lg:grid-cols-12">
            <Reveal class="lg:col-span-4">
                <div class="paper-card flex flex-col items-start gap-8 p-8">
                    <BrandLogo size="xl" />
                    <p v-if="studio.tagline" class="body-copy">
                        {{ studio.tagline }}
                    </p>
                    <dl class="grid w-full">
                        <div
                            v-if="studio.founded_year"
                            class="rule flex justify-between py-3"
                        >
                            <dt class="meta">Founded</dt>
                            <dd class="font-medium">
                                {{ studio.founded_year }}
                            </dd>
                        </div>
                        <div
                            v-if="studio.location"
                            class="rule flex justify-between py-3"
                        >
                            <dt class="meta">Based in</dt>
                            <dd class="font-medium">{{ studio.location }}</dd>
                        </div>
                    </dl>
                </div>
            </Reveal>

            <Reveal :delay="0.1" class="lg:col-span-7 lg:col-start-6">
                <p
                    v-for="(paragraph, i) in paragraphs(studio.bio)"
                    :key="i"
                    class="lead mb-6"
                >
                    {{ paragraph }}
                </p>

                <blockquote
                    v-if="studio.mission"
                    class="mt-12 border-l-2 border-brand pl-6"
                >
                    <p class="meta mb-3">Our mission</p>
                    <p class="display text-4xl leading-tight sm:text-5xl">
                        “{{ studio.mission }}”
                    </p>
                </blockquote>
            </Reveal>
        </div>
    </section>

    <section
        v-if="figures.length"
        class="border-y border-ink bg-ink text-paper"
        data-tone="dark"
    >
        <dl class="container-site grid gap-10 py-16 sm:grid-cols-3">
            <div v-for="figure in figures" :key="figure.label">
                <dd class="display text-7xl sm:text-8xl">
                    <AnimatedCounter
                        :value="figure.value"
                        :suffix="figure.suffix"
                    />
                </dd>
                <dt class="meta mt-2 text-paper/60">{{ figure.label }}</dt>
            </div>
        </dl>
    </section>

    <section class="section">
        <div class="container-site grid items-center gap-14 lg:grid-cols-12">
            <Reveal class="lg:col-span-5">
                <SmartImage
                    :src="founder.avatar_url"
                    :alt="founder.name"
                    ratio="4/5"
                    class="rounded-[2rem]"
                >
                    <template #fallback>
                        <span class="display text-[9rem] text-stone/30">{{
                            founder.name.charAt(0)
                        }}</span>
                    </template>
                </SmartImage>
            </Reveal>
            <Reveal :delay="0.1" class="lg:col-span-6 lg:col-start-7">
                <p class="eyebrow">The founder</p>
                <h2 class="display display-md mt-6">{{ founder.name }}</h2>
                <p class="mt-3 text-xl text-brand-text">
                    {{ founder.headline }}
                </p>
                <p
                    v-for="(paragraph, i) in paragraphs(
                        founder.founder_message || founder.bio,
                    )"
                    :key="i"
                    class="body-copy mt-6"
                >
                    {{ paragraph }}
                </p>
                <Link :href="founderRoute().url" class="btn btn-ink mt-10">
                    See the founder's portfolio
                    <ArrowUpRight class="btn-arrow size-4" aria-hidden="true" />
                </Link>
            </Reveal>
        </div>
    </section>

    <ClientStrip :clients="clients" />

    <ContactSection
        site="studio"
        eyebrow="Start a project"
        title="Tell us what needs building"
        highlight="building"
        description="Describe the problem and we will tell you honestly whether software is the fix, what it would take, and what it would cost."
        :email="studio.public_email"
        :phone="studio.phone"
        :location="studio.location"
        :socials="studio.socials"
    />
</template>
