<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight, Sparkles } from '@lucide/vue';
import { motion, useTransform } from 'motion-v';
import { computed, ref } from 'vue';
import HeroDevicesCarousel from '@/components/landing/HeroDevicesCarousel.vue';
import SectionList from '@/components/landing/SectionList.vue';
import TechBadge from '@/components/portfolio/TechBadge.vue';
import { Button } from '@/components/ui/button';
import { useScrollProgress } from '@/composables/useScrollProgress';
import { useScrollSpy } from '@/composables/useScrollSpy';
import { blurUp, fadeUp, stagger } from '@/lib/motion';
import { cn } from '@/lib/utils';
import type {
    LandingSection,
    Profile,
    Project,
    ProjectLandingPage,
} from '@/types';

type Props = {
    profile: Profile;
    project: Project;
    landingPage: ProjectLandingPage;
    sections: LandingSection[];
};

const props = defineProps<Props>();

const title = computed(
    () =>
        props.landingPage.seo_title ??
        `${props.project.title} — ${props.profile.name}`,
);
const headline = computed(
    () => props.landingPage.headline ?? props.project.title,
);

const accentStyle = computed(() => {
    const style: Record<string, string> = {};

    if (props.landingPage.accent_from) {
        style['--brand'] = props.landingPage.accent_from;
    }

    if (props.landingPage.accent_to) {
        style['--brand-accent'] = props.landingPage.accent_to;
    }

    return style;
});

const navSections = computed(() =>
    props.sections
        .filter((s) => Boolean(s.heading))
        .map((s) => ({ id: `section-${s.id}`, label: s.heading as string })),
);
const activeSection = useScrollSpy(navSections.value.map((e) => e.id));
function anchorFor(section: LandingSection) {
    return `section-${section.id}`;
}

const heroRef = ref<HTMLElement | null>(null);
const { progress } = useScrollProgress({
    target: heroRef,
    offset: ['start start', 'end start'],
});
const heroY = useTransform(progress, [0, 1], [0, 80]);
const heroOpacity = useTransform(progress, [0, 0.85], [1, 0]);
const heroScale = useTransform(progress, [0, 1], [1, 0.985]);
</script>

