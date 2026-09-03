<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Briefcase, Pencil, Plus } from '@lucide/vue';
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
import experienceRoutes from '@/routes/admin/experiences';
import type { Experience } from '@/types';

type Props = {
    experiences: Experience[];
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Experience', href: experienceRoutes.index() }],
    },
});

const { items, move, saving } = useReorder<Experience>(
    () => props.experiences,
    experienceRoutes.reorder().url,
);

function period(experience: Experience): string {
    const format = (value: string | null) =>
        value
            ? new Date(value).toLocaleDateString(undefined, {
                  month: 'short',
                  year: 'numeric',
              })
            : 'Present';

    return `${format(experience.start_date)} — ${format(experience.end_date)}`;
}
</script>

<template>
    <Head title="Experience" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="Experience"
            description="Your work history, shown as a timeline on the public site."
        >
            <template #actions>
                <Button
                    as-child
                    class="bg-gradient-to-r from-amber-500 to-orange-500 text-white hover:from-amber-600 hover:to-orange-600"
                >
                    <Link :href="experienceRoutes.create()">
                        <Plus class="size-4" />
                        New role
                    </Link>
                </Button>
            </template>
        </AdminPageHeader>

        <motion.div
            :variants="fadeUp"
            initial="hidden"
            animate="visible"
            class="overflow-hidden rounded-xl border border-border"
        >
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-20">Order</TableHead>
                        <TableHead>Role</TableHead>
                        <TableHead class="hidden sm:table-cell"
                            >Period</TableHead
                        >
                        <TableHead class="w-24 text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="(experience, index) in items"
                        :key="experience.id"
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
                            <div class="flex items-center gap-3">
                                <span class="rounded-lg bg-amber-500/10 p-1.5">
                                    <Briefcase
                                        class="size-3.5 text-amber-600 dark:text-amber-400"
                                    />
                                </span>
                                <div>
                                    <p class="font-medium">
                                        {{ experience.role }}
                                        <Badge
                                            v-if="!experience.end_date"
                                            variant="secondary"
                                            class="ml-2 border-0 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                                        >
                                            Current
                                        </Badge>
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ experience.company }}
                                        <template v-if="experience.location">
                                            · {{ experience.location }}
                                        </template>
                                    </p>
                                </div>
                            </div>
                        </TableCell>

                        <TableCell
                            class="hidden text-sm text-muted-foreground sm:table-cell"
                        >
                            {{ period(experience) }}
                        </TableCell>

                        <TableCell>
                            <div
                                class="flex items-center justify-end gap-1 opacity-0 transition-opacity group-hover:opacity-100"
                            >
                                <Button as-child variant="ghost" size="icon-sm">
                                    <Link
                                        :href="
                                            experienceRoutes.edit(experience.id)
                                        "
                                        :aria-label="`Edit ${experience.role}`"
                                    >
                                        <Pencil class="size-4" />
                                    </Link>
                                </Button>
                                <DeleteAction
                                    :url="
                                        experienceRoutes.destroy(experience.id)
                                            .url
                                    "
                                    :label="`${experience.role} at ${experience.company}`"
                                />
                            </div>
                        </TableCell>
                    </TableRow>

                    <TableRow v-if="!items.length">
                        <TableCell
                            colspan="4"
                            class="py-12 text-center text-muted-foreground"
                        >
                            <div class="flex flex-col items-center gap-2">
                                <Briefcase
                                    class="size-8 text-muted-foreground/30"
                                />
                                <p>No roles yet — add your first one.</p>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </motion.div>
    </div>
</template>
