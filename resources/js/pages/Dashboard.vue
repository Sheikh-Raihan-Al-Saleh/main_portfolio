<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowUpRight, Plus } from '@lucide/vue';
import { computed, ref } from 'vue';
import { formatDate } from '@/composables/text';
import { route } from '@/composables/useRoute';
import { useSite } from '@/composables/useSite';
import type { DashboardProps } from '@/types';

const props = defineProps<DashboardProps>();

const { studio } = useSite();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: route('adminDashboard') }],
    },
});

type LogLevel = 'OK' | 'INFO' | 'WARN';

const levelClass: Record<LogLevel, string> = {
    OK: 'text-primary',
    INFO: 'text-info',
    WARN: 'text-warn',
};

const heatClass = [
    'bg-muted',
    'bg-primary/25',
    'bg-primary/50',
    'bg-primary/75',
    'bg-primary',
];

const sum = (values: number[]): number =>
    values.reduce((total, value) => total + value, 0);

/** Points for an 84×28 trend line. */
function sparkline(values: number[]): string {
    const max = Math.max(...values);
    const min = Math.min(...values);
    const span = max - min || 1;

    return values
        .map((value, index) => {
            const x = (index * 84) / Math.max(values.length - 1, 1);
            const y = 25 - ((value - min) / span) * 22;

            return `${x.toFixed(1)},${y.toFixed(1)}`;
        })
        .join(' ');
}

/** "14:02:11" for today, "Nov 21" for older entries. */
function logTime(date: string): string {
    const value = new Date(date);

    if (value.toDateString() === new Date().toDateString()) {
        return value.toLocaleTimeString(undefined, { hour12: false });
    }

    return value.toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
    });
}

/* ── Headline numbers, written as constants ── */
const tiles = computed(() => [
    {
        key: 'publishedProjects',
        value: props.stats.publishedProjects,
        note: `of ${props.stats.projects} total`,
        href: route('projectIndex'),
        spark: sparkline(props.activity.projects),
        highlight: false,
    },
    {
        key: 'unreadMessages',
        value: props.stats.unreadMessages,
        note: `${props.stats.messages} received in total`,
        href: route('messageIndex'),
        spark: sparkline(props.activity.messages),
        highlight: props.stats.unreadMessages > 0,
    },
    {
        key: 'clients',
        value: props.stats.clients,
        note: 'logos on the studio site',
        href: route('clientIndex'),
        spark: null,
        highlight: false,
    },
    {
        key: 'skills',
        value: props.stats.skills,
        note: `${props.stats.experiences} roles · ${props.stats.educations} education`,
        href: route('skillIndex'),
        spark: null,
        highlight: false,
    },
]);

/* ── Enquiries chart (the service sends 30 days) ── */
const range = ref<7 | 30>(7);

const rangeTotal = computed(() =>
    sum(props.activity.messages.slice(-range.value)),
);

/** Change against the week before; only the 7-day view has one to compare. */
const weekDelta = computed<number | null>(() => {
    if (range.value !== 7) {
        return null;
    }

    const previous = sum(props.activity.messages.slice(-14, -7));

    return previous === 0
        ? null
        : Math.round(((rangeTotal.value - previous) / previous) * 100);
});

const chart = computed(() => {
    const values = props.activity.messages.slice(-range.value);
    const labels = props.activity.labels.slice(-range.value);
    const top = Math.max(2, Math.ceil(Math.max(...values) / 2) * 2);
    const every = range.value === 7 ? 1 : 5;

    return {
        top,
        bars: values.map((value, index) => ({
            value,
            label: index % every === 0 ? labels[index] : '',
            title: `${labels[index]}: ${value} ${value === 1 ? 'enquiry' : 'enquiries'}`,
            height: `${(value / top) * 100}%`,
            isLast: index === values.length - 1,
        })),
    };
});

