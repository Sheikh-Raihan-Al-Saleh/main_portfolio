<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ExternalLink, Sparkles } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import MagneticButton from '@/components/motion/MagneticButton.vue';
import ProjectCard from '@/components/portfolio/ProjectCard.vue';
import SocialIcon from '@/components/portfolio/SocialIcon.vue';
import TechBadge from '@/components/portfolio/TechBadge.vue';
import { Button } from '@/components/ui/button';
import { fadeUp, inViewOnce, scaleIn, stagger } from '@/lib/motion';
import type { Profile, Project } from '@/types';

type Props = {
    profile: Profile;
    project: Project;
    related: Project[];
};

const props = defineProps<Props>();

/** Descriptions are plain text; blank lines separate paragraphs. */
const paragraphs = computed(() =>
    (props.project.description ?? '')
        .split(/\n\s*\n/)
        .map((paragraph) => paragraph.trim())
        .filter(Boolean),
);

function formatDate(value: string | null): string | null {
    if (!value) {
        return null;
    }

    return new Date(value).toLocaleDateString(undefined, {
        month: 'short',
        year: 'numeric',
    });
}

const period = computed(() => {
    const start = formatDate(props.project.started_at);
    const end = formatDate(props.project.completed_at);

    if (!start && !end) {
        return null;
    }

    return `${start ?? '—'} → ${end ?? 'Ongoing'}`;
});
</script>

<template>
    <Head :title="`${project.title} — ${profile.name}`">
        <meta
            v-if="project.summary"
            name="description"
            :content="project.summary"
        />
        <meta property="og:title" :content="project.title" />
        <meta
            v-if="project.summary"
            property="og:description"
            :content="project.summary"
        />
        <meta
            v-if="project.cover_image_url"
            property="og:image"
            :content="project.cover_image_url"
        />
    </Head>

    <article class="mx-auto max-w-4xl px-4 py-16 sm:px-6">
        <Button as-child variant="ghost" size="sm" class="mb-8 -ml-2">
            <Link href="/projects">
                <ArrowLeft class="size-4" />
                All projects
            </Link>
        </Button>

        <motion.header
            :variants="stagger(0.08)"
            initial="hidden"
            animate="visible"
            class="flex flex-col gap-5"
        >
            <motion.h1
                :variants="fadeUp"
                class="text-4xl font-bold tracking-tight text-balance sm:text-5xl"
            >
                {{ project.title }}
            </motion.h1>

            <motion.p
                v-if="project.summary"
                :variants="fadeUp"
                class="text-lg text-pretty text-muted-foreground"
            >
                {{ project.summary }}
            </motion.p>

            <motion.dl
                :variants="fadeUp"
                class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-muted-foreground"
            >
                <div v-if="project.role" class="flex gap-2">
                    <dt class="font-medium">Role:</dt>
                    <dd>{{ project.role }}</dd>
                </div>
                <div v-if="period" class="flex gap-2">
                    <dt class="font-medium">Timeline:</dt>
                    <dd>{{ period }}</dd>
                </div>
            </motion.dl>

            <motion.div
                v-if="project.tech_stack?.length"
                :variants="fadeUp"
                class="flex flex-wrap gap-1.5"
            >
                <TechBadge
                    v-for="tech in project.tech_stack"
                    :key="tech"
                    :label="tech"
                />
            </motion.div>

            <motion.div
                v-if="
                    project.landing_url || project.live_url || project.repo_url
                "
                :variants="fadeUp"
                class="mt-2 flex flex-wrap gap-3"
            >
                <!-- The case study is the richest destination, so it leads. -->
                <MagneticButton v-if="project.landing_url">
                    <Button as-child>
                        <Link :href="project.landing_url">
                            <Sparkles class="size-4" />
                            Read the case study
                        </Link>
                    </Button>
                </MagneticButton>

                <Button
                    v-if="project.live_url"
                    as-child
                    :variant="project.landing_url ? 'outline' : 'default'"
                >
                    <a
                        :href="project.live_url"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <ExternalLink class="size-4" />
                        Visit live site
                    </a>
                </Button>
                <Button v-if="project.repo_url" as-child variant="outline">
                    <a
                        :href="project.repo_url"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <SocialIcon name="github" class="size-4" />
                        View source
                    </a>
                </Button>
            </motion.div>
        </motion.header>

        <motion.figure
            v-if="project.cover_image_url"
            :variants="scaleIn"
            initial="hidden"
            animate="visible"
            class="my-12 overflow-hidden rounded-xl border border-border/70 bg-muted"
        >
            <img
                :src="project.cover_image_url"
                :alt="project.title"
                class="w-full object-cover"
            />
        </motion.figure>

        <motion.div
            v-if="paragraphs.length"
            :variants="stagger(0.06)"
            initial="hidden"
            while-in-view="visible"
            :in-view-options="inViewOnce"
            class="mt-12 max-w-2xl"
        >
            <motion.p
                v-for="(paragraph, index) in paragraphs"
                :key="index"
                :variants="fadeUp"
                class="mb-5 leading-relaxed text-muted-foreground last:mb-0"
            >
                {{ paragraph }}
            </motion.p>
        </motion.div>

        <motion.div
            v-if="project.gallery_urls.length"
            :variants="stagger(0.08)"
            initial="hidden"
            while-in-view="visible"
            :in-view-options="inViewOnce"
            class="mt-16 grid gap-6 sm:grid-cols-2"
        >
            <motion.img
                v-for="(image, index) in project.gallery_urls"
                :key="image"
                :variants="scaleIn"
                :src="image"
                :alt="`${project.title} screenshot ${index + 1}`"
                loading="lazy"
                class="w-full rounded-xl border border-border/70 bg-muted object-cover"
            />
        </motion.div>
    </article>

    <section
        v-if="related.length"
        class="mt-16 border-t border-border/60 py-16"
    >
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <h2 class="mb-8 text-xl font-semibold tracking-tight">
                More projects
            </h2>

            <motion.div
                :variants="stagger(0.08)"
                initial="hidden"
                while-in-view="visible"
                :in-view-options="inViewOnce"
                class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >
                <ProjectCard
                    v-for="item in related"
                    :key="item.id"
                    :project="item"
                />
            </motion.div>
        </div>
    </section>
</template>
