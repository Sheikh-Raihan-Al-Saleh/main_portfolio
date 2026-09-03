<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Cloud, Database, Globe, Server } from '@lucide/vue';
import { AnimatePresence, motion } from 'motion-v';
import { computed, nextTick, ref } from 'vue';
import SectionHeading from '@/components/portfolio/SectionHeading.vue';
import { contentConfig } from '@/lib/content';
import { fadeUp, inViewOnce, stagger } from '@/lib/motion';
import type { Profile, Skill, SkillGroup } from '@/types';

type Props = {
    skillGroups: SkillGroup[];
};

const props = defineProps<Props>();

const page = usePage<{ profile: Profile | null }>();
const content = computed(() => contentConfig(page.props.profile));

const categories = computed(() => props.skillGroups);

const activeIndex = ref(0);
const activeKey = ref(categories.value[0]?.key ?? '');
const prevIndex = ref(activeIndex.value);

const activeGroup = computed<SkillGroup | undefined>(
    () => categories.value[activeIndex.value],
);

const direction = computed(() =>
    activeIndex.value > prevIndex.value ? 1 : -1,
);

function selectTab(index: number) {
    if (index === activeIndex.value) {
        return;
    }

    prevIndex.value = activeIndex.value;
    activeIndex.value = index;
    activeKey.value = categories.value[index]?.key ?? '';
}

const categoryIcons: Record<string, typeof Server> = {
    backend: Server,
    frontend: Globe,
    devops: Cloud,
    database: Database,
    language: Server,
    framework: Globe,
    tool: Cloud,
    'devops & cloud': Cloud,
    'databases & storage': Database,
};

function getIcon(key: string) {
    return categoryIcons[key.toLowerCase()] ?? Server;
}

function padTo(value: string, length: number) {
    return value.length >= length
        ? value
        : value + ' '.repeat(length - value.length);
}

function formatSkill(skill: Skill, width: number) {
    return {
        name: padTo(skill.name, width),
        level: skill.proficiency,
    };
}

const activeLines = computed(() => {
    const group = activeGroup.value;

    if (!group) {
        return [];
    }

    const width = Math.max(...group.skills.map((s) => s.name.length), 6);

    return group.skills.map((skill) => formatSkill(skill, width));
});

const activeMaxLevel = computed(() =>
    Math.max(
        20,
        ...(activeGroup.value?.skills.map((s) => s.proficiency) ?? [0]),
    ),
);

const tablistRef = ref<HTMLElement | null>(null);

function onTabKeydown(event: KeyboardEvent, index: number) {
    const keys = ['ArrowDown', 'ArrowRight', 'ArrowUp', 'ArrowLeft'];

    if (!keys.includes(event.key)) {
        return;
    }

    event.preventDefault();
    const dir =
        event.key === 'ArrowDown' || event.key === 'ArrowRight' ? 1 : -1;
    const next =
        (index + dir + categories.value.length) % categories.value.length;

    selectTab(next);

    nextTick(() => {
        const el = tablistRef.value?.querySelectorAll('[role="tab"]')[next];

        (el as HTMLElement | undefined)?.focus();
    });
}
</script>

