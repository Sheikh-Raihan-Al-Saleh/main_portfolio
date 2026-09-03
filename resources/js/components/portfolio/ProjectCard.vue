<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight, Eye } from '@lucide/vue';
import { motion } from 'motion-v';
import { ref } from 'vue';
import ProjectDetailModal from '@/components/portfolio/ProjectDetailModal.vue';
import { fadeUp } from '@/lib/motion';
import { cn } from '@/lib/utils';
import type { Project } from '@/types';

type Props = {
    project: Project;
    featured?: boolean;
};

const props = withDefaults(defineProps<Props>(), { featured: false });

const modalOpen = ref(false);
</script>

<template>
    <motion.article
        :variants="fadeUp"
        :class="
            cn(
                'group relative flex h-full flex-col',
                props.featured && 'sm:col-span-2',
            )
        "
    >
        <Link
            :href="`/projects/${project.slug}`"
            class="absolute inset-0 z-10 rounded-lg focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
            :aria-label="`View ${project.title}`"
        />

        <!-- Window chrome card -->
        <div class="window-chrome glow-card rounded-lg">
            <div class="title-bar">
                <div class="traffic-light">
                    <span class="bg-syn-variable/70" />
                    <span class="bg-syn-number/70" />
                    <span class="bg-syn-string/70" />
                </div>
                <span class="ml-2 truncate"
                    >{{
                        project.title.toLowerCase().replace(/\s+/g, '-')
                    }}.tsx</span
                >
                <span
                    v-if="project.is_featured"
                    class="ml-auto rounded bg-brand/20 px-1.5 py-0.5 text-[9px] font-semibold text-brand"
                >
                    FEATURED
                </span>
            </div>

            <!-- Image area -->
            <div
                :class="
                    cn(
                        'img-shine relative overflow-hidden bg-muted',
                        props.featured ? 'aspect-21/9' : 'aspect-4/3',
                    )
                "
            >
                <img
                    v-if="project.cover_image_url"
                    :src="project.cover_image_url"
                    :alt="project.title"
                    loading="lazy"
                    class="size-full object-cover transition-transform duration-700 ease-out group-hover:scale-110"
                />
                <div
                    v-else
                    class="bg-mesh grid size-full place-items-center text-muted-foreground/30"
                >
                    <span class="font-mono text-lg">// no preview</span>
                </div>

                <!-- Hover overlay -->
                <div
                    aria-hidden="true"
                    class="absolute inset-0 bg-gradient-to-t from-background/80 via-transparent to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                />

                <div
                    class="absolute right-3 bottom-3 z-10 flex translate-y-2 gap-2 opacity-0 transition-all duration-500 group-hover:translate-y-0 group-hover:opacity-100"
                >
                    <button
                        type="button"
                        class="flex size-9 items-center justify-center rounded border border-border/60 bg-background/80 text-foreground backdrop-blur-sm transition-colors hover:bg-background"
                        aria-label="Quick view"
                        @click.prevent="modalOpen = true"
                    >
                        <Eye class="size-3.5" />
                    </button>
                    <span
                        class="flex size-9 items-center justify-center rounded border border-border/60 bg-background/80 text-foreground backdrop-blur-sm transition-all duration-500 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                    >
                        <ArrowUpRight class="size-4" />
                    </span>
                </div>
            </div>
        </div>

        <!-- Card body -->
        <div class="mt-3 flex flex-1 flex-col gap-2 px-1">
            <h3
                :class="
                    cn(
                        'font-mono leading-snug font-semibold tracking-tight',
                        props.featured ? 'text-xl sm:text-2xl' : 'text-lg',
                    )
                "
            >
                {{ project.title }}
            </h3>

            <p
                v-if="project.summary"
                :class="
                    cn(
                        'text-sm leading-relaxed text-muted-foreground',
                        props.featured ? 'line-clamp-3' : 'line-clamp-2',
                    )
                "
            >
                {{ project.summary }}
            </p>

            <!-- Tech stack as code tags -->
            <div
                v-if="project.tech_stack?.length"
                class="flex flex-wrap gap-1.5"
            >
                <span
                    v-for="tech in project.tech_stack.slice(0, 4)"
                    :key="tech"
                    class="rounded border border-border/60 bg-muted/50 px-2 py-0.5 font-mono text-[10px] text-muted-foreground"
                >
                    {{ tech }}
                </span>
                <span
                    v-if="project.tech_stack.length > 4"
                    class="rounded border border-border/60 bg-muted/50 px-2 py-0.5 font-mono text-[10px] text-muted-foreground"
                >
                    +{{ project.tech_stack.length - 4 }}
                </span>
            </div>

            <div
                class="relative z-20 mt-auto flex w-fit items-center gap-4 pt-2 font-mono text-xs"
            >
                <button
                    type="button"
                    class="text-brand transition-colors hover:text-brand/80"
                    @click.prevent="modalOpen = true"
                >
                    &lt;QuickView /&gt;
                </button>

                <Link
                    v-if="project.landing_url"
                    :href="`/projects/${project.slug}`"
                    class="text-brand transition-colors hover:text-brand/80"
                    @click.stop
                >
                    &lt;CaseStudy /&gt;
                </Link>

                <a
                    v-if="project.repo_url"
                    :href="project.repo_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-muted-foreground transition-colors hover:text-foreground"
                    @click.stop
                >
                    src/
                </a>
            </div>
        </div>

        <ProjectDetailModal
            :project="project"
            :open="modalOpen"
            @update:open="modalOpen = $event"
        />
    </motion.article>
</template>
