<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    Briefcase,
    CalendarRange,
    ChevronDown,
    GraduationCap,
    MapPin,
} from '@lucide/vue';
import { AnimatePresence, motion } from 'motion-v';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import SectionHeading from '@/components/portfolio/SectionHeading.vue';
import { contentConfig } from '@/lib/content';
import { fadeUp, inViewOnce, stagger } from '@/lib/motion';
import type { Education, Experience, Profile } from '@/types';

type Props = {
    experiences: Experience[];
    educations: Education[];
};

const props = defineProps<Props>();

const page = usePage<{ profile: Profile | null }>();
const content = computed(() => contentConfig(page.props.profile));

function formatDate(value: string | null | undefined): string {
    if (!value) {
        return 'Present';
    }

    return new Date(value).toLocaleDateString(undefined, {
        month: 'short',
        year: 'numeric',
    });
}

const openExperience = ref<number | null>(null);

const timeline = computed(() => props.experiences);
const educationItems = computed(() => props.educations);

/* ── Carousel ── */
const trackRef = ref<HTMLElement | null>(null);
const canScrollLeft = ref(false);
const canScrollRight = ref(true);

function checkScroll() {
    const el = trackRef.value;

    if (!el) {
        return;
    }

    canScrollLeft.value = el.scrollLeft > 4;
    canScrollRight.value = el.scrollLeft < el.scrollWidth - el.clientWidth - 4;
}

function scrollBy(direction: -1 | 1) {
    const el = trackRef.value;

    if (!el) {
        return;
    }

    const cardWidth =
        el.querySelector<HTMLElement>(':scope > *')?.offsetWidth ?? 300;

    el.scrollBy({ left: direction * (cardWidth + 24), behavior: 'smooth' });
}

onMounted(() => {
    trackRef.value?.addEventListener('scroll', checkScroll, { passive: true });
    checkScroll();
});

onUnmounted(() => {
    trackRef.value?.removeEventListener('scroll', checkScroll);
});
</script>

