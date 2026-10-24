<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Cloud, Code2, Database, Layers, Monitor, Wrench } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import Reveal from '@/components/site/Reveal.vue';
import SectionHeader from '@/components/site/SectionHeader.vue';
import { contentConfig } from '@/lib/content';
import { EASE_OUT } from '@/lib/motion';
import type { Profile, SkillGroup } from '@/types';

/**
 * Skills, one card per group, each skill with a proficiency bar that fills
 * the first time the card scrolls into view.
 */
type Props = {
    skillGroups: SkillGroup[];
};

defineProps<Props>();

const page = usePage<{ profile: Profile | null }>();
const copy = computed(() => contentConfig(page.props.profile).sections.skills);

const icons: Record<string, typeof Code2> = {
    language: Code2,
    framework: Layers,
    frontend: Monitor,
    database: Database,
    devops: Cloud,
    tool: Wrench,
};

function iconFor(key: string) {
    const lower = key.toLowerCase();
    const match = Object.keys(icons).find((name) => lower.includes(name));

    return match ? icons[match] : Code2;
}

function bar(level: number, index: number) {
    return {
        hidden: { scaleX: 0 },
        visible: {
            scaleX: Math.max(0, Math.min(100, level)) / 100,
            transition: {
                duration: 1.1,
                ease: EASE_OUT,
                delay: 0.15 + index * 0.06,
            },
        },
    };
}
</script>

<template>
    <section id="skills" class="site-section scroll-mt-24">
        <div class="site-container">
            <SectionHeader
                :eyebrow="copy.eyebrow"
                :title="copy.title"
                :highlight="copy.highlight"
                :description="copy.description"
            />

            <div class="mt-14 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                <Reveal
                    v-for="(group, gi) in skillGroups"
                    :key="group.key"
                    :delay="(gi % 3) * 0.08"
                >
                    <article
                        class="ui-card ui-card-hover flex h-full flex-col gap-5 p-7"
                    >
                        <header class="flex items-center gap-3">
                            <span class="ui-icon-tile"
                                ><component
                                    :is="iconFor(group.key)"
                                    class="size-5"
                            /></span>
                            <div>
                                <h3 class="ui-h3">{{ group.label }}</h3>
                                <p class="text-xs text-muted-foreground">
                                    {{ group.skills.length }} skills
                                </p>
                            </div>
                        </header>
                        <motion.ul
                            initial="hidden"
                            while-in-view="visible"
                            :in-view-options="{ once: true }"
                            class="flex flex-col gap-4"
                        >
                            <li
                                v-for="(skill, si) in group.skills"
                                :key="skill.id"
                            >
                                <div
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span class="font-medium">{{
                                        skill.name
                                    }}</span>
                                    <span
                                        class="text-xs text-muted-foreground tabular-nums"
                                        >{{ skill.proficiency }}%</span
                                    >
                                </div>
                                <div
                                    class="mt-2 h-1.5 overflow-hidden rounded-full bg-muted"
                                    role="progressbar"
                                    :aria-label="skill.name"
                                    :aria-valuenow="skill.proficiency"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                >
                                    <motion.div
                                        :variants="bar(skill.proficiency, si)"
                                        class="h-full origin-left rounded-full bg-gradient-to-r from-[var(--grad-from)] to-[var(--grad-to)]"
                                    />
                                </div>
                            </li>
                        </motion.ul>
                    </article>
                </Reveal>
            </div>
        </div>
    </section>
</template>
