<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { GraduationCap, Pencil, Plus } from '@lucide/vue';
import { motion } from 'motion-v';
import { ref } from 'vue';
import EducationController from '@/actions/App/Http/Controllers/Admin/EducationController';
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
import { useReorder } from '@/composables/useReorder';
import { fadeUp } from '@/lib/motion';
import educationRoutes from '@/routes/admin/educations';
import type { Education } from '@/types';
import EducationForm from './EducationForm.vue';

type Props = {
    educations: Education[];
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Education', href: educationRoutes.index() }],
    },
});

const { items, move, saving } = useReorder<Education>(
    () => props.educations,
    educationRoutes.reorder().url,
);

function period(education: Education): string {
    const format = (value: string | null) =>
        value ? new Date(value).getFullYear().toString() : 'Present';

    return `${format(education.start_date)} — ${format(education.end_date)}`;
}

/** Create and edit happen in a modal, so the list stays in view. */
const dialogOpen = ref(false);
const editing = ref<Education | null>(null);

function openCreate() {
    editing.value = null;
    dialogOpen.value = true;
}

function openEdit(education: Education) {
    editing.value = education;
    dialogOpen.value = true;
}
</script>

<template>
    <Head title="Education" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="Education"
            description="Degrees and qualifications shown beside your work history."
        >
            <template #actions>
                <Button
                    class="bg-gradient-to-r from-[var(--signal)] to-[var(--grad-to)] text-white shadow-[0_10px_24px_-12px_var(--brand-glow)] hover:opacity-95"
                    type="button"
                    @click="openCreate"
                >
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

                        <TableCell>
                            <div class="flex items-center gap-3">
                                <span
                                    class="rounded-lg bg-emerald-500/10 p-1.5"
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
                            {{ period(education) }}
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
                @saved="dialogOpen = false"
                @cancel="dialogOpen = false"
            />
        </FormDialog>
    </div>
</template>
