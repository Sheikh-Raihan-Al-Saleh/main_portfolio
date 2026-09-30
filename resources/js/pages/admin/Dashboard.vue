<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowUpRight,
    Briefcase,
    ExternalLink,
    FolderKanban,
    GraduationCap,
    Inbox,
    MessageSquare,
    Plus,
    Sparkles,
    TrendingUp,
    Users,
} from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import CompletenessRing from '@/components/admin/CompletenessRing.vue';
import DonutChart from '@/components/admin/DonutChart.vue';
import LineChart from '@/components/admin/LineChart.vue';
import AnimatedCounter from '@/components/portfolio/AnimatedCounter.vue';
import { Button } from '@/components/ui/button';
import { fadeUp, stagger } from '@/lib/motion';
import { dashboard } from '@/routes/admin';
import clients from '@/routes/admin/clients';
import educations from '@/routes/admin/educations';
import experiences from '@/routes/admin/experiences';
import messages from '@/routes/admin/messages';
import projects from '@/routes/admin/projects';
import skills from '@/routes/admin/skills';
import { edit as studioSectionsEdit } from '@/routes/admin/studio-sections';
import type {
    Company,
    CompletenessScore,
    ContactMessage,
    DashboardActivity,
    MessageSource,
    Profile,
    Project,
} from '@/types';