/* ── Content heatmap: projects touched per day, Monday-first columns ── */
const heatmap = computed(() => {
    const values = props.activity.projects;
    const max = Math.max(...values, 1);
    const first = new Date();
    first.setDate(first.getDate() - (values.length - 1));
    const padding = (first.getDay() + 6) % 7;

    return [
        ...Array.from({ length: padding }, () => null),
        ...values.map((value, index) => ({
            level: value === 0 ? 0 : Math.ceil((value / max) * 4),
            title: `${props.activity.labels[index]}: ${value} ${value === 1 ? 'edit' : 'edits'}`,
        })),
    ];
});

const editsTotal = computed(() => sum(props.activity.projects));

const streak = computed(() => {
    let days = 0;

    for (let i = props.activity.projects.length - 1; i >= 0; i--) {
        if (props.activity.projects[i] === 0) {
            break;
        }

        days++;
    }

    return days;
});

/* ── Activity log built from the recent rows ── */
const log = computed(() =>
    [
        ...props.recentMessages.map((message) => ({
            key: `m${message.id}`,
            at: message.created_at,
            level: (message.read_at ? 'INFO' : 'OK') as LogLevel,
            event: 'message.received',
            detail: `#${message.id} from ${message.name}`,
            href: route('messageShow', message.id),
        })),
        ...props.recentProjects.map((project) => ({
            key: `p${project.id}`,
            at: project.updated_at,
            level: (project.is_published ? 'OK' : 'WARN') as LogLevel,
            event: project.is_published ? 'project.updated' : 'project.draft',
            detail: project.slug,
            href: route('projectEdit', project.slug),
        })),
    ]
        .sort((a, b) => b.at.localeCompare(a.at))
        .slice(0, 8),
);

/* ── Profile completeness as lint output ── */
const profiles = computed(() => [
    {
        file: 'studio.json',
        score: props.completeness.studio,
        href: route('studioEdit'),
    },
    {
        file: 'founder.json',
        score: props.completeness.founder,
        href: route('founderEdit'),
    },
]);

const warnings = computed(() =>
    profiles.value.reduce(
        (total, profile) => total + profile.score.missing.length,
        0,
    ),
);

const stackFile = computed(() => [
    ['projects', props.stats.projects],
    ['published', props.stats.publishedProjects],
    ['clients', props.stats.clients],
    ['skills', props.stats.skills],
    ['experiences', props.stats.experiences],
    ['educations', props.stats.educations],
    ['homeBlocks', props.stats.homeBlocks],
    ['messages', props.stats.messages],
]);
</script>

