<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Pencil, Plus, Sparkles } from '@lucide/vue';
import { motion } from 'motion-v';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import DeleteAction from '@/components/admin/DeleteAction.vue';
import ReorderButtons from '@/components/admin/ReorderButtons.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
import skillRoutes from '@/routes/admin/skills';
import type { Skill } from '@/types';

type Props = {
    skills: Skill[];
    categories: Record<string, string>;
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Skills', href: skillRoutes.index() }],
    },
});

const { items, move, saving } = useReorder<Skill>(
    () => props.skills,
    skillRoutes.reorder().url,
);

const categoryColors: Record<string, string> = {
    frontend: 'bg-primary/10 text-primary',
    backend: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
    devops: 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
    database: 'bg-violet-500/10 text-violet-600 dark:text-violet-400',
    design: 'bg-pink-500/10 text-pink-600 dark:text-pink-400',
    mobile: 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400',
};
</script>

<template>
    <Head title="Skills" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="Skills"
            description="Grouped by category on the public site, in the order set here."
        >
            <template #actions>
                <Button
                    as-child
                    class="bg-gradient-to-r from-violet-500 to-purple-500 text-white hover:from-violet-600 hover:to-purple-600"
                >
                    <Link :href="skillRoutes.create()">
                        <Plus class="size-4" />
                        New skill
                    </Link>
                </Button>
            </template>
        </AdminPageHeader>

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
                        <TableHead>Skill</TableHead>
                        <TableHead class="w-40">Category</TableHead>
                        <TableHead class="w-40">Proficiency</TableHead>
                        <TableHead class="w-24 text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="(skill, index) in items"
                        :key="skill.id"
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

                        <TableCell>
                            <div class="flex items-center gap-2">
                                <span class="rounded-lg bg-violet-500/10 p-1.5">
                                    <Sparkles
                                        class="size-3.5 text-violet-600 dark:text-violet-400"
                                    />
                                </span>
                                <span class="font-medium">{{
                                    skill.name
                                }}</span>
                                <Badge
                                    v-if="skill.is_featured"
                                    variant="secondary"
                                    class="border-0 bg-amber-500/10 text-amber-600 dark:text-amber-400"
                                >
                                    Featured
                                </Badge>
                            </div>
                        </TableCell>

                        <TableCell>
                            <Badge
                                variant="secondary"
                                :class="[
                                    'border-0',
                                    categoryColors[skill.category] ??
                                        'bg-muted text-muted-foreground',
                                ]"
                            >
                                {{
                                    categories[skill.category] ?? skill.category
                                }}
                            </Badge>
                        </TableCell>

                        <TableCell>
                            <div class="flex items-center gap-2">
                                <div
                                    class="h-2 w-full max-w-24 overflow-hidden rounded-full bg-muted"
                                >
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-violet-500 to-purple-500"
                                        :style="{
                                            width: `${skill.proficiency}%`,
                                        }"
                                    />
                                </div>
                                <span
                                    class="text-xs text-muted-foreground tabular-nums"
                                >
                                    {{ skill.proficiency }}%
                                </span>
                            </div>
                        </TableCell>

                        <TableCell>
                            <div
                                class="flex items-center justify-end gap-1 opacity-0 transition-opacity group-hover:opacity-100"
                            >
                                <Button as-child variant="ghost" size="icon-sm">
                                    <Link
                                        :href="skillRoutes.edit(skill.id)"
                                        :aria-label="`Edit ${skill.name}`"
                                    >
                                        <Pencil class="size-4" />
                                    </Link>
                                </Button>
                                <DeleteAction
                                    :url="skillRoutes.destroy(skill.id).url"
                                    :label="skill.name"
                                />
                            </div>
                        </TableCell>
                    </TableRow>

                    <TableRow v-if="!items.length">
                        <TableCell
                            colspan="5"
                            class="py-12 text-center text-muted-foreground"
                        >
                            <div class="flex flex-col items-center gap-2">
                                <Sparkles
                                    class="size-8 text-muted-foreground/30"
                                />
                                <p>No skills yet — add your first one.</p>
                                <Button as-child size="sm" class="mt-1">
                                    <Link :href="skillRoutes.create()">
                                        <Plus class="size-4" />
                                        New skill
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
