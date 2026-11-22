<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Cpu, Pencil, Plus } from '@lucide/vue';
import { ref } from 'vue';
import TechnologyController from '@/actions/App/Http/Controllers/Admin/TechnologyController';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import DeleteAction from '@/components/admin/DeleteAction.vue';
import FormDialog from '@/components/admin/form/FormDialog.vue';
import FormFooter from '@/components/admin/form/FormFooter.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import technologyRoutes from '@/routes/admin/technologies';
import type { Technology } from '@/types';

/**
 * The shared technology vocabulary: the tags on projects (and the public
 * archive filter). New names typed into a project's stack are added here
 * automatically; this page renames, merges by hand, or removes them.
 */
defineProps<{ technologies: Technology[] }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Technologies', href: technologyRoutes.index() },
        ],
    },
});

const dialogOpen = ref(false);
const editing = ref<Technology | null>(null);

function openCreate(): void {
    editing.value = null;
    dialogOpen.value = true;
}

function openEdit(technology: Technology): void {
    editing.value = technology;
    dialogOpen.value = true;
}
</script>

<template>
    <Head title="Technologies" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="Technologies"
            description="The tech-stack tags on your projects. Names typed into a project are added here automatically."
        >
            <template #actions>
                <Button type="button" @click="openCreate">
                    <Plus class="size-4" />
                    New technology
                </Button>
            </template>
        </AdminPageHeader>

        <div
            v-if="technologies.length"
            class="overflow-x-auto rounded-xl border border-border"
        >
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead class="hidden sm:table-cell">Slug</TableHead>
                        <TableHead class="w-32">Projects</TableHead>
                        <TableHead class="w-24 text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="technology in technologies"
                        :key="technology.id"
                    >
                        <TableCell class="font-medium">{{
                            technology.name
                        }}</TableCell>
                        <TableCell
                            class="hidden font-mono text-xs text-muted-foreground sm:table-cell"
                        >
                            {{ technology.slug }}
                        </TableCell>
                        <TableCell>{{
                            technology.projects_count ?? 0
                        }}</TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end gap-1">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    :aria-label="`Edit ${technology.name}`"
                                    @click="openEdit(technology)"
                                >
                                    <Pencil class="size-4" />
                                </Button>
                                <DeleteAction
                                    :url="
                                        TechnologyController.destroy.url(
                                            technology.id,
                                        )
                                    "
                                    :label="technology.name"
                                    description="It is removed from every project that uses it."
                                />
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div
            v-else
            class="ui-card flex flex-col items-center gap-3 border-dashed p-10 text-center"
        >
            <span class="ui-icon-tile"><Cpu class="size-5" /></span>
            <p class="font-semibold">No technologies yet</p>
            <p class="max-w-sm text-sm text-muted-foreground">
                Add one here, or type it into a project's stack.
            </p>
        </div>

        <FormDialog
            v-model:open="dialogOpen"
            :title="editing ? 'Edit technology' : 'New technology'"
            :icon="Cpu"
        >
            <Form
                :key="editing?.id ?? 'new'"
                v-bind="
                    editing
                        ? TechnologyController.update.form(editing.id)
                        : TechnologyController.store.form()
                "
                class="flex flex-col"
                v-slot="{ errors, processing }"
                @success="dialogOpen = false"
            >
                <div class="grid gap-5 px-6 py-6">
                    <div class="grid gap-2">
                        <Label for="technology-name">Name</Label>
                        <Input
                            id="technology-name"
                            name="name"
                            required
                            :default-value="editing?.name ?? ''"
                            placeholder="Laravel"
                        />
                        <InputError :message="errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="technology-icon">Icon</Label>
                        <Input
                            id="technology-icon"
                            name="icon"
                            :default-value="editing?.icon ?? ''"
                            placeholder="Optional Lucide icon name"
                        />
                        <InputError :message="errors.icon" />
                    </div>
                </div>
                <FormFooter
                    mode="dialog"
                    :processing="processing"
                    :submit-label="editing ? 'Save changes' : 'Add technology'"
                    @cancel="dialogOpen = false"
                />
            </Form>
        </FormDialog>
    </div>
</template>
