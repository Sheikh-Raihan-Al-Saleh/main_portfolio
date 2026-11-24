<script setup lang="ts">
import { motion } from 'motion-v';
import Reveal from '@/components/Site/Reveal.vue';
import SectionHeading from '@/components/Site/SectionHeading.vue';
import { EASE_OUT, inViewOnce } from '@/composables/motion';
import type { SkillsSectionProps } from '@/types';

/**
 * The founder's toolkit, by category, with proficiency bars that fill as
 * they scroll into view.
 */
defineProps<SkillsSectionProps>();
</script>

<template>
    <section id="skills" class="section scroll-mt-24 bg-paper-deep">
        <div class="container-site">
            <SectionHeading
                :eyebrow="copy.eyebrow"
                :title="copy.title"
                :highlight="copy.highlight"
                :description="copy.description"
                align="split"
            />

            <div
                class="mt-16 grid gap-x-12 gap-y-14 md:grid-cols-2 lg:grid-cols-3"
            >
                <Reveal
                    v-for="(group, groupIndex) in groups"
                    :key="group.id"
                    :delay="(groupIndex % 3) * 0.08"
                >
                    <h3
                        class="flex items-baseline justify-between border-b border-ink pb-3"
                    >
                        <span class="display text-3xl">{{ group.name }}</span>
                        <span class="meta">{{ group.skills.length }}</span>
                    </h3>

                    <ul class="mt-5 grid gap-4">
                        <li v-for="skill in group.skills" :key="skill.id">
                            <div
                                class="flex items-baseline justify-between text-sm"
                            >
                                <span
                                    class="font-medium"
                                    :class="
                                        skill.is_featured && 'text-brand-text'
                                    "
                                    >{{ skill.name }}</span
                                >
                                <span class="meta"
                                    >{{ skill.proficiency }}%</span
                                >
                            </div>
                            <div class="mt-2 h-px w-full bg-line">
                                <motion.div
                                    class="h-[3px] -translate-y-px bg-ink"
                                    :initial="{ width: '0%' }"
                                    :while-in-view="{
                                        width: `${skill.proficiency}%`,
                                    }"
                                    :in-view-options="inViewOnce"
                                    :transition="{
                                        duration: 1.2,
                                        ease: EASE_OUT,
                                    }"
                                />
                            </div>
                        </li>
                    </ul>
                </Reveal>
            </div>
        </div>
    </section>
</template>
