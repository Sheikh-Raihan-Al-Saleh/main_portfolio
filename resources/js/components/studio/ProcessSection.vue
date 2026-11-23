<script setup lang="ts">
import { Plus } from '@lucide/vue';
import { AnimatePresence, motion } from 'motion-v';
import { ref } from 'vue';
import SectionHeading from '@/components/site/SectionHeading.vue';
import { resolveIcon } from '@/lib/iconResolver';
import type { ProcessSectionProps } from '@/types';

/**
 * How the system is built: the engineering stages as an editorial
 * accordion. One stage is open at a time; the first starts open.
 */
defineProps<ProcessSectionProps>();

const open = ref(0);
</script>

<template>
    <section
        id="engineering"
        class="section scroll-mt-24 bg-ink text-paper"
        data-tone="dark"
    >
        <div class="container-site">
            <SectionHeading
                :eyebrow="content.eyebrow"
                :title="content.title"
                :highlight="content.highlight"
                :description="content.description"
                align="split"
                class="[&_.eyebrow]:text-paper/60 [&_.lead]:text-paper/70"
            />

            <ol class="mt-16 border-t border-paper/20">
                <li
                    v-for="(stage, stageIndex) in content.stages"
                    :key="stage.label"
                    class="border-b border-paper/20"
                >
                    <h3>
                        <button
                            type="button"
                            class="group flex w-full items-center gap-6 py-7 text-left sm:gap-10"
                            :aria-expanded="open === stageIndex"
                            :aria-controls="`stage-${stageIndex}`"
                            @click="
                                open = open === stageIndex ? -1 : stageIndex
                            "
                        >
                            <span class="index-number w-8 shrink-0">{{
                                String(stageIndex + 1).padStart(2, '0')
                            }}</span>
                            <span
                                class="display flex-1 text-4xl transition-colors group-hover:text-brand-text sm:text-6xl"
                            >
                                {{ stage.label }}
                            </span>
                            <span class="hidden text-paper/60 md:block">{{
                                stage.tagline
                            }}</span>
                            <Plus
                                class="size-6 shrink-0 transition-transform duration-500"
                                :class="
                                    open === stageIndex &&
                                    'rotate-45 text-brand-text'
                                "
                                aria-hidden="true"
                            />
                        </button>
                    </h3>

                    <AnimatePresence :initial="false">
                        <motion.div
                            v-if="open === stageIndex"
                            :id="`stage-${stageIndex}`"
                            class="overflow-hidden"
                            :initial="{ height: 0, opacity: 0 }"
                            :animate="{ height: 'auto', opacity: 1 }"
                            :exit="{ height: 0, opacity: 0 }"
                            :transition="{
                                duration: 0.5,
                                ease: [0.16, 1, 0.3, 1],
                            }"
                        >
                            <div
                                class="grid gap-8 pb-10 sm:pl-[4.5rem] lg:grid-cols-12"
                            >
                                <div
                                    class="flex items-start gap-5 lg:col-span-7"
                                >
                                    <component
                                        :is="resolveIcon(stage.icon)"
                                        class="mt-1 size-6 shrink-0 text-brand-text"
                                        aria-hidden="true"
                                    />
                                    <p
                                        class="text-lg leading-relaxed text-paper/80"
                                    >
                                        {{ stage.detail }}
                                    </p>
                                </div>
                                <ul
                                    class="flex flex-wrap content-start gap-2 lg:col-span-4 lg:col-start-9"
                                >
                                    <li
                                        v-for="tech in stage.tech"
                                        :key="tech"
                                        class="tag border-paper/25 bg-transparent text-paper/80"
                                    >
                                        {{ tech }}
                                    </li>
                                </ul>
                            </div>
                        </motion.div>
                    </AnimatePresence>
                </li>
            </ol>
        </div>
    </section>
</template>
