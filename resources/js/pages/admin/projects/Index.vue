<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ExternalLink,
    FolderKanban,
    Pencil,
    Plus,
    Search,
    Sparkles,
} from '@lucide/vue';
import { motion } from 'motion-v';
import { ref, watch } from 'vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import DeleteAction from '@/components/admin/DeleteAction.vue';
import ReorderButtons from '@/components/admin/ReorderButtons.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useReorder } from '@/composables/useReorder';
import { fadeUp } from '@/lib/motion';
import projectRoutes from '@/routes/admin/projects';
import type { Project } from '@/types';

type Props = {
    projects: Project[];
    filters: { search: string | null };
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Projects', href: projectRoutes.index() }],
    },
});

const { items, move, saving } = useReorder<Project>(
    () => props.projects,
    projectRoutes.reorder().url,
);

const search = ref(props.filters.search ?? '');

// Debounce so typing doesn't fire a request per keystroke.
let searchTimer: ReturnType<typeof setTimeout> | null = null;

watch(search, (value) => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        router.get(projectRoutes.index().url, value ? { search: value } : {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 300);
});
</script>

<template>
    <Head title="Projects" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="Projects"
            description="Everything in your portfolio. Drag order controls how they appear on the site."
        >
            <template #actions>
                <Button
                    as-child
                    class="bg-gradient-to-r from-[var(--signal)] to-[var(--grad-to)] text-white shadow-[0_10px_24px_-12px_var(--brand-glow)] hover:opacity-95"
                >
                    <Link :href="projectRoutes.create()">
                        <Plus class="size-4" />
                        New project
                    </Link>
                </Button>
            </template>
        </AdminPageHeader>

        <div class="relative mb-4 max-w-sm">
            <Search
                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
            />
            <Input
                v-model="search"
                placeholder="Search projects…"
                class="pl-9"
            />
        </div>

        <motion.div
            :variants="fadeUp"
            initial="hidden"
            animate="visible"
            class="overflow-x-auto rounded-xl border border-border"
        >
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-20">Order</TableHead>
                        <TableHead>Project</TableHead>
                        <TableHead class="hidden md:table-cell"
                            >Stack</TableHead
                        >
                        <TableHead class="hidden w-32 sm:table-cell"
                            >Context</TableHead
                        >
                        <TableHead class="hidden w-32 sm:table-cell"
                            >Status</TableHead
                        >
                        <TableHead class="w-32 text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="(project, index) in items"
                        :key="project.id"
                        class="group transition-colors hover:bg-accent/30"
                    >
                        <TableCell>
                            <ReorderButtons
                                :index="index"
                                :total="items.length"
                                :disabled="saving"
                                @move="move"
                            />
                        </TableCell>

                        <TableCell class="w-full max-w-0 whitespace-normal">
                            <div class="flex items-center gap-2 sm:gap-3">
                                <div
                                    v-if="project.cover_image_url"
                                    class="relative size-8 shrink-0 overflow-hidden rounded-lg bg-muted sm:size-10"
                                >
                                    <img
                                        :src="project.cover_image_url"
                                        alt=""
                                        class="size-full object-cover"
                                    />
                                </div>
                                <div
                                    v-else
                                    class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500/10 to-cyan-500/10 sm:size-10"
                                >
                                    <FolderKanban
                                        class="size-4 text-blue-500"
                                    />
                                </div>
                                <div class="min-w-0">
                                    <p
                                        class="line-clamp-2 font-medium break-words sm:truncate"
                                    >
                                        {{ project.title }}
                                    </p>
                                    <p
                                        class="hidden truncate font-mono text-xs text-muted-foreground sm:block"
                                    >
                                        /{{ project.slug }}
                                    </p>
                                    <!-- Status column is hidden on phones; show it inline. -->
                                    <p
                                        class="mt-0.5 text-xs font-medium sm:hidden"
                                        :class="
                                            project.is_published
                                                ? 'text-emerald-600 dark:text-emerald-400'
                                                : 'text-amber-600 dark:text-amber-400'
                                        "
                                    >
                                        {{
                                            project.is_published
                                                ? 'Published'
                                                : 'Draft'
                                        }}
                                    </p>
                                </div>
                            </div>
                        </TableCell>

                        <TableCell class="hidden md:table-cell">
                            <div class="flex flex-wrap gap-1">
                                <Badge
                                    v-for="tech in (
                                        project.tech_stack ?? []
                                    ).slice(0, 3)"
                                    :key="tech"
                                    variant="secondary"
                                    class="border-0 bg-blue-500/10 text-blue-600 dark:text-blue-400"
                                >
                                    {{ tech }}
                                </Badge>
                            </div>
                        </TableCell>

                        <TableCell class="hidden sm:table-cell">
                            <Badge
                                variant="outline"
                                :class="
                                    project.context === 'company'
                                        ? 'border-0 bg-blue-500/10 text-blue-600 dark:text-blue-400'
                                        : 'border-0 bg-violet-500/10 text-violet-600 dark:text-violet-400'
                                "
                            >
                                {{
                                    project.context === 'company'
                                        ? 'Company'
                                        : 'Personal'
                                }}
                            </Badge>
                        </TableCell>

                        <TableCell class="hidden sm:table-cell">
                            <div class="flex flex-wrap gap-1">
                                <Badge
                                    :variant="
                                        project.is_published
                                            ? 'default'
                                            : 'outline'
                                    "
                                    :class="
                                        project.is_published
                                            ? 'border-0 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                            : 'border-0 bg-amber-500/10 text-amber-600 dark:text-amber-400'
                                    "
                                >
                                    {{
                                        project.is_published
                                            ? 'Published'
                                            : 'Draft'
                                    }}
                                </Badge>
                                <Badge
                                    v-if="project.is_featured"
                                    variant="secondary"
                                    class="border-0 bg-violet-500/10 text-violet-600 dark:text-violet-400"
                                >
                                    Featured
                                </Badge>
                            </div>
                        </TableCell>

                        <TableCell>
                            <div
                                class="grid grid-cols-2 justify-items-center gap-0.5 transition-opacity sm:flex sm:items-center sm:justify-end sm:gap-1 pointer-fine:opacity-0 pointer-fine:group-focus-within:opacity-100 pointer-fine:group-hover:opacity-100"
                            >
                                <Button
                                    v-if="project.is_published"
                                    as-child
                                    variant="ghost"
                                    size="icon-sm"
                                >
                                    <a
                                        :href="`/projects/${project.slug}`"
                                        target="_blank"
                                        rel="noopener"
                                        :aria-label="`View ${project.title}`"
                                    >
                                        <ExternalLink class="size-4" />
                                    </a>
                                </Button>

                                <Button
                                    as-child
                                    variant="ghost"
                                    size="icon-sm"
                                    :class="
                                        project.has_landing_page
                                            ? 'text-brand'
                                            : undefined
                                    "
                                >
                                    <Link
                                        :href="`/admin/projects/${project.slug}/landing`"
                                        :aria-label="`Landing page for ${project.title}`"
                                        :title="
                                            project.has_landing_page
                                                ? 'Edit landing page'
                                                : 'Create landing page'
                                        "
                                    >
                                        <Sparkles class="size-4" />
                                    </Link>
                                </Button>

                                <Button as-child variant="ghost" size="icon-sm">
                                    <Link
                                        :href="projectRoutes.edit(project.slug)"
                                        :aria-label="`Edit ${project.title}`"
                                    >
                                        <Pencil class="size-4" />
                                    </Link>
                                </Button>

                                <DeleteAction
                                    :url="
                                        projectRoutes.destroy(project.slug).url
                                    "
                                    :label="project.title"
                                />
                            </div>
                        </TableCell>
                    </TableRow>

                    <TableRow v-if="!items.length">
                        <TableCell
                            colspan="6"
                            class="py-12 text-center text-muted-foreground"
                        >
                            <div class="flex flex-col items-center gap-2">
                                <FolderKanban
                                    class="size-8 text-muted-foreground/30"
                                />
                                <p>
                                    {{
                                        filters.search
                                            ? 'No projects match that search.'
                                            : 'No projects yet — create your first one.'
                                    }}
                                </p>
                                <Button
                                    v-if="!filters.search"
                                    as-child
                                    size="sm"
                                    class="mt-1"
                                >
                                    <Link :href="projectRoutes.create()">
                                        <Plus class="size-4" />
                                        New project
                                    </Link>
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </motion.div>
    </div>
</template>