<template>
    <section
        id="experience"
        class="bg-noise relative scroll-mt-20 border-t border-border pt-20 sm:pt-24"
    >
        <div class="corner-dot corner-dot-tl" aria-hidden="true" />
        <div class="corner-dot corner-dot-tr" aria-hidden="true" />

        <div class="container-laravel section-dashed-xl">
            <SectionHeading
                index="04"
                :eyebrow="content.sections.experience.eyebrow"
                :title="content.sections.experience.title"
                :highlight="content.sections.experience.highlight"
                :description="content.sections.experience.description"
            />

            <!-- Carousel -->
            <motion.div
                :variants="fadeUp"
                initial="hidden"
                while-in-view="visible"
                :in-view-options="inViewOnce"
                class="relative"
            >
                <!-- Nav arrows -->
                <div
                    class="pointer-events-none absolute inset-y-0 -left-2 z-10 flex items-center"
                >
                    <button
                        type="button"
                        aria-label="Scroll left"
                        :disabled="!canScrollLeft"
                        class="pointer-events-auto grid size-9 place-items-center rounded-lg border border-border bg-card text-foreground shadow-sm transition-all duration-200 hover:text-brand disabled:pointer-events-none disabled:opacity-30"
                        @click="scrollBy(-1)"
                    >
                        <ArrowLeft class="size-4" />
                    </button>
                </div>
                <div
                    class="pointer-events-none absolute inset-y-0 -right-2 z-10 flex items-center"
                >
                    <button
                        type="button"
                        aria-label="Scroll right"
                        :disabled="!canScrollRight"
                        class="pointer-events-auto grid size-9 place-items-center rounded-lg border border-border bg-card text-foreground shadow-sm transition-all duration-200 hover:text-brand disabled:pointer-events-none disabled:opacity-30"
                        @click="scrollBy(1)"
                    >
                        <ArrowRight class="size-4" />
                    </button>
                </div>

                <!-- Fade edges -->
                <div
                    v-if="canScrollLeft"
                    aria-hidden="true"
                    class="pointer-events-none absolute inset-y-0 left-0 z-[5] w-12 bg-gradient-to-r from-background to-transparent"
                />
                <div
                    v-if="canScrollRight"
                    aria-hidden="true"
                    class="pointer-events-none absolute inset-y-0 right-0 z-[5] w-12 bg-gradient-to-l from-background to-transparent"
                />

                <!-- Track -->
                <div
                    ref="trackRef"
                    class="flex [scrollbar-width:none] gap-6 overflow-x-auto scroll-smooth pt-1 pb-4 [&::-webkit-scrollbar]:hidden"
                    style="scroll-snap-type: x mandatory"
                >
                    <article
                        v-for="experience in timeline"
                        :key="experience.id"
                        class="group w-[340px] shrink-0 snap-start sm:w-[380px]"
                        style="scroll-snap-align: start"
                    >
                        <!-- Dark card — Laravel testimonial style -->
                        <div
                            class="relative flex h-full flex-col rounded-lg bg-neutral-900 p-5 text-white shadow-lg sm:p-6 dark:bg-card dark:text-foreground"
                        >
                            <!-- Top accent line -->
                            <div
                                class="absolute inset-x-0 top-0 h-0.5 bg-brand"
                                aria-hidden="true"
                            />

                            <div
                                class="mb-4 flex items-start justify-between gap-3"
                            >
                                <div class="min-w-0">
                                    <h3
                                        class="text-base font-bold tracking-tight"
                                    >
                                        {{ experience.role }}
                                    </h3>
                                    <p class="mt-1">
                                        <a
                                            v-if="experience.company_url"
                                            :href="experience.company_url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-sm font-semibold text-brand hover:underline"
                                        >
                                            {{ experience.company }}
                                        </a>
                                        <span
                                            v-else
                                            class="text-sm font-semibold text-brand"
                                        >
                                            {{ experience.company }}
                                        </span>
                                    </p>
                                </div>

                                <span
                                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-white/10 px-2.5 py-1 font-mono text-[10px] text-white/70 dark:bg-muted dark:text-muted-foreground"
                                >
                                    <CalendarRange
                                        class="size-3"
                                        aria-hidden="true"
                                    />
                                    {{ formatDate(experience.start_date) }}
                                </span>
                            </div>

                            <div
                                class="mb-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-white/60 dark:text-muted-foreground"
                            >
                                <span
                                    v-if="experience.employment_type"
                                    class="inline-flex items-center gap-1.5"
                                >
                                    <Briefcase
                                        class="size-3"
                                        aria-hidden="true"
                                    />
                                    {{ experience.employment_type }}
                                </span>
                                <span
                                    v-if="experience.location"
                                    class="inline-flex items-center gap-1.5"
                                >
                                    <MapPin class="size-3" aria-hidden="true" />
                                    {{ experience.location }}
                                </span>
                            </div>

                            <p
                                v-if="experience.description"
                                class="mb-4 line-clamp-4 text-sm leading-relaxed text-white/70 dark:text-foreground/70"
                            >
                                {{ experience.description }}
                            </p>

                            <AnimatePresence :initial="false">
                                <motion.div
                                    v-if="
                                        experience.highlights?.length &&
                                        openExperience === experience.id
                                    "
                                    :initial="{ height: 0, opacity: 0 }"
                                    :animate="{ height: 'auto', opacity: 1 }"
                                    :exit="{ height: 0, opacity: 0 }"
                                    :transition="{
                                        duration: 0.3,
                                        ease: [0.16, 1, 0.3, 1],
                                    }"
                                    class="overflow-hidden"
                                >
                                    <ul
                                        class="mb-4 flex flex-col gap-1.5 border-t border-white/10 pt-4 dark:border-border"
                                    >
                                        <li
                                            v-for="(
                                                highlight, i
                                            ) in experience.highlights"
                                            :key="i"
                                            class="flex gap-2 text-sm leading-relaxed text-white/75 dark:text-foreground/75"
                                        >
                                            <span
                                                class="mt-2 size-1.5 shrink-0 rounded-full bg-brand"
                                                aria-hidden="true"
                                            />
                                            {{ highlight }}
                                        </li>
                                    </ul>
                                </motion.div>
                            </AnimatePresence>

                            <div class="mt-auto pt-2">
                                <button
                                    v-if="experience.highlights?.length"
                                    type="button"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-white/20 px-3 py-1.5 text-[11px] font-semibold text-white/70 transition-colors duration-200 hover:border-brand hover:text-white dark:border-border dark:text-muted-foreground dark:hover:text-foreground"
                                    :aria-expanded="
                                        openExperience === experience.id
                                    "
                                    @click="
                                        openExperience =
                                            openExperience === experience.id
                                                ? null
                                                : experience.id
                                    "
                                >
                                    {{
                                        openExperience === experience.id
                                            ? 'collapse'
                                            : `highlights (${experience.highlights.length})`
                                    }}
                                    <ChevronDown
                                        class="size-3.5 transition-transform duration-300"
                                        :class="
                                            openExperience === experience.id
                                                ? 'rotate-180 text-brand'
                                                : ''
                                        "
                                        aria-hidden="true"
                                    />
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
            </motion.div>

            <!-- Education -->
            <div v-if="educationItems.length" class="mx-auto mt-16 max-w-5xl">
                <motion.div
                    :variants="stagger(0.1)"
                    initial="hidden"
                    while-in-view="visible"
                    :in-view-options="inViewOnce"
                    class="mb-6 flex items-center gap-2.5"
                >
                    <span
                        class="grid size-8 place-items-center rounded-lg bg-muted text-muted-foreground"
                    >
                        <GraduationCap class="size-4" aria-hidden="true" />
                    </span>
                    <h3
                        class="text-sm font-bold tracking-[0.12em] text-foreground uppercase"
                    >
                        Education
                    </h3>
                </motion.div>

                <div
                    class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <motion.div
                        v-for="education in educationItems"
                        :key="education.id"
                        :variants="fadeUp"
                        initial="hidden"
                        while-in-view="visible"
                        :in-view-options="inViewOnce"
                        class="flex flex-col rounded-lg border border-border p-5 transition-shadow duration-300 hover:shadow-lg sm:p-6"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <span class="text-sm font-bold text-foreground">
                                {{
                                    [education.degree, education.field_of_study]
                                        .filter(Boolean)
                                        .join(', ')
                                }}
                            </span>
                            <span
                                v-if="education.grade"
                                class="shrink-0 rounded-lg bg-muted px-2 py-0.5 font-mono text-[10px] text-muted-foreground"
                            >
                                {{ education.grade }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm font-semibold text-brand">
                            {{ education.institution }}
                        </p>
                        <p
                            class="mt-auto pt-3 text-[11px] text-muted-foreground"
                        >
                            {{ formatDate(education.start_date) }} →
                            {{ formatDate(education.end_date) }}
                        </p>
                    </motion.div>
                </div>
            </div>
        </div>
    </section>
</template>
