<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Pencil, Plus, Sparkles } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import SkillController from '@/actions/App/Http/Controllers/Skill/SkillController';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import DeleteAction from '@/components/admin/DeleteAction.vue';
import FormDialog from '@/components/admin/form/FormDialog.vue';
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
import { useEditDialog } from '@/composables/useEditDialog';
import { useReorder } from '@/composables/useReorder';
import { fadeUp } from '@/lib/motion';
import skillRoutes from '@/routes/admin/skills';
import type { Skill } from '@/types';
import type { AdminSkillsIndexProps } from '@/types';
import SkillCategoryManager from './SkillCategoryManager.vue';
import SkillForm from './SkillForm.vue';

const props = defineProps<AdminSkillsIndexProps>();

const skills = computed(() =>
    props.categories.flatMap((category) => category.skills),
);

const categoryNames = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.categories.map((category) => [category.id, category.name]),
    ),
);

const categoryOptions = computed(() =>
    props.categories.map((category) => ({
        value: category.id,
        label: category.name,
    })),
);

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Skills', href: skillRoutes.index() }],
    },
});

const { items, move, saving } = useReorder<Skill>(
    () => skills.value,
    skillRoutes.reorder().url,
);

/** Create and edit happen in a modal, so the list stays in view. */
const { dialogOpen, editing, openCreate, openEdit, closeDialog } =
    useEditDialog<Skill>();
</script>

<template>
    <Head title="Skills" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="Skills"
            description="Grouped by category on the public site, in the order set here."
        >
            <template #actions>
                <Button type="button" @click="openCreate">
                    <Plus class="size-4" />
                    New skill
                </Button>
            </template>
        </AdminPageHeader>

        <SkillCategoryManager :categories="categories" class="mb-6" />

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
                        <TableHead class="hidden w-40 sm:table-cell"
                            >Category</TableHead
                        >
                        <TableHead class="hidden w-40 md:table-cell"
                            >Proficiency</TableHead
                        >
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

                        <TableCell class="w-full max-w-0">
                            <div class="flex min-w-0 items-center gap-2">
                                <span
                                    class="shrink-0 rounded-lg bg-violet-500/10 p-1.5"
                                >
                                    <Sparkles
                                        class="size-3.5 text-violet-600 dark:text-violet-400"
                                    />
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate font-medium">{{
                                        skill.name
                                    }}</span>
                                    <!-- Category column is hidden on phones. -->
                                    <span
                                        class="block truncate text-xs text-muted-foreground sm:hidden"
                                        >{{
                                            categoryNames[
                                                skill.skill_category_id
                                            ]
                                        }}
                                        · {{ skill.proficiency }}%</span
                                    >
                                </span>
                                <Badge
                                    v-if="skill.is_featured"
                                    variant="secondary"
                                    class="hidden border-0 bg-amber-500/10 text-amber-600 sm:inline-flex dark:text-amber-400"
                                >
                                    Featured
                                </Badge>
                            </div>
                        </TableCell>

                        <TableCell class="hidden sm:table-cell">
                            <Badge
                                variant="secondary"
                                class="border-0 bg-muted text-muted-foreground"
                            >
                                {{ categoryNames[skill.skill_category_id] }}
                            </Badge>
                        </TableCell>

                        <TableCell class="hidden md:table-cell">
                            <div class="flex items-center gap-2">
                                <div
                                    class="h-2 w-full max-w-24 overflow-hidden rounded-full bg-muted"
                                >
                                    <div
                                        class="h-full rounded-full bg-brand"
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
                            <div class="flex items-center justify-end gap-1">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon-sm"
                                    :aria-label="`Edit ${skill.name}`"
                                    @click="openEdit(skill)"
                                >
                                    <Pencil class="size-4" />
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
                                <Button
                                    size="sm"
                                    class="mt-1"
                                    type="button"
                                    @click="openCreate"
                                >
                                    <Plus class="size-4" />
                                    New skill
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </motion.div>

        <FormDialog
            v-model:open="dialogOpen"
            :title="editing ? 'Edit skill' : 'New skill'"
            :icon="Sparkles"
        >
            <SkillForm
                :key="editing?.id ?? 'new'"
                mode="dialog"
                :skill="editing ?? undefined"
                :category-options="categoryOptions"
                :action="
                    editing
                        ? SkillController.update.form(editing.id)
                        : SkillController.store.form()
                "
                :submit-label="editing ? 'Save changes' : 'Create skill'"
                @saved="closeDialog"
                @cancel="closeDialog"
            />
        </FormDialog>
    </div>
</template>