type Props = {
    stats: {
        projects: number;
        publishedProjects: number;
        clients: number;
        skills: number;
        experiences: number;
        educations: number;
        messages: number;
        unreadMessages: number;
        homeBlocks: number;
    };
    activity: DashboardActivity;
    completeness: {
        studio: CompletenessScore;
        founder: CompletenessScore;
    };
    recentMessages: ContactMessage[];
    recentProjects: Pick<
        Project,
        'id' | 'title' | 'slug' | 'is_published' | 'is_featured' | 'updated_at'
    >[];
    messageSources: MessageSource[];
    profile: Profile;
    company: Company;
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

const cards = [
    {
        label: 'Projects',
        value: props.stats.publishedProjects,
        suffix: `/${props.stats.projects}`,
        caption: 'published',
        icon: FolderKanban,
        href: projects.index(),
        color: 'from-blue-500 to-cyan-500',
        bg: 'bg-blue-500/10',
        text: 'text-blue-600 dark:text-blue-400',
    },
    {
        label: 'Clients',
        value: props.stats.clients,
        suffix: '',
        caption: 'logos',
        icon: Users,
        href: clients.index(),
        color: 'from-sky-500 to-indigo-500',
        bg: 'bg-sky-500/10',
        text: 'text-sky-600 dark:text-sky-400',
    },
    {
        label: 'Messages',
        value: props.stats.unreadMessages,
        suffix: `/${props.stats.messages}`,
        caption: 'unread',
        icon: Inbox,
        href: messages.index(),
        color: 'from-rose-500 to-pink-500',
        bg: 'bg-rose-500/10',
        text: 'text-rose-600 dark:text-rose-400',
    },
    {
        label: 'Home blocks',
        value: props.stats.homeBlocks,
        suffix: '',
        caption: 'builder blocks',
        icon: Sparkles,
        href: studioSectionsEdit(),
        color: 'from-violet-500 to-purple-500',
        bg: 'bg-violet-500/10',
        text: 'text-violet-600 dark:text-violet-400',
    },
    {
        label: 'Skills',
        value: props.stats.skills,
        suffix: '',
        caption: 'listed',
        icon: Sparkles,
        href: skills.index(),
        color: 'from-fuchsia-500 to-pink-500',
        bg: 'bg-fuchsia-500/10',
        text: 'text-fuchsia-600 dark:text-fuchsia-400',
    },
    {
        label: 'Experience',
        value: props.stats.experiences,
        suffix: '',
        caption: 'roles',
        icon: Briefcase,
        href: experiences.index(),
        color: 'from-amber-500 to-orange-500',
        bg: 'bg-amber-500/10',
        text: 'text-amber-600 dark:text-amber-400',
    },
    {
        label: 'Education',
        value: props.stats.educations,
        suffix: '',
        caption: 'entries',
        icon: GraduationCap,
        href: educations.index(),
        color: 'from-emerald-500 to-teal-500',
        bg: 'bg-emerald-500/10',
        text: 'text-emerald-600 dark:text-emerald-400',
    },
];

/** Total inbound interest over the window, for the trend headline. */
const messagesTotal = computed(() =>
    props.activity.messages.reduce((sum, value) => sum + value, 0),
);

const hasActivity = computed(() =>
    props.activity.messages.some((value) => value > 0) ||
    props.activity.projects.some((value) => value > 0),
);

const donutColors = ['#ef4444', '#f97316', '#8b5cf6', '#06b6d4', '#22c55e'];

const donutSlices = computed(() =>
    props.messageSources.map((source, index) => ({
        label: source.source,
        value: source.count,
        color: donutColors[index % donutColors.length],
    })),
);

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString(undefined, {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Dashboard" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="Dashboard"
            :description="`Analytics and content health for ${company.name} and ${profile.name}.`"
        >
            <template #actions>
                <Button as-child variant="outline" size="sm">
                    <a href="/" target="_blank" rel="noopener">
                        <ExternalLink class="size-4" />
                        View site
                    </a>
                </Button>
            </template>
        </AdminPageHeader>

        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <Button as-child size="sm">
                    <Link :href="projects.create()">
                        <Plus class="size-4" />
                        New project
                    </Link>
                </Button>
                <Button as-child variant="outline" size="sm">
                    <Link :href="studioSectionsEdit()">
                        <Sparkles class="size-4" />
                        Home sections
                    </Link>
                </Button>
                <Button as-child variant="outline" size="sm">
                    <Link :href="messages.index()">
                        <MessageSquare class="size-4" />
                        Inbox
                        <span
                            v-if="stats.unreadMessages"
                            class="rounded-full bg-primary px-1.5 py-px text-[11px] font-semibold text-primary-foreground"
                        >
                            {{ stats.unreadMessages }}
                        </span>
                    </Link>
                </Button>
            </div>
        </div>

        <!-- ── Headline metrics ── -->
        <motion.div
            :variants="stagger(0.06)"
            initial="hidden"
            animate="visible"
            class="grid gap-4 pt-4 sm:grid-cols-2 lg:grid-cols-4"
        >
            <motion.div
                v-for="card in cards"
                :key="card.label"
                :variants="fadeUp"
            >
                <Link
                    :href="card.href"
                    class="group relative flex flex-col gap-3 overflow-hidden rounded-2xl border border-border bg-card p-6 transition-all duration-500 hover:translate-y-1 hover:border-transparent hover:shadow-lg hover:shadow-primary/20"
                >
                    <div
                        :class="[
                            'absolute inset-x-0 top-0 h-1 bg-gradient-to-r opacity-0 transition-opacity duration-300 group-hover:opacity-100',
                            card.color,
                        ]"
                    />

                    <div class="flex items-center justify-between">
                        <span
                            :class="[
                                'rounded-lg p-2 transition-colors duration-300',
                                card.bg,
                            ]"
                        >
                            <component
                                :is="card.icon"
                                :class="['size-5', card.text]"
                            />
                        </span>
                        <TrendingUp
                            class="size-4 text-muted-foreground/0 transition-all duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 group-hover:text-muted-foreground/60"
                        />
                    </div>
                    <div>
                        <p
                            class="text-3xl font-bold tracking-tight tabular-nums"
                        >
                            <AnimatedCounter
                                :value="card.value"
                                :suffix="card.suffix"
                            />
                        </p>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            {{ card.label }}
                            <span class="text-xs opacity-70">{{
                                card.caption
                            }}</span>
                        </p>
                    </div>
                </Link>
            </motion.div>
        </motion.div>

        <!-- ── Analytics row ── -->
        <div class="mt-8 grid gap-6 xl:grid-cols-3">
            <!-- 30-day trend -->
            <motion.section
                :variants="fadeUp"
                initial="hidden"
                animate="visible"
                class="rounded-2xl border border-border bg-card p-6 xl:col-span-2"
            >
                <header
                    class="mb-4 flex flex-wrap items-center justify-between gap-2 border-b pb-4"
                >
                    <div>
                        <h2 class="flex items-center gap-2 text-lg font-semibold">
                            <span class="rounded-lg bg-primary/10 p-1.5">
                                <TrendingUp class="size-4 text-primary" />
                            </span>
                            Last 30 days
                        </h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ messagesTotal }}
                            {{ messagesTotal === 1 ? 'enquiry' : 'enquiries' }}
                            received, {{ activity.projects.reduce((sum, value) => sum + value, 0) }}
                            content {{ activity.projects.reduce((sum, value) => sum + value, 0) === 1 ? 'update' : 'updates' }}.
                        </p>
                    </div>
                    <Button as-child variant="ghost" size="sm">
                        <Link :href="messages.index()">
                            View inbox
                            <ArrowUpRight class="size-4" />
                        </Link>
                    </Button>
                </header>

                <LineChart
                    v-if="hasActivity"
                    :labels="activity.labels"
                    :series="[
                        {
                            name: 'Enquiries',
                            values: activity.messages,
                            color: 'var(--color-brand, #ef4444)',
                        },
                        {
                            name: 'Content updates',
                            values: activity.projects,
                            color: '#8b5cf6',
                        },
                    ]"
                />
                <p
                    v-else
                    class="grid place-items-center rounded-xl border border-dashed border-border py-16 text-center text-sm text-muted-foreground"
                >
                    No activity in the last 30 days yet. Enquiries and content
                    updates will chart here as they happen.
                </p>
            </motion.section>

            <!-- Completeness -->
            <motion.section
                :variants="fadeUp"
                initial="hidden"
                animate="visible"
                class="rounded-2xl border border-border bg-card p-6"
            >
                <header class="mb-6 border-b pb-4">
                    <h2 class="text-lg font-semibold">Content health</h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        How complete each public profile is. Click a gap to fix
                        it.
                    </p>
                </header>

                <div class="grid grid-cols-2 gap-6">
                    <CompletenessRing
                        label="Studio"
                        :percent="completeness.studio.percent"
                        :missing="completeness.studio.missing"
                        href="/admin/company"
                    />
                    <CompletenessRing
                        label="Founder"
                        :percent="completeness.founder.percent"
                        :missing="completeness.founder.missing"
                        href="/admin/profile"
                    />
                </div>
            </motion.section>
        </div>

        <!-- ── Second analytics row ── -->
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <!-- Enquiry split -->
            <motion.section
                :variants="fadeUp"
                initial="hidden"
                animate="visible"
                class="rounded-2xl border border-border bg-card p-6"
            >
                <header class="mb-4 border-b pb-4">
                    <h2 class="flex items-center gap-2 text-lg font-semibold">
                        <span class="rounded-lg bg-rose-500/10 p-1.5">
                            <Inbox
                                class="size-4 text-rose-600 dark:text-rose-400"
                            />
                        </span>
                        Enquiries over time
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        How recent enquiries are split against older ones.
                    </p>
                </header>

                <DonutChart
                    v-if="donutSlices.length"
                    :slices="donutSlices"
                    center-label="enquiries"
                />
                <p
                    v-else
                    class="py-10 text-center text-sm text-muted-foreground"
                >
                    No enquiries yet.
                </p>
            </motion.section>

            <!-- Recent messages -->
            <motion.section
                :variants="fadeUp"
                initial="hidden"
                animate="visible"
                class="rounded-2xl border border-border bg-card p-6"
            >
                <header class="flex items-center justify-between border-b pb-4">
                    <h2 class="flex items-center gap-2 text-lg font-semibold">
                        <span class="rounded-lg bg-primary/10 p-1.5">
                            <MessageSquare class="size-4 text-primary" />
                        </span>
                        Recent messages
                        <span
                            v-if="stats.unreadMessages"
                            class="rounded-full bg-primary px-2 py-0.5 text-xs font-medium text-white"
                        >
                            {{ stats.unreadMessages }} new
                        </span>
                    </h2>
                    <Button as-child variant="ghost" size="sm">
                        <Link :href="messages.index()">View all</Link>
                    </Button>
                </header>

                <ul
                    v-if="recentMessages.length"
                    class="divide-y divide-border"
                >
                    <li
                        v-for="message in recentMessages"
                        :key="message.id"
                    >
                        <Link
                            :href="messages.show(message.id)"
                            class="flex items-center gap-3 rounded-lg px-2 py-3 transition-colors hover:bg-primary/20"
                        >
                            <span
                                :class="[
                                    'size-2 shrink-0 rounded-full',
                                    message.read_at
                                        ? 'bg-muted-foreground/30'
                                        : 'bg-primary',
                                ]"
                            />
                            <span class="min-w-0 flex-1">
                                <span
                                    class="block truncate text-sm font-medium"
                                >
                                    {{ message.subject || '(no subject)' }}
                                </span>
                                <span
                                    class="block truncate text-xs text-muted-foreground"
                                >
                                    {{ message.name }} · {{ message.email }}
                                </span>
                            </span>
                            <span
                                class="shrink-0 text-xs text-muted-foreground"
                            >
                                {{ formatDate(message.created_at) }}
                            </span>
                        </Link>
                    </li>
                </ul>
                <p
                    v-else
                    class="py-8 text-center text-sm text-muted-foreground"
                >
                    No messages yet.
                </p>
            </motion.section>
        </div>

        <!-- ── Recent projects ── -->
        <motion.section
            :variants="fadeUp"
            initial="hidden"
            animate="visible"
            class="mt-6 rounded-2xl border border-border bg-card p-6"
        >
            <header class="flex items-center justify-between border-b pb-4">
                <h2 class="flex items-center gap-2 text-lg font-semibold">
                    <span class="rounded-lg bg-violet-500/10 p-1.5">
                        <FolderKanban
                            class="size-4 text-violet-600 dark:text-violet-400"
                        />
                    </span>
                    Recent projects
                </h2>
                <Button as-child variant="ghost" size="sm">
                    <Link :href="projects.index()">View all</Link>
                </Button>
            </header>

            <ul v-if="recentProjects.length" class="divide-y divide-border">
                <li v-for="project in recentProjects" :key="project.id">
                    <Link
                        :href="projects.edit(project.slug)"
                        class="flex items-center gap-3 rounded-lg px-2 py-3 transition-colors hover:bg-primary/20"
                    >
                        <span class="min-w-0 flex-1 truncate text-sm font-medium">
                            {{ project.title }}
                        </span>
                        <span
                            v-if="project.is_featured"
                            class="shrink-0 rounded-full bg-violet-500/10 px-2 py-0.5 text-xs font-medium text-violet-600 dark:text-violet-400"
                        >
                            Featured
                        </span>
                        <span
                            v-if="!project.is_published"
                            class="shrink-0 rounded-full bg-amber-500/10 px-2 py-0.5 text-xs font-medium text-amber-600 dark:text-amber-400"
                        >
                            Draft
                        </span>
                        <span class="shrink-0 text-xs text-muted-foreground">
                            {{ formatDate(project.updated_at) }}
                        </span>
                    </Link>
                </li>
            </ul>
            <p
                v-else
                class="py-8 text-center text-sm text-muted-foreground"
            >
                No projects yet.
            </p>
        </motion.section>
    </div>
</template>