<template>
    <Head title="Dashboard" />

    <div class="space-y-5 p-4 sm:p-6">
        <!-- ── Header ── -->
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div class="space-y-1.5">
                <p class="font-mono text-xs text-muted-foreground">
                    studio <span class="opacity-50">/</span> admin
                    <span class="opacity-50">/</span>
                    <span class="text-foreground">dashboard</span>
                </p>
                <h1
                    class="font-mono text-xl font-bold tracking-tight sm:text-2xl"
                >
                    <span class="text-primary">$</span>
                    whoami
                    <span class="font-normal text-muted-foreground">→</span>
                    {{ studio.name
                    }}<span aria-hidden="true" class="ui-cursor text-primary"
                        >▍</span
                    >
                </h1>
            </div>

            <div class="flex flex-wrap items-center gap-2 font-mono text-sm">
                <a
                    href="/"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex h-10 items-center gap-2 rounded-lg border border-border bg-card px-3.5 transition-colors hover:bg-muted"
                >
                    ↗ open site
                </a>
                <Link
                    :href="route('messageIndex')"
                    class="inline-flex h-10 items-center gap-2 rounded-lg border border-border bg-card px-3.5 transition-colors hover:bg-muted"
                >
                    inbox/
                    <span
                        v-if="stats.unreadMessages"
                        class="rounded bg-primary px-1.5 text-xs font-bold text-primary-foreground"
                    >
                        {{ stats.unreadMessages }}
                    </span>
                </Link>
                <Link
                    :href="route('projectCreate')"
                    class="inline-flex h-10 items-center gap-2 rounded-lg bg-primary px-4 font-bold text-primary-foreground transition-colors hover:bg-primary/90"
                >
                    <Plus class="size-4" />
                    new project
                </Link>
            </div>
        </header>

        <!-- ── Headline numbers ── -->
        <section
            aria-label="Key numbers"
            class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4"
        >
            <Link
                v-for="tile in tiles"
                :key="tile.key"
                :href="tile.href"
                class="group flex flex-col gap-2.5 rounded-xl border border-border bg-card p-4 transition-colors hover:border-foreground/40"
            >
                <span class="font-mono text-xs text-muted-foreground">
                    <span class="text-syntax">const</span>
                    <span class="text-foreground"> {{ tile.key }}</span> =
                </span>
                <span class="flex items-end justify-between gap-3">
                    <span
                        :class="[
                            'font-mono text-3xl font-bold tracking-tight tabular-nums',
                            tile.highlight ? 'text-primary' : '',
                        ]"
                    >
                        {{ tile.value
                        }}<span class="text-xl text-muted-foreground/60"
                            >;</span
                        >
                    </span>
                    <svg
                        v-if="tile.spark"
                        aria-hidden="true"
                        width="84"
                        height="28"
                        viewBox="0 0 84 28"
                        class="text-primary"
                    >
                        <polyline
                            :points="tile.spark"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.75"
                            stroke-linejoin="round"
                            stroke-linecap="round"
                        />
                    </svg>
                </span>
                <span class="font-mono text-xs text-muted-foreground">
                    // {{ tile.note }}
                </span>
            </Link>
        </section>

        <!-- ── Enquiries chart + counts ── -->
        <div class="grid gap-3 xl:grid-cols-3">
            <section
                class="flex min-w-0 flex-col gap-4 rounded-xl border border-border bg-card p-5 xl:col-span-2"
            >
                <header
                    class="flex flex-wrap items-start justify-between gap-3"
                >
                    <div class="space-y-1">
                        <h2
                            class="font-mono text-sm font-medium text-muted-foreground"
                        >
                            enquiries.count()
                        </h2>
                        <p class="flex items-baseline gap-2.5 font-mono">
                            <span class="text-2xl font-bold tabular-nums">{{
                                rangeTotal
                            }}</span>
                            <span
                                v-if="weekDelta !== null"
                                :class="[
                                    'text-xs font-bold',
                                    weekDelta >= 0
                                        ? 'text-primary'
                                        : 'text-warn',
                                ]"
                            >
                                {{ weekDelta >= 0 ? '+' : '' }}{{ weekDelta }}%
                            </span>
                            <span class="text-xs text-muted-foreground">
                                {{
                                    weekDelta !== null
                                        ? 'vs previous 7 days'
                                        : `in the last ${range} days`
                                }}
                            </span>
                        </p>
                    </div>
                    <div
                        role="group"
                        aria-label="Time range"
                        class="flex gap-0.5 rounded-lg border border-border bg-background p-0.5 font-mono text-xs font-bold"
                    >
                        <button
                            v-for="option in [7, 30] as const"
                            :key="option"
                            type="button"
                            :aria-pressed="range === option"
                            :class="[
                                'h-8 min-w-12 rounded-md px-2 transition-colors',
                                range === option
                                    ? 'bg-muted text-primary'
                                    : 'text-muted-foreground hover:text-foreground',
                            ]"
                            @click="range = option"
                        >
                            {{ option }}d
                        </button>
                    </div>
                </header>

                <div
                    role="img"
                    :aria-label="`Bar chart: ${rangeTotal} enquiries in the last ${range} days`"
                    class="flex gap-2.5"
                >
                    <div
                        aria-hidden="true"
                        class="flex h-[200px] min-w-5 flex-col justify-between pb-[22px] text-right font-mono text-[11px] text-muted-foreground"
                    >
                        <span>{{ chart.top }}</span>
                        <span>{{ chart.top / 2 }}</span>
                        <span>0</span>
                    </div>
                    <div class="relative min-w-0 flex-1">
                        <div
                            aria-hidden="true"
                            class="absolute inset-x-0 top-[7px] flex h-[171px] flex-col justify-between"
                        >
                            <div class="border-t border-dashed border-border" />
                            <div class="border-t border-dashed border-border" />
                            <div class="border-t border-border" />
                        </div>
                        <div
                            :class="[
                                'relative flex h-[200px]',
                                range === 7 ? 'gap-3.5' : 'gap-1',
                            ]"
                        >
                            <div
                                v-for="(bar, index) in chart.bars"
                                :key="index"
                                :title="bar.title"
                                class="flex min-w-0 flex-1 flex-col gap-1.5"
                            >
                                <div class="mt-[7px] flex h-[171px] items-end">
                                    <div
                                        :class="[
                                            'w-full rounded-t-[3px] transition-[height] duration-300',
                                            bar.isLast
                                                ? 'bg-primary'
                                                : 'bg-primary/30',
                                        ]"
                                        :style="{ height: bar.height }"
                                    />
                                </div>
                                <span
                                    class="h-4 text-center font-mono text-[10px] whitespace-nowrap text-muted-foreground"
                                >
                                    {{ bar.label }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section
                class="flex flex-col gap-3 rounded-xl border border-border bg-card p-5 font-mono text-xs"
            >
                <h2 class="text-sm font-medium text-muted-foreground">
                    cat content.json
                </h2>
                <dl class="space-y-2">
                    <div
                        v-for="[name, count] in stackFile"
                        :key="name"
                        class="flex justify-between gap-3"
                    >
                        <dt class="text-info">"{{ name }}"</dt>
                        <dd class="tabular-nums">{{ count }}</dd>
                    </div>
                </dl>
                <Link
                    :href="route('studioContentEdit')"
                    class="mt-auto inline-flex items-center gap-1 text-primary hover:underline"
                >
                    edit home blocks <ArrowUpRight class="size-3.5" />
                </Link>
            </section>
        </div>

        <!-- ── Heatmap + activity log ── -->
        <div class="grid gap-3 lg:grid-cols-2">
            <section
                class="flex min-w-0 flex-col gap-4 rounded-xl border border-border bg-card p-5"
            >
                <header
                    class="flex flex-wrap items-baseline justify-between gap-2 font-mono"
                >
                    <h2 class="text-sm font-medium text-muted-foreground">
                        git log --projects
                        <span class="text-foreground"
                            >· {{ editsTotal }}
                            {{ editsTotal === 1 ? 'edit' : 'edits' }} in 30
                            days</span
                        >
                    </h2>
                    <span class="text-[11px] text-muted-foreground">
                        streak:
                        <span class="text-primary">{{ streak }}d</span>
                    </span>
                </header>
                <div
                    role="img"
                    :aria-label="`Project edits per day: ${editsTotal} in the last 30 days`"
                    class="grid w-max grid-flow-col grid-rows-7 gap-1"
                >
                    <template v-for="(cell, index) in heatmap" :key="index">
                        <span v-if="cell === null" class="size-4" />
                        <span
                            v-else
                            :title="cell.title"
                            :class="[
                                'size-4 rounded-[3px]',
                                heatClass[cell.level],
                            ]"
                        />
                    </template>
                </div>
                <div
                    aria-hidden="true"
                    class="flex items-center justify-end gap-1 font-mono text-[11px] text-muted-foreground"
                >
                    less
                    <span
                        v-for="shade in heatClass"
                        :key="shade"
                        :class="['size-3 rounded-[3px]', shade]"
                    />
                    more
                </div>
            </section>

            <section
                class="flex min-w-0 flex-col overflow-hidden rounded-xl border border-border bg-muted/40"
            >
                <div
                    class="flex items-center gap-2 border-b border-border bg-card px-3.5 py-2.5"
                >
                    <span class="size-2.5 rounded-full bg-border" />
                    <span class="size-2.5 rounded-full bg-border" />
                    <span class="size-2.5 rounded-full bg-border" />
                    <h2
                        class="flex-1 text-center font-mono text-xs font-normal text-muted-foreground"
                    >
                        tail -f activity.log
                    </h2>
                </div>
                <ol
                    v-if="log.length"
                    class="flex flex-col gap-1.5 overflow-x-auto p-4 font-mono text-xs"
                >
                    <li v-for="line in log" :key="line.key">
                        <Link
                            :href="line.href"
                            class="flex gap-3 whitespace-nowrap hover:underline"
                        >
                            <span class="text-muted-foreground/70">{{
                                logTime(line.at)
                            }}</span>
                            <span
                                :class="['min-w-10', levelClass[line.level]]"
                                >{{ line.level }}</span
                            >
                            <span>{{ line.event }}</span>
                            <span class="text-muted-foreground">{{
                                line.detail
                            }}</span>
                        </Link>
                    </li>
                    <li aria-hidden="true" class="mt-1 flex gap-2 text-primary">
                        ❯
                        <span class="ui-cursor text-foreground">▍</span>
                    </li>
                </ol>
                <p v-else class="p-4 font-mono text-xs text-muted-foreground">
                    // no activity yet
                </p>
            </section>
        </div>

        <!-- ── Inbox + projects ── -->
        <div class="grid gap-3 lg:grid-cols-2">
            <section
                class="flex min-w-0 flex-col rounded-xl border border-border bg-card"
            >
                <header
                    class="flex items-center justify-between px-5 pt-4 pb-2 font-mono"
                >
                    <h2 class="text-sm font-medium">
                        inbox
                        <span class="text-muted-foreground"
                            >· {{ stats.unreadMessages }} open</span
                        >
                    </h2>
                    <Link
                        :href="route('messageIndex')"
                        class="text-xs text-primary hover:underline"
                    >
                        view all →
                    </Link>
                </header>
                <ul v-if="recentMessages.length" class="px-1.5 pb-1.5">
                    <li v-for="message in recentMessages" :key="message.id">
                        <Link
                            :href="route('messageShow', message.id)"
                            class="flex items-start gap-3 rounded-md px-3.5 py-2.5 transition-colors hover:bg-muted"
                        >
                            <span
                                aria-hidden="true"
                                :class="[
                                    'font-mono text-sm leading-5',
                                    message.read_at
                                        ? 'text-muted-foreground/60'
                                        : 'text-primary',
                                ]"
                            >
                                {{ message.read_at ? '✓' : '◉' }}
                            </span>
                            <span class="min-w-0 flex-1 space-y-1">
                                <span
                                    :class="[
                                        'block truncate text-sm',
                                        message.read_at
                                            ? 'text-muted-foreground'
                                            : 'font-semibold',
                                    ]"
                                >
                                    {{ message.subject || '(no subject)' }}
                                    <span class="sr-only">{{
                                        message.read_at ? '(read)' : '(unread)'
                                    }}</span>
                                </span>
                                <span
                                    class="block truncate font-mono text-[11px] text-muted-foreground"
                                >
                                    #{{ message.id }} by {{ message.name }} ·
                                    {{ formatDate(message.created_at) }}
                                </span>
                            </span>
                        </Link>
                    </li>
                </ul>
                <p
                    v-else
                    class="px-5 pb-6 font-mono text-xs text-muted-foreground"
                >
                    // inbox is empty
                </p>
            </section>

            <section
                class="flex min-w-0 flex-col rounded-xl border border-border bg-card"
            >
                <header
                    class="flex items-center justify-between px-5 pt-4 pb-2 font-mono"
                >
                    <h2 class="text-sm font-medium">
                        projects
                        <span class="text-muted-foreground"
                            >· recent changes</span
                        >
                    </h2>
                    <Link
                        :href="route('projectIndex')"
                        class="text-xs text-primary hover:underline"
                    >
                        ls projects/ →
                    </Link>
                </header>
                <div
                    v-if="recentProjects.length"
                    class="overflow-x-auto px-1.5 pb-2"
                >
                    <table class="w-full min-w-[420px] border-collapse text-sm">
                        <thead>
                            <tr
                                class="font-mono text-[11px] text-muted-foreground"
                            >
                                <th
                                    scope="col"
                                    class="border-b border-border px-3.5 py-2 text-left font-medium"
                                >
                                    title
                                </th>
                                <th
                                    scope="col"
                                    class="border-b border-border px-3.5 py-2 text-left font-medium"
                                >
                                    status
                                </th>
                                <th
                                    scope="col"
                                    class="border-b border-border px-3.5 py-2 text-right font-medium"
                                >
                                    updated
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="project in recentProjects"
                                :key="project.id"
                                class="hover:bg-muted/60"
                            >
                                <td
                                    class="border-b border-border/60 px-3.5 py-2.5"
                                >
                                    <Link
                                        :href="
                                            route('projectEdit', project.slug)
                                        "
                                        class="flex flex-col hover:underline"
                                    >
                                        <span class="font-medium">
                                            {{ project.title }}
                                            <span
                                                v-if="project.is_featured"
                                                class="text-warn"
                                                title="Featured"
                                                >★</span
                                            >
                                        </span>
                                        <span
                                            class="font-mono text-xs text-muted-foreground"
                                            >{{ project.slug }}</span
                                        >
                                    </Link>
                                </td>
                                <td
                                    :class="[
                                        'border-b border-border/60 px-3.5 py-2.5 font-mono text-xs whitespace-nowrap',
                                        project.is_published
                                            ? 'text-primary'
                                            : 'text-muted-foreground',
                                    ]"
                                >
                                    {{
                                        project.is_published
                                            ? '● live'
                                            : '○ draft'
                                    }}
                                </td>
                                <td
                                    class="border-b border-border/60 px-3.5 py-2.5 text-right font-mono text-xs whitespace-nowrap text-muted-foreground"
                                >
                                    {{ formatDate(project.updated_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p
                    v-else
                    class="px-5 pb-6 font-mono text-xs text-muted-foreground"
                >
                    // no projects yet
                </p>
            </section>
        </div>

        <!-- ── Profile completeness as lint ── -->
        <section
            class="flex flex-col gap-4 rounded-xl border border-border bg-card p-5 font-mono text-sm"
        >
            <header class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 class="text-sm font-medium text-muted-foreground">
                    <span class="text-primary">$</span>
                    php artisan profile:lint
                </h2>
                <span class="text-xs text-muted-foreground">
                    <span v-if="warnings" class="text-warn"
                        >{{ warnings }}
                        {{ warnings === 1 ? 'warning' : 'warnings' }}</span
                    >
                    <span v-else class="text-primary">all checks passed</span>
                    · 0 errors
                </span>
            </header>
            <div class="grid gap-6 md:grid-cols-2">
                <div
                    v-for="profile in profiles"
                    :key="profile.file"
                    class="flex flex-col gap-2"
                >
                    <div class="flex justify-between gap-3">
                        <span>{{ profile.file }}</span>
                        <span class="text-primary"
                            >{{ profile.score.percent }}%</span
                        >
                    </div>
                    <div
                        role="progressbar"
                        :aria-label="`${profile.file} completeness`"
                        :aria-valuenow="profile.score.percent"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        class="h-1.5 overflow-hidden rounded-full bg-muted"
                    >
                        <div
                            class="h-full bg-primary"
                            :style="{ width: `${profile.score.percent}%` }"
                        />
                    </div>
                    <ul class="space-y-1 text-xs">
                        <li v-for="field in profile.score.missing" :key="field">
                            <span class="text-warn">⚠</span>
                            {{ field.toLowerCase() }} missing
                        </li>
                        <li v-if="!profile.score.missing.length">
                            <span class="text-primary">✓</span>
                            every field is set
                        </li>
                    </ul>
                    <Link
                        v-if="profile.score.missing.length"
                        :href="profile.href"
                        class="text-xs text-primary hover:underline"
                    >
                        fix {{ profile.file }} →
                    </Link>
                </div>
            </div>
        </section>
    </div>
</template>
