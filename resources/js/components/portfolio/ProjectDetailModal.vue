<script setup lang="ts">
import { ArrowUpRight, Code, ExternalLink, X } from '@lucide/vue';
import { AnimatePresence, motion } from 'motion-v';
import { onMounted, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import type { Project } from '@/types';

type Props = {
    project: Project | null;
    open: boolean;
};

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

/*
 * The dialog is portaled to <body>. Render the portal only after hydration so
 * the server HTML and the first client render agree; otherwise every card's
 * closed modal leaves mismatched anchors that later break page unmounts.
 */
const mounted = ref(false);

onMounted(() => {
    mounted.value = true;
});

function close() {
    emit('update:open', false);
}

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') {
        close();
    }
}

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            document.addEventListener('keydown', onKeydown);
            document.body.style.overflow = 'hidden';
        } else {
            document.removeEventListener('keydown', onKeydown);
            document.body.style.overflow = '';
        }
    },
);
</script>

<template>
    <Teleport v-if="mounted" to="body">
        <AnimatePresence>
            <motion.div
                v-if="open && project"
                key="project-overlay"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 md:p-10"
                initial="hidden"
                animate="visible"
                exit="hidden"
            >
                <!-- Backdrop -->
                <motion.div
                    class="absolute inset-0 bg-black/60 backdrop-blur-sm"
                    :initial="{ opacity: 0 }"
                    :animate="{ opacity: 1 }"
                    :exit="{ opacity: 0 }"
                    :transition="{ duration: 0.25 }"
                    @click="close"
                />

                <!-- Dialog — 3D scale-up entrance -->
                <motion.div
                    class="relative z-10 flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-border bg-background shadow-2xl"
                    :initial="{ opacity: 0, scale: 0.92, rotateX: 6, y: 20 }"
                    :animate="{ opacity: 1, scale: 1, rotateX: 0, y: 0 }"
                    :exit="{ opacity: 0, scale: 0.95, rotateX: -4, y: 10 }"
                    :transition="{
                        duration: 0.4,
                        ease: [0.16, 1, 0.3, 1],
                    }"
                    role="dialog"
                    aria-modal="true"
                    :aria-label="project.title"
                >
                    <!-- Close button -->
                    <button
                        type="button"
                        class="absolute top-4 right-4 z-20 grid size-8 place-items-center rounded-lg border border-border bg-background/80 text-muted-foreground backdrop-blur-sm transition-colors hover:text-foreground"
                        aria-label="Close"
                        @click="close"
                    >
                        <X class="size-4" />
                    </button>

                    <!-- Scrollable content -->
                    <div class="overflow-y-auto">
                        <!-- Cover image -->
                        <div
                            v-if="project.cover_image_url"
                            class="relative aspect-[16/8] w-full overflow-hidden"
                        >
                            <img
                                :src="project.cover_image_url"
                                :alt="project.title"
                                class="h-full w-full object-cover"
                            />
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-background via-transparent to-transparent"
                                aria-hidden="true"
                            />
                        </div>

                        <div class="flex flex-col gap-6 p-6 sm:p-8">
                            <!-- Header -->
                            <div>
                                <div
                                    class="mb-3 flex flex-wrap items-center gap-2"
                                >
                                    <span
                                        v-if="project.is_featured"
                                        class="rounded-lg bg-brand/10 px-2 py-0.5 font-mono text-[10px] font-semibold text-brand"
                                    >
                                        ★ featured
                                    </span>
                                    <span
                                        class="font-mono text-xs text-muted-foreground uppercase"
                                    >
                                        {{
                                            project.landing_url
                                                ? 'live case study'
                                                : 'open source'
                                        }}
                                    </span>
                                    <span
                                        v-if="project.started_at"
                                        class="ml-auto text-[11px] text-muted-foreground tabular-nums"
                                    >
                                        {{
                                            new Date(
                                                project.started_at,
                                            ).toLocaleDateString(undefined, {
                                                year: 'numeric',
                                            })
                                        }}
                                    </span>
                                </div>
                                <h2
                                    class="font-display text-2xl font-bold tracking-tight sm:text-3xl"
                                >
                                    {{ project.title }}
                                </h2>
                                <p
                                    v-if="project.summary"
                                    class="mt-3 max-w-2xl text-muted-foreground"
                                >
                                    {{ project.summary }}
                                </p>
                            </div>

                            <!-- Details grid -->
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div
                                    v-if="project.role"
                                    class="rounded-lg border border-border bg-muted/30 p-4"
                                >
                                    <span
                                        class="block text-[10px] font-medium tracking-[0.14em] text-muted-foreground uppercase"
                                    >
                                        Role
                                    </span>
                                    <span
                                        class="mt-1 block text-sm font-semibold"
                                    >
                                        {{ project.role }}
                                    </span>
                                </div>
                                <div
                                    v-if="project.started_at"
                                    class="rounded-lg border border-border bg-muted/30 p-4"
                                >
                                    <span
                                        class="block text-[10px] font-medium tracking-[0.14em] text-muted-foreground uppercase"
                                    >
                                        Timeline
                                    </span>
                                    <span
                                        class="mt-1 block text-sm font-semibold"
                                    >
                                        {{
                                            new Date(
                                                project.started_at,
                                            ).toLocaleDateString(undefined, {
                                                month: 'short',
                                                year: 'numeric',
                                            })
                                        }}
                                        <template v-if="project.completed_at">
                                            —
                                            {{
                                                new Date(
                                                    project.completed_at,
                                                ).toLocaleDateString(
                                                    undefined,
                                                    {
                                                        month: 'short',
                                                        year: 'numeric',
                                                    },
                                                )
                                            }}
                                        </template>
                                        <template v-else> — Present</template>
                                    </span>
                                </div>
                            </div>

                            <!-- Tech stack -->
                            <div v-if="project.tech_stack?.length">
                                <h4
                                    class="mb-3 text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase"
                                >
                                    Tech Stack
                                </h4>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="tech in project.tech_stack"
                                        :key="tech"
                                        class="rounded-lg border border-brand/20 bg-brand/5 px-3 py-1 font-mono text-xs font-medium text-brand"
                                    >
                                        {{ tech }}
                                    </span>
                                </div>
                            </div>

                            <!-- Description -->
                            <div v-if="project.description">
                                <h4
                                    class="mb-3 text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase"
                                >
                                    About this project
                                </h4>
                                <p
                                    class="text-sm leading-relaxed text-muted-foreground"
                                >
                                    {{ project.description }}
                                </p>
                            </div>

                            <!-- Gallery -->
                            <div v-if="project.gallery_urls?.length">
                                <h4
                                    class="mb-3 text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase"
                                >
                                    Gallery
                                </h4>
                                <div class="grid grid-cols-2 gap-3">
                                    <div
                                        v-for="(
                                            url, index
                                        ) in project.gallery_urls.slice(0, 4)"
                                        :key="index"
                                        class="overflow-hidden rounded-lg border border-border"
                                    >
                                        <img
                                            :src="url"
                                            :alt="`${project.title} screenshot ${index + 1}`"
                                            loading="lazy"
                                            class="w-full object-cover transition-transform duration-500 hover:scale-105"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div
                                class="flex flex-wrap gap-3 border-t border-border pt-5"
                            >
                                <Button
                                    v-if="project.landing_url"
                                    as-child
                                    size="sm"
                                    class="btn-laravel-primary rounded-lg px-6 text-xs font-semibold"
                                >
                                    <a
                                        :href="project.landing_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <ExternalLink class="size-3.5" />
                                        Live Demo
                                    </a>
                                </Button>

                                <Button
                                    v-if="project.repo_url"
                                    as-child
                                    variant="outline"
                                    size="sm"
                                    class="btn-laravel rounded-lg px-6 text-xs font-semibold"
                                >
                                    <a
                                        :href="project.repo_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <Code class="size-3.5" />
                                        Source Code
                                    </a>
                                </Button>

                                <Button
                                    v-if="
                                        project.live_url && !project.landing_url
                                    "
                                    as-child
                                    variant="outline"
                                    size="sm"
                                    class="btn-laravel rounded-lg px-6 text-xs font-semibold"
                                >
                                    <a
                                        :href="project.live_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <ArrowUpRight class="size-3.5" />
                                        Visit Site
                                    </a>
                                </Button>
                            </div>
                        </div>
                    </div>
                </motion.div>
            </motion.div>
        </AnimatePresence>
    </Teleport>
</template>
