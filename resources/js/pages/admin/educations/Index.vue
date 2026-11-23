<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { GraduationCap, Pencil, Plus } from '@lucide/vue';
import { motion } from 'motion-v';
import EducationController from '@/actions/App/Http/Controllers/Education/EducationController';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import DeleteAction from '@/components/admin/DeleteAction.vue';
import FormDialog from '@/components/admin/form/FormDialog.vue';
import ReorderButtons from '@/components/admin/ReorderButtons.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useEditDialog } from '@/composables/useEditDialog';
import { useReorder } from '@/composables/useReorder';
import { fadeUp } from '@/lib/motion';
import { formatPeriod } from '@/lib/text';
import educationRoutes from '@/routes/admin/educations';
import type { Education } from '@/types';
import type { AdminEducationsIndexProps } from '@/types';
import EducationForm from './EducationForm.vue';

const props = defineProps<AdminEducationsIndexProps>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Education', href: educationRoutes.index() }],
    },
});

const { items, move, saving } = useReorder<Education>(
    () => props.educations,
    educationRoutes.reorder().url,
);

/** Create and edit happen in a modal, so the list stays in view. */
const { dialogOpen, editing, openCreate, openEdit, closeDialog } =
    useEditDialog<Education>();
</script>

<template>
    <Head title="Education" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="Education"
            description="Degrees and qualifications shown beside your work history."
        >
            <template #actions>
                <Button type="button" @click="openCreate">
                    <Plus class="size-4" />
                    New entry
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
                        <TableHead>Qualification</TableHead>
                        <TableHead class="hidden sm:table-cell"
                            >Period</TableHead
                        >
                        <TableHead class="w-24 text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow
                        v-for="(education, index) in items"
                        :key="education.id"
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

                        <TableCell class="w-full whitespace-normal">
                            <div class="flex items-center gap-3">
                                <span
                                    class="shrink-0 rounded-lg bg-emerald-500/10 p-1.5"
                                >
                                    <GraduationCap
                                        class="size-3.5 text-emerald-600 dark:text-emerald-400"
                                    />
                                </span>
                                <div>
                                    <p class="font-medium">
                                        {{ education.degree }}
                                        <template
                                            v-if="education.field_of_study"
                                        >
                                            , {{ education.field_of_study }}
                                        </template>
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ education.institution }}
                                    </p>
                                </div>
                            </div>
                        </TableCell>

                        <TableCell
                            class="hidden text-sm text-muted-foreground sm:table-cell"
                        >
                            {{
                                formatPeriod(
                                    education.start_date,
                                    education.end_date,
                                    'year',
                                )
                            }}
                        </TableCell>

                        <TableCell>
                            <div class="flex items-center justify-end gap-1">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon-sm"
                                    :aria-label="`Edit ${education.degree}`"
                                    @click="openEdit(education)"
                                >
                                    <Pencil class="size-4" />
                                </Button>
                                <DeleteAction
                                    :url="
                                        educationRoutes.destroy(education.id)
                                            .url
                                    "
                                    :label="`${education.degree} at ${education.institution}`"
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
                                <GraduationCap
                                    class="size-8 text-muted-foreground/30"
                                />
                                <p>No education entries yet.</p>
                                <Button
                                    size="sm"
                                    class="mt-1"
                                    type="button"
                                    @click="openCreate"
                                >
                                    <Plus class="size-4" />
                                    New entry
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </motion.div>

        <FormDialog
            v-model:open="dialogOpen"
            :title="editing ? 'Edit education' : 'New education'"
            :icon="GraduationCap"
        >
            <EducationForm
                :key="editing?.id ?? 'new'"
                mode="dialog"
                :education="editing ?? undefined"
                :action="
                    editing
                        ? EducationController.update.form(editing.id)
                        : EducationController.store.form()
                "
                :submit-label="editing ? 'Save changes' : 'Create education'"
                @saved="closeDialog"
                @cancel="closeDialog"
            />
        </FormDialog>
    </div>
</template>
