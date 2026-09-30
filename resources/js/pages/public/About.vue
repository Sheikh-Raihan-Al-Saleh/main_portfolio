<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ArrowUpRight, Mail, MapPin } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import ClientLogosSection from '@/components/company/ClientLogosSection.vue';
import CompanyContactSection from '@/components/company/CompanyContactSection.vue';
import FounderCard from '@/components/company/FounderCard.vue';
import AnimatedCounter from '@/components/portfolio/AnimatedCounter.vue';
import SectionHeading from '@/components/portfolio/SectionHeading.vue';
import { fadeUp, inViewOnce, stagger } from '@/lib/motion';
import type { Client, Company, CompanyStats, Profile } from '@/types';

type Props = {
    company: Company;
    profile: Profile;
    clients: Client[];
    stats: CompanyStats;
};

const props = defineProps<Props>();

const title = computed(
    () =>
        props.company.meta_title
            ? `${props.company.meta_title} — About`
            : `About ${props.company.name}`,
);

type Figure = { value: number; suffix: string; label: string };

const figures = computed<Figure[]>(() =>
    [
        { value: props.stats.yearsInBusiness, suffix: '+', label: 'years in business' },
        { value: props.stats.projects, suffix: '+', label: 'products shipped' },
        { value: props.stats.clients, suffix: '', label: 'clients served' },
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
    <section
        id="top"
        class="bg-noise relative scroll-mt-20 overflow-hidden border-b border-border pt-32 pb-16 sm:pt-40 sm:pb-20"
    >
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div
                class="absolute -top-24 -left-24 h-[420px] w-[420px] rounded-full bg-gradient-to-br from-brand/15 to-transparent blur-3xl"
            />
        </div>

        <div class="container-laravel relative">
            <motion.div
                :variants="stagger(0.1)"
                initial="hidden"
                animate="visible"
                class="flex max-w-3xl flex-col gap-5"
            >
                <motion.p
                    :variants="fadeUp"
                    class="text-xs font-semibold tracking-widest text-brand uppercase"
                >
                    About us
                </motion.p>

                <motion.h1
                    :variants="fadeUp"
                    class="font-display text-4xl font-bold tracking-tight text-balance sm:text-5xl"
                >
                    {{ company.headline ?? company.name }}
                </motion.h1>

                <motion.p
                    :variants="fadeUp"
                    class="text-lg leading-relaxed text-muted-foreground"
                >
                    {{ company.tagline }}
                </motion.p>

                <motion.div
                    :variants="fadeUp"
                    class="mt-2 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-muted-foreground"
                >
                    <a
                        v-if="company.public_email"
                        :href="`mailto:${company.public_email}`"
                        class="inline-flex items-center gap-2 transition-colors hover:text-foreground"
                    >
                        <Mail class="size-4" />
                        {{ company.public_email }}
                    </a>
                    <span
                        v-if="company.location"
                        class="inline-flex items-center gap-2"
                    >
                        <MapPin class="size-4" />
                        {{ company.location }}
                    </span>
                </motion.div>
            </motion.div>
        </div>
    </section>

    <!-- Studio story -->
    <section class="bg-noise scroll-mt-20 py-20 sm:py-24">
        <div class="container-laravel section-dashed-xl">
            <SectionHeading
                index="01"
                eyebrow="Who we are"
                title="How the studio works"
                highlight="works"
                :description="company.headline ?? ''"
            />

            <motion.div
                :variants="stagger(0.1)"
                initial="hidden"
                while-in-view="visible"
                :in-view-options="inViewOnce"
                class="grid gap-10 lg:grid-cols-5 lg:gap-16"
            >
                <motion.div
                    :variants="fadeUp"
                    class="flex flex-col gap-5 lg:col-span-3"
                >
                    <p
                        v-for="paragraph in paragraphs"
                        :key="paragraph.slice(0, 24)"
                        class="leading-relaxed text-pretty text-muted-foreground"
                    >
                        {{ paragraph }}
                    </p>

                    <div
                        v-if="company.mission"
                        class="mt-2 rounded-xl border-l-2 border-brand bg-brand/5 p-6"
                    >
                        <p
                            class="text-xs font-semibold tracking-widest text-brand uppercase"
                        >
                            Our mission
                        </p>
                        <p class="mt-2 text-lg leading-relaxed font-medium">
                            {{ company.mission }}
                        </p>
                    </div>
                </motion.div>

                <motion.div
                    :variants="fadeUp"
                    class="flex flex-col gap-6 lg:col-span-2"
                >
                    <dl
                        v-if="figures.length"
                        class="flex flex-col gap-5 rounded-xl border border-border p-6"
                    >
                        <div
                            v-for="figure in figures"
                            :key="figure.label"
                            class="flex items-baseline justify-between gap-4 border-b border-border pb-5 last:border-0 last:pb-0"
                        >
                            <dt class="text-sm text-muted-foreground">
                                {{ figure.label }}
                            </dt>
                            <dd class="text-2xl font-bold tabular-nums">
                                <AnimatedCounter
                                    :value="figure.value"
                                    :suffix="figure.suffix"
                                />
                            </dd>
                        </div>
                    </dl>

                    <a
                        href="/projects"
                        class="btn-laravel group inline-flex w-fit items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold"
                    >
                        see the work
                        <ArrowUpRight
                            class="size-4 text-brand transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                            aria-hidden="true"
                        />
                    </a>
                </motion.div>
            </motion.div>
        </div>
    </section>

    <!-- Clients -->
    <ClientLogosSection
        :clients="clients"
        :company="company"
        index="02"
        eyebrow="Clients"
        heading="Who we work with"
        description="The teams who trusted a small studio with their platform."
    />

    <!-- Founder -->
    <section
        id="founder"
        class="bg-noise scroll-mt-20 border-t border-border py-20 sm:py-24"
    >
        <div class="container-laravel section-dashed-xl">
            <SectionHeading
                index="03"
                eyebrow="The founder"
                title="Who you will actually talk to"
                highlight="talk"
                description="The studio is deliberately small, so the person who scopes the work is the person who builds it."
            />

            <div class="mt-12">
                <FounderCard :profile="profile" detailed />
            </div>
        </div>
    </section>

    <CompanyContactSection :company="company" />
</template>
