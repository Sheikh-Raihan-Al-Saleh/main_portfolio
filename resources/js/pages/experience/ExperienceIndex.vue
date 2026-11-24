<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Briefcase, Pencil, Plus } from '@lucide/vue';
import { motion } from 'motion-v';
import AdminPageHeader from '@/components/AdminPageHeader.vue';
import DeleteAction from '@/components/DeleteAction.vue';
import ExperienceForm from '@/components/Experience/ExperienceForm.vue';
import FormDialog from '@/components/Form/FormDialog.vue';
import ReorderButtons from '@/components/ReorderButtons.vue';
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
import { fadeUp } from '@/composables/motion';
import { formatPeriod } from '@/composables/text';
import { useEditDialog } from '@/composables/useEditDialog';
import { useExperienceList } from '@/composables/useExperienceList';
import { route, routeForm } from '@/composables/useRoute';
import type { Experience } from '@/types';
import type { ExperienceIndexProps } from '@/types';

const props = defineProps<ExperienceIndexProps>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Experience', href: route('experienceIndex') }],
    },
});

const { experienceList, move, saving } = useExperienceList(props);

/** Create and edit happen in a modal, so the list stays in view. */
const { dialogOpen, editing, openCreate, openEdit, closeDialog } =
    useEditDialog<Experience>();
</script>

<template>
    <Head title="Experience" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="Experience"
            description="Your work history, shown as a timeline on the public site."
        >
            <template #actions>
                <Button type="button" @click="openCreate">
                    <Plus class="size-4" />
                    New role
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
                        <TableHead>Role</TableHead>
                        <TableHead class="hidden sm:table-cell"
                            >Period</TableHead
                        >
                        <TableHead class="w-24 text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="(experience, index) in experienceList"
                        :key="experience.id"
                        class="group transition-colors hover:bg-accent/30"
                    >
                        <TableCell>
                            <ReorderButtons
                                :index="index"
                                :total="experienceList.length"
                                :disabled="saving"
                                @move="move"
                            />
                        </TableCell>

                        <TableCell class="w-full whitespace-normal">
                            <div class="flex items-center gap-3">
                                <span
                                    class="shrink-0 rounded-lg bg-amber-500/10 p-1.5"
                                >
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
                            {{
                                formatPeriod(
                                    experience.start_date,
                                    experience.end_date,
                                    'month',
                                )
                            }}
                        </TableCell>

                        <TableCell>
                            <div class="flex items-center justify-end gap-1">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon-sm"
                                    :aria-label="`Edit ${experience.role}`"
                                    @click="openEdit(experience)"
                                >
                                    <Pencil class="size-4" />
                                </Button>
                                <DeleteAction
                                    :url="
                                        route(
                                            'experienceDestroy',
                                            experience.id,
                                        )
                                    "
                                    :label="`${experience.role} at ${experience.company}`"
                                />
                            </div>
                        </TableCell>
                    </TableRow>

                    <TableRow v-if="!experienceList.length">
                        <TableCell
                            colspan="4"
                            class="py-12 text-center text-muted-foreground"
                        >
                            <div class="flex flex-col items-center gap-2">
                                <Briefcase
                                    class="size-8 text-muted-foreground/30"
                                />
                                <p>No roles yet — add your first one.</p>
                                <Button
                                    size="sm"
                                    class="mt-1"
                                    type="button"
                                    @click="openCreate"
                                >
                                    <Plus class="size-4" />
                                    New role
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </motion.div>

        <FormDialog
            v-model:open="dialogOpen"
            :title="editing ? 'Edit experience' : 'New experience'"
            :icon="Briefcase"
        >
            <ExperienceForm
                :key="editing?.id ?? 'new'"
                mode="dialog"
                :experience="editing ?? undefined"
                :action="
                    editing
                        ? routeForm('experienceUpdate', editing.id)
                        : routeForm('experienceStore')
                "
                :submit-label="editing ? 'Save changes' : 'Create experience'"
                @saved="closeDialog"
                @cancel="closeDialog"
            />
        </FormDialog>
    </div>
</template>