<template>
    <Head :title="title">
        <meta
            v-if="landingPage.seo_description"
            name="description"
            :content="landingPage.seo_description"
        />
        <meta property="og:title" :content="title" />
        <meta
            v-if="landingPage.seo_description"
            property="og:description"
            :content="landingPage.seo_description"
        />
        <meta
            v-if="landingPage.og_image_url"
            property="og:image"
            :content="landingPage.og_image_url"
        />
        <meta name="twitter:card" content="summary_large_image" />
    </Head>

    <div :style="accentStyle" class="bg-white">
        <!-- HERO — CLEAN 3D -->
        <section
            ref="heroRef"
            class="landing-stage relative overflow-hidden bg-white"
        >
            <div
                class="hero-glow pointer-events-none absolute inset-0"
                aria-hidden="true"
            />
            <div
                class="hero-glow-2 pointer-events-none absolute inset-0"
                aria-hidden="true"
            />
            <div
                class="hero-glow-3 pointer-events-none absolute inset-0"
                aria-hidden="true"
            />
            <!-- subtle grid -->
            <div
                class="pointer-events-none absolute inset-0 opacity-[0.035]"
                aria-hidden="true"
                style="
                    background-image:
                        linear-gradient(to right, #0f172a 1px, transparent 1px),
                        linear-gradient(to bottom, #0f172a 1px, transparent 1px);
                    background-size: 72px 72px;
                "
            />
            <!-- top soft orb -->
            <div
                class="pointer-events-none absolute -top-32 left-1/2 h-[520px] w-[900px] -translate-x-1/2 rounded-full bg-gradient-to-r from-[#2563eb]/10 via-[#7c3aed]/10 to-[#06b6d4]/10 blur-3xl"
                aria-hidden="true"
            />

            <motion.div
                :style="{ y: heroY, opacity: heroOpacity, scale: heroScale }"
                class="relative"
            >
                <div
                    class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8"
                >
                    <!-- top bar -->
                    <div class="mb-8 flex items-center justify-between">
                        <Link
                            href="/projects"
                            class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-medium text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-900"
                        >
                            <ArrowLeft class="size-3.5" />
                            All projects
                        </Link>
                        <span
                            class="hidden items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 sm:inline-flex"
                        >
                            <span
                                class="size-1.5 animate-pulse rounded-full bg-emerald-500"
                            />
                            Live preview
                        </span>
                    </div>

                    <motion.div
                        :variants="stagger(0.09)"
                        initial="hidden"
                        animate="visible"
                        class="mx-auto max-w-4xl text-center"
                    >
                        <motion.div
                            :variants="fadeUp"
                            class="flex justify-center"
                        >
                            <span
                                v-if="landingPage.eyebrow"
                                class="inline-flex items-center gap-2 rounded-full border border-[#2563eb]/15 bg-[#2563eb]/5 px-4 py-1.5 text-xs font-semibold tracking-widest text-[#2563eb] uppercase"
                            >
                                <Sparkles class="size-3.5" />
                                {{ landingPage.eyebrow }}
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-1.5 text-xs font-semibold tracking-widest text-slate-600 uppercase shadow-sm"
                            >
                                <Sparkles class="size-3.5 text-[#2563eb]" />
                                Built for every screen
                            </span>
                        </motion.div>

                        <motion.h1
                            :variants="blurUp"
                            class="landing-section-title mx-auto mt-6 max-w-3xl text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl lg:text-[56px]"
                        >
                            <span class="block">{{ headline }}</span>
                            <span class="text-gradient-hero block"
                                >Accessible Everywhere.</span
                            >
                        </motion.h1>

                        <motion.p
                            v-if="landingPage.subheadline"
                            :variants="fadeUp"
                            class="mx-auto mt-5 max-w-2xl text-base leading-relaxed text-slate-500 sm:text-lg"
                        >
                            {{ landingPage.subheadline }}
                        </motion.p>
                        <motion.p
                            v-else
                            :variants="fadeUp"
                            class="mx-auto mt-5 max-w-2xl text-base leading-relaxed text-slate-500 sm:text-lg"
                        >
                            One dashboard, flawless on desktop, tablet and
                            phone. Real-time sync, zero compromise — engineered
                            for speed and clarity.
                        </motion.p>

                        <motion.div
                            v-if="project.tech_stack?.length"
                            :variants="fadeUp"
                            class="mt-6 flex flex-wrap justify-center gap-2"
                        >
                            <TechBadge
                                v-for="tech in project.tech_stack.slice(0, 8)"
                                :key="tech"
                                :label="tech"
                            />
                        </motion.div>

                        <motion.div
                            :variants="fadeUp"
                            class="mt-8 flex flex-wrap items-center justify-center gap-3"
                        >
                            <Button
                                v-if="landingPage.primary_cta_url"
                                as-child
                                size="lg"
                                class="h-11 rounded-full bg-slate-900 px-7 text-white shadow-lg shadow-slate-900/20 transition hover:bg-black hover:shadow-xl"
                            >
                                <a
                                    :href="landingPage.primary_cta_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    {{
                                        landingPage.primary_cta_label ??
                                        'View live'
                                    }}
                                    <ArrowRight class="size-4" />
                                </a>
                            </Button>
                            <Button
                                v-if="landingPage.secondary_cta_url"
                                as-child
                                size="lg"
                                variant="outline"
                                class="h-11 rounded-full border-slate-200 bg-white px-7 shadow-sm hover:bg-slate-50"
                            >
                                <a :href="landingPage.secondary_cta_url">
                                    {{
                                        landingPage.secondary_cta_label ??
                                        'Get in touch'
                                    }}
                                </a>
                            </Button>
                            <Button
                                v-if="
                                    !landingPage.primary_cta_url &&
                                    !landingPage.secondary_cta_url
                                "
                                as-child
                                size="lg"
                                class="h-11 rounded-full bg-slate-900 px-7 text-white shadow-lg"
                            >
                                <a href="#contact"
                                    >Start project <ArrowRight class="size-4"
                                /></a>
                            </Button>
                        </motion.div>

                        <!-- trust row -->
                        <motion.div
                            :variants="fadeUp"
                            class="mt-8 flex flex-wrap items-center justify-center gap-4 text-xs text-slate-400"
                        >
                            <span class="inline-flex items-center gap-1.5"
                                ><span
                                    class="size-1 rounded-full bg-emerald-500"
                                />
                                Works offline</span
                            >
                            <span class="size-1 rounded-full bg-slate-200" />
                            <span>⚡ 100% responsive</span>
                            <span class="size-1 rounded-full bg-slate-200" />
                            <span>🔒 Secure & fast</span>
                        </motion.div>
                    </motion.div>

                    <!-- 3D DEVICE STAGE — hero devices matching reference -->
                    <motion.div
                        :variants="fadeUp"
                        initial="hidden"
                        animate="visible"
                        transition="{ delay: 0.3 }"
                        class="mt-10 sm:mt-14"
                    >
                        <HeroDevicesCarousel
                            :images="landingPage.hero_media_urls ?? []"
                        />
                    </motion.div>
                </div>
            </motion.div>
        </section>

        <!-- In-page navigation — pill glass -->
        <nav
            v-if="navSections.length > 1"
            class="sticky top-[60px] z-30 border-y border-slate-100 bg-white/80 backdrop-blur-xl supports-[backdrop-filter]:bg-white/70"
            aria-label="Sections on this page"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div
                    class="mask-fade-x -mx-4 flex gap-1.5 overflow-x-auto px-4 py-3 sm:mx-0 sm:px-0"
                >
                    <a
                        v-for="entry in navSections"
                        :key="entry.id"
                        :href="`#${entry.id}`"
                        :class="
                            cn(
                                'shrink-0 rounded-full border px-4 py-1.5 text-sm font-medium whitespace-nowrap transition',
                                activeSection === entry.id
                                    ? 'border-slate-900 bg-slate-900 text-white shadow-sm'
                                    : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-900',
                            )
                        "
                    >
                        {{ entry.label }}
                    </a>
                </div>
            </div>
        </nav>

        <!-- Composed sections — new clean 3D -->
        <div class="bg-[#fcfdff]">
            <SectionList :sections="sections" :anchor-for="anchorFor" />
        </div>
    </div>
</template>
