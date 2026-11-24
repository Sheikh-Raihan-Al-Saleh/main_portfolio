<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { GraduationCap, Pencil, Plus } from '@lucide/vue';
import { motion } from 'motion-v';
import AdminPageHeader from '@/components/AdminPageHeader.vue';
import DeleteAction from '@/components/DeleteAction.vue';
import EducationForm from '@/components/Education/EducationForm.vue';
import FormDialog from '@/components/Form/FormDialog.vue';
import ReorderButtons from '@/components/ReorderButtons.vue';
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
import { useEducationList } from '@/composables/useEducationList';
import { route, routeForm } from '@/composables/useRoute';
import type { Education } from '@/types';
import type { EducationIndexProps } from '@/types';

const props = defineProps<EducationIndexProps>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Education', href: route('educationIndex') }],
    },
});

const { educationList, move, saving } = useEducationList(props);

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
                        v-for="(education, index) in educationList"
                        :key="education.id"
                        class="group transition-colors hover:bg-accent/30"
                    >
                        <TableCell>
                            <ReorderButtons
                                :index="index"
                                :total="educationList.length"
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
                                        route('educationDestroy', education.id)
                                    "
                                    :label="`${education.degree} at ${education.institution}`"
                                />
                            </div>
                        </TableCell>
                    </TableRow>

                    <TableRow v-if="!educationList.length">
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
                        ? routeForm('educationUpdate', editing.id)
                        : routeForm('educationStore')
                "
                :submit-label="editing ? 'Save changes' : 'Create education'"
                @saved="closeDialog"
                @cancel="closeDialog"
            />
        </FormDialog>
    </div>
</template>
