<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
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
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
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
import type { Company, ContactMessage, Profile, Project } from '@/types';

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
    };
    recentMessages: ContactMessage[];
    recentProjects: Pick<
        Project,
        'id' | 'title' | 'slug' | 'is_published' | 'is_featured' | 'updated_at'
    >[];
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
        label: 'Skills',
        value: props.stats.skills,
        suffix: '',
        caption: 'listed',
        icon: Sparkles,
        href: skills.index(),
        color: 'from-violet-500 to-purple-500',
        bg: 'bg-violet-500/10',
        text: 'text-violet-600 dark:text-violet-400',
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
];

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
            :description="`Managing the portfolio for ${profile.name}.`"
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

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <Button as-child size="sm">
                <Link :href="projects.create()">
                    <Plus class="size-4" />
                    New project
                </Link>
            </Button>
            <Button as-child variant="outline" size="sm">
                <Link :href="clients.index()">
                    <Users class="size-4" />
                    Manage clients
                </Link>
            </Button>
            <Button as-child variant="outline" size="sm">
                <Link :href="messages.index()">
                    <MessageSquare class="size-4" />
                    Inbox
                    <span
                        v-if="stats.unreadMessages"
                        class="rounded-full bg-blue-500 px-1.5 py-px text-[11px] font-semibold text-white"
                    >
                        {{ stats.unreadMessages }}
                    </span>
                </Link>
            </Button>
        </div>

        <motion.div
            :variants="stagger(0.08)"
            initial="hidden"
            animate="visible"
            class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
        >
            <motion.div
                v-for="card in cards"
                :key="card.label"
                :variants="fadeUp"
            >
                <Link
                    :href="card.href"
                    class="group relative flex flex-col gap-3 overflow-hidden rounded-xl border border-border bg-card p-5 transition-all duration-300 hover:border-transparent hover:shadow-lg hover:shadow-black/5 dark:hover:shadow-black/20"
                >
                    <!-- Gradient accent bar -->
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

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <motion.section
                :variants="fadeUp"
                initial="hidden"
                animate="visible"
                class="rounded-xl border border-border bg-card"
            >
                <header
                    class="flex items-center justify-between border-b px-5 py-4"
                >
                    <h2 class="flex items-center gap-2 font-semibold">
                        <span class="rounded-lg bg-blue-500/10 p-1.5">
                            <MessageSquare
                                class="size-4 text-blue-600 dark:text-blue-400"
                            />
                        </span>
                        Recent messages
                        <span
                            v-if="stats.unreadMessages"
                            class="rounded-full bg-blue-500 px-2 py-0.5 text-xs font-medium text-white"
                        >
                            {{ stats.unreadMessages }} new
                        </span>
                    </h2>
                    <Button as-child variant="ghost" size="sm">
                        <Link :href="messages.index()">View all</Link>
                    </Button>
                </header>

                <ul v-if="recentMessages.length" class="divide-y divide-border">
                    <li v-for="message in recentMessages" :key="message.id">
                        <Link
                            :href="messages.show(message.id)"
                            class="flex items-center gap-3 px-5 py-3 transition-colors hover:bg-accent/50"
                        >
                            <span
                                :class="[
                                    'size-2 shrink-0 rounded-full',
                                    message.read_at
                                        ? 'bg-muted-foreground/30'
                                        : 'bg-blue-500',
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
                    class="px-5 py-8 text-center text-sm text-muted-foreground"
                >
                    No messages yet.
                </p>
            </motion.section>

            <motion.section
                :variants="fadeUp"
                initial="hidden"
                animate="visible"
                class="rounded-xl border border-border bg-card"
            >
                <header
                    class="flex items-center justify-between border-b px-5 py-4"
                >
                    <h2 class="flex items-center gap-2 font-semibold">
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
                            class="flex items-center gap-3 px-5 py-3 transition-colors hover:bg-accent/50"
                        >
                            <span
                                class="min-w-0 flex-1 truncate text-sm font-medium"
                            >
                                {{ project.title }}
                            </span>
                            <span
                                v-if="!project.is_published"
                                class="shrink-0 rounded-full bg-amber-500/10 px-2 py-0.5 text-xs font-medium text-amber-600 dark:text-amber-400"
                            >
                                Draft
                            </span>
                            <span
                                class="shrink-0 text-xs text-muted-foreground"
                            >
                                {{ formatDate(project.updated_at) }}
                            </span>
                        </Link>
                    </li>
                </ul>
                <p
                    v-else
                    class="px-5 py-8 text-center text-sm text-muted-foreground"
                >
                    No projects yet.
                </p>
            </motion.section>
        </div>
    </div>
</template>