<template>
    <section
        id="skills"
        class="bg-noise relative scroll-mt-20 border-t border-border pt-20 sm:pt-24"
    >
        <div class="corner-dot corner-dot-tl" aria-hidden="true" />
        <div class="corner-dot corner-dot-tr" aria-hidden="true" />

        <div class="container-laravel section-dashed-xl">
            <SectionHeading
                index="02"
                :eyebrow="content.sections.skills.eyebrow"
                :title="content.sections.skills.title"
                :highlight="content.sections.skills.highlight"
                :description="content.sections.skills.description"
            />

            <motion.div
                :variants="stagger(0.08)"
                initial="hidden"
                while-in-view="visible"
                :in-view-options="inViewOnce"
            >
                <div class="grid gap-6 lg:grid-cols-12">
                    <!-- Tab list -->
                    <motion.div :variants="fadeUp" class="lg:col-span-4">
                        <div
                            ref="tablistRef"
                            role="tablist"
                            aria-label="Skill categories"
                            aria-orientation="vertical"
                            class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-1 lg:gap-1.5"
                        >
                            <button
                                v-for="(group, index) in categories"
                                :key="group.key"
                                role="tab"
                                :id="`skill-tab-${group.key}`"
                                :aria-controls="`skill-panel-${group.key}`"
                                :aria-selected="index === activeIndex"
                                :tabindex="index === activeIndex ? 0 : -1"
                                @click="selectTab(index)"
                                @keydown="onTabKeydown($event, index)"
                                class="group flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left transition-all duration-200"
                                :class="
                                    index === activeIndex
                                        ? 'border-brand bg-brand/5 text-foreground'
                                        : 'border-border bg-card text-muted-foreground hover:border-border hover:bg-muted/30'
                                "
                            >
                                <span
                                    class="grid size-8 shrink-0 place-items-center rounded-lg"
                                    :class="
                                        index === activeIndex
                                            ? 'bg-foreground text-background'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                >
                                    <component
                                        :is="getIcon(group.key)"
                                        class="size-4"
                                    />
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span
                                        class="block truncate text-sm font-semibold"
                                    >
                                        {{ group.label }}
                                    </span>
                                    <span
                                        class="block text-[10px] tracking-[0.12em] text-muted-foreground uppercase"
                                    >
                                        {{ group.skills.length }} tools
                                    </span>
                                </span>
                                <span
                                    v-if="index === activeIndex"
                                    aria-hidden="true"
                                    class="size-1.5 rounded-full bg-brand"
                                />
                            </button>
                        </div>
                    </motion.div>

                    <!-- Code editor panel -->
                    <motion.div :variants="fadeUp" class="lg:col-span-8">
                        <div class="code-window h-full">
                            <div class="code-window-header">
                                <span class="code-window-dot bg-red-500" />
                                <span class="code-window-dot bg-yellow-500" />
                                <span class="code-window-dot bg-green-500" />
                                <span
                                    class="ml-2 text-xs text-muted-foreground"
                                >
                                    <span class="text-brand">$</span>
                                    cat tools/{{
                                        activeGroup?.key ?? 'tools'
                                    }}.conf
                                </span>
                                <span
                                    class="ml-auto font-mono text-xs text-muted-foreground uppercase"
                                >
                                    {{ activeGroup?.skills.length ?? 0 }}
                                    entries
                                </span>
                            </div>

                            <div class="p-5 sm:p-6">
                                <AnimatePresence mode="wait" :initial="false">
                                    <motion.div
                                        v-if="activeGroup"
                                        :key="activeKey"
                                        :id="`skill-panel-${activeGroup.key}`"
                                        role="tabpanel"
                                        :aria-labelledby="`skill-tab-${activeGroup.key}`"
                                        :initial="{
                                            opacity: 0,
                                            x: direction * 24,
                                            filter: 'blur(6px)',
                                        }"
                                        :animate="{
                                            opacity: 1,
                                            x: 0,
                                            filter: 'blur(0px)',
                                        }"
                                        :exit="{
                                            opacity: 0,
                                            x: direction * -24,
                                            filter: 'blur(6px)',
                                        }"
                                        :transition="{
                                            duration: 0.3,
                                            ease: [0.16, 1, 0.3, 1],
                                        }"
                                    >
                                        <div
                                            class="space-y-1.5 font-mono text-[13px] leading-relaxed sm:text-sm"
                                        >
                                            <p>
                                                <span class="text-syn-keyword"
                                                    >export const</span
                                                >
                                                <span class="text-syn-variable">
                                                    {{ activeGroup.key }}</span
                                                >
                                                <span class="text-syn-operator">
                                                    = [</span
                                                >
                                            </p>
                                            <p
                                                v-for="line in activeLines"
                                                :key="line.name.trim()"
                                                class="flex items-center gap-3 pl-5"
                                            >
                                                <span>
                                                    <span
                                                        class="text-syn-operator"
                                                        >{ name:</span
                                                    >
                                                    <span
                                                        class="text-syn-string"
                                                    >
                                                        "{{ line.name }}"</span
                                                    >
                                                    <span
                                                        class="text-syn-operator"
                                                    >
                                                        }</span
                                                    >
                                                    <span
                                                        class="text-syn-comment"
                                                        >,</span
                                                    >
                                                </span>
                                                <span
                                                    class="hidden w-24 shrink-0 items-center gap-1.5 sm:flex"
                                                >
                                                    <span
                                                        class="relative h-1 flex-1 overflow-hidden rounded-full bg-muted"
                                                    >
                                                        <motion.span
                                                            class="absolute inset-y-0 left-0 rounded-full bg-brand"
                                                            :initial="{
                                                                width: '0%',
                                                            }"
                                                            :animate="{
                                                                width: `${
                                                                    (line.level /
                                                                        activeMaxLevel) *
                                                                    100
                                                                }%`,
                                                            }"
                                                            :transition="{
                                                                duration: 0.8,
                                                                delay: 0.15,
                                                                ease: [
                                                                    0.16, 1,
                                                                    0.3, 1,
                                                                ],
                                                            }"
                                                        />
                                                    </span>
                                                    <span
                                                        class="text-xs text-syn-number tabular-nums"
                                                    >
                                                        {{ line.level }}%
                                                    </span>
                                                </span>
                                            </p>
                                            <p>
                                                <span class="text-syn-operator"
                                                    >];</span
                                                >
                                            </p>
                                        </div>
                                    </motion.div>
                                </AnimatePresence>
                            </div>
                        </div>
                    </motion.div>
                </div>
            </motion.div>
        </div>
    </section>
</template>
