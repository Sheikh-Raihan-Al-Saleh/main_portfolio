<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import { computed } from 'vue';
import FounderCard from '@/components/company/FounderCard.vue';
import SectionHeading from '@/components/portfolio/SectionHeading.vue';
import { useStudioContent } from '@/composables/useStudioContent';
import { fadeUp, inViewOnce, stagger } from '@/lib/motion';
import type { Company, Profile } from '@/types';

type Props = {
    company: Company;
    profile: Profile;
    /** Shortens the founder card when the section is used on the home page. */
    compact?: boolean;
};

const props = withDefaults(defineProps<Props>(), { compact: false });

const content = useStudioContent(props.company, 'about');

/** The bio is authored as blank-line separated paragraphs. */
const paragraphs = computed(() =>
    (props.company.bio ?? '')
        .split(/\n\s*\n/)
        .map((paragraph) => paragraph.trim())
        .filter(Boolean),
);
</script>

<template>
    <section
        id="about"
        class="bg-noise cv-auto scroll-mt-20 border-t border-border py-20 sm:py-24"
    >
        <div class="container-laravel section-dashed-xl">
            <SectionHeading
                index="05"
                :eyebrow="content.eyebrow"
                :title="content.title"
                :highlight="content.highlight"
                :description="company.tagline ?? ''"
            />

            <motion.div
                :variants="stagger(0.1)"
                initial="hidden"
                while-in-view="visible"
                :in-view-options="inViewOnce"
                class="grid gap-10 lg:grid-cols-2 lg:gap-16"
            >
                <motion.div :variants="fadeUp" class="flex flex-col gap-5">
                    <p
                        v-for="paragraph in paragraphs"
                        :key="paragraph.slice(0, 24)"
                        class="leading-relaxed text-pretty text-muted-foreground"
                    >
                        {{ paragraph }}
                    </p>

                    <div
                        v-if="company.mission"
                        class="mt-2 rounded-xl border-l-2 border-brand bg-brand/5 p-5"
                    >
                        <p
                            class="text-xs font-semibold tracking-widest text-brand uppercase"
                        >
                            {{ content.mission_label }}
                        </p>
                        <p class="mt-2 leading-relaxed font-medium">
                            {{ company.mission }}
                        </p>
                    </div>

                    <Link
                        href="/about"
                        class="group mt-2 inline-flex w-fit items-center gap-1.5 text-sm font-semibold text-brand"
                    >
                        {{ content.more_label }}
                        <span
                            aria-hidden="true"
                            class="transition-transform duration-200 group-hover:translate-x-0.5"
                        >
                            →
                        </span>
                    </Link>
                </motion.div>

                <motion.div :variants="fadeUp">
                    <FounderCard :profile="profile" :detailed="!compact" />
                </motion.div>
            </motion.div>
        </div>
    </section>
</template>
