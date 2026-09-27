<script setup lang="ts">
import { Briefcase, Mail, MapPin, Phone, Quote } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import SectionHeading from '@/components/portfolio/SectionHeading.vue';
import { getInitials } from '@/composables/useInitials';
import { contentConfig } from '@/lib/content';
import { fadeUp, inViewOnce, stagger } from '@/lib/motion';
import type { Profile } from '@/types';

type Props = {
    profile: Profile;
};

const props = defineProps<Props>();

const content = computed(() => contentConfig(props.profile));

const paragraphs = computed(() =>
    (props.profile.bio ?? '')
        .split(/\n\s*\n/)
        .map((paragraph) => paragraph.trim())
        .filter(Boolean),
);

/**
 * The founder's note, split on blank lines so it reads as paragraphs. Kept
 * separate from the bio: the bio describes his career, this is why the company
 * exists at all.
 */
const messageParagraphs = computed(() =>
    (props.profile.founder_message ?? '')
        .split(/\n\s*\n/)
        .map((paragraph) => paragraph.trim())
        .filter(Boolean),
);

const facts = computed(() =>
    [
        {
            icon: Briefcase,
            label: content.value.about.role_label,
            value: props.profile.headline,
        },
        {
            icon: MapPin,
            label: content.value.about.location_label,
            value: props.profile.location,
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
        },
    ].filter((fact) => Boolean(fact.value)),
);
</script>

<template>
    <section
        id="about"
        class="bg-noise relative scroll-mt-20 border-t border-border pt-20 sm:pt-24"
    >
        <div class="corner-dot corner-dot-tl" aria-hidden="true" />
        <div class="corner-dot corner-dot-tr" aria-hidden="true" />

        <div class="container-laravel section-dashed-xl">
            <SectionHeading
                index="01"
                :eyebrow="content.sections.about.eyebrow"
                :title="content.sections.about.title"
                :highlight="content.sections.about.highlight"
                :description="content.sections.about.description"
            />

            <!-- Founder's note — the reason the company exists -->
            <motion.blockquote
                v-if="messageParagraphs.length"
                :variants="stagger(0.1)"
                initial="hidden"
                while-in-view="visible"
                :in-view-options="inViewOnce"
                class="relative mb-14 overflow-hidden rounded-lg border border-brand/20 bg-brand/[0.03] p-6 sm:p-8"
            >
                <div
                    class="pointer-events-none absolute -top-10 -right-6 text-brand/10"
                    aria-hidden="true"
                >
                    <Quote class="size-32" />
                </div>

                <motion.p
                    :variants="fadeUp"
                    class="relative mb-4 inline-flex items-center gap-2 font-mono text-xs text-brand uppercase"
                >
                    <span class="size-1.5 animate-pulse rounded-full bg-brand" />
                    {{ content.sections.about.message_label }}
                </motion.p>

                <div class="relative flex flex-col gap-4">
                    <motion.p
                        v-for="(paragraph, index) in messageParagraphs"
                        :key="index"
                        :variants="fadeUp"
                        class="text-lg leading-relaxed text-pretty text-foreground/80"
                    >
                        {{ paragraph }}
                    </motion.p>
                </div>

                <motion.footer
                    :variants="fadeUp"
                    class="relative mt-6 flex items-center gap-3 border-t border-brand/15 pt-4"
                >
                    <span
                        class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand/10 font-mono text-xs font-bold text-brand"
                    >
                        {{ getInitials(props.profile.name) }}
                    </span>
                    <span class="flex flex-col">
                        <span class="text-sm font-semibold">{{
                            profile.name
                        }}</span>
                        <span
                            class="text-xs text-muted-foreground"
                        >
                            {{ profile.headline ?? 'Founder' }}
                        </span>
                    </span>
                </motion.footer>
            </motion.blockquote>

            <div
                class="grid items-start gap-12 lg:grid-cols-[1.05fr_0.95fr] lg:gap-16"
            >
                <!-- Bio -->
                <motion.div
                    :variants="stagger(0.12)"
                    initial="hidden"
                    while-in-view="visible"
                    :in-view-options="inViewOnce"
                    class="flex flex-col gap-6"
                >
                    <motion.p
                        v-for="(paragraph, index) in paragraphs"
                        :key="index"
                        :variants="fadeUp"
                        class="text-lg leading-relaxed text-pretty text-foreground/75"
                    >
                        {{ paragraph }}
                    </motion.p>

                    <motion.div
                        :variants="fadeUp"
                        class="flex flex-wrap gap-2 pt-2"
                    >
                        <span
                            v-for="role in profile.roles"
                            :key="role"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-border px-3 py-1 font-mono text-xs text-muted-foreground"
                        >
                            #{{ role }}
                        </span>
                    </motion.div>
                </motion.div>

                <!-- Specs card — dark like Laravel testimonials -->
                <motion.div
                    :variants="stagger(0.1)"
                    initial="hidden"
                    while-in-view="visible"
                    :in-view-options="inViewOnce"
                >
                    <div
                        class="overflow-hidden rounded-lg border border-border"
                    >
                        <div
                            class="flex items-center justify-between border-b border-border bg-muted px-5 py-3"
                        >
                            <span class="text-xs font-semibold text-foreground"
                                >specifications.ts</span
                            >
                            <span
                                class="font-mono text-xs text-muted-foreground uppercase"
                            >
                                §2.1
                            </span>
                        </div>

                        <dl class="divide-y divide-border">
                            <motion.div
                                v-for="(fact, i) in facts"
                                :key="fact.label"
                                :variants="fadeUp"
                                :transition="{ delay: i * 0.05 }"
                                class="flex items-center gap-4 px-5 py-3.5"
                            >
                                <span
                                    class="grid size-8 shrink-0 place-items-center rounded-lg bg-muted text-muted-foreground"
                                >
                                    <component :is="fact.icon" class="size-4" />
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span
                                        class="block text-[10px] font-medium tracking-[0.12em] text-muted-foreground uppercase"
                                        >{{ fact.label }}</span
                                    >
                                    <span
                                        class="block truncate text-sm font-medium text-foreground"
                                    >
                                        <a
                                            v-if="fact.href"
                                            :href="fact.href"
                                            class="hover:text-brand"
                                            >{{ fact.value }}</a
                                        >
                                        <template v-else>{{
                                            fact.value
                                        }}</template>
                                    </span>
                                </span>
                            </motion.div>

                            <motion.div
                                :variants="fadeUp"
                                class="flex items-center gap-4 px-5 py-3.5"
                            >
                                <span
                                    class="grid size-8 shrink-0 place-items-center rounded-lg bg-muted text-muted-foreground"
                                >
                                    <span class="text-xs">◉</span>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span
                                        class="block text-[10px] font-medium tracking-[0.12em] text-muted-foreground uppercase"
                                        >availability</span
                                    >
                                    <span
                                        class="flex items-center gap-2 text-sm font-medium text-foreground"
                                    >
                                        <span
                                            class="size-1.5 rounded-full"
                                            :class="
                                                profile.available_for_work
                                                    ? 'bg-emerald-500 shadow-[0_0_6px_2px_rgba(16,185,129,0.4)]'
                                                    : 'bg-muted-foreground'
                                            "
                                        />
                                        {{
                                            profile.available_for_work
                                                ? content.about.available_open
                                                : content.about.available_closed
                                        }}
                                    </span>
                                </span>
                            </motion.div>
                        </dl>
                    </div>
                </motion.div>
            </div>
        </div>
    </section>
</template>
