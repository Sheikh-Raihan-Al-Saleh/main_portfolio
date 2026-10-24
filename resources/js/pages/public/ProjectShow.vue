<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProjectDetail from '@/components/site/ProjectDetail.vue';
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

    return new Date(value).toLocaleDateString('en', {
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

    <!-- Personal work belongs to the founder's site, so it links back there. -->
    <ProjectDetail
        :project="project"
        :related="related"
        :paragraphs="paragraphs"
        :period="period"
        :back-href="
            project.context === 'personal' ? '/founder#projects' : '/projects'
        "
        :back-label="
            project.context === 'personal'
                ? 'Back to portfolio'
                : 'All projects'
        "
    />
</template>
