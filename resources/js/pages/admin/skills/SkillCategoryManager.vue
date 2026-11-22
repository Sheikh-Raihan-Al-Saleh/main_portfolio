<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Check, FolderTree, Plus } from '@lucide/vue';
import SkillCategoryController from '@/actions/App/Http/Controllers/Admin/SkillCategoryController';
import DeleteAction from '@/components/admin/DeleteAction.vue';
import ReorderButtons from '@/components/admin/ReorderButtons.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useReorder } from '@/composables/useReorder';
import type { SkillCategory } from '@/types';

/**
 * Inline management of the founder's skill categories: add, rename,
 * reorder, and delete (only when empty).
 */
const props = defineProps<{ categories: SkillCategory[] }>();

const { items, move, saving } = useReorder(
    () => props.categories,
    'skill-categories',
);
</script>

<template>
    <section class="ui-card p-5 sm:p-6">
        <div class="mb-4 flex items-center gap-3">
            <span class="ui-icon-tile"><FolderTree class="size-5" /></span>
            <div>
                <h2 class="admin-title text-2xl">Categories</h2>
                <p class="text-sm text-muted-foreground">
                    The groups skills are shown in on the founder page, in this
                    order.
                </p>
            </div>
        </div>

        <ul class="grid gap-2">
            <li
                v-for="(category, index) in items"
                :key="category.id"
                class="flex flex-wrap items-center gap-2 rounded-lg border border-border p-2"
            >
                <ReorderButtons
                    :index="index"
                    :total="items.length"
                    :disabled="saving"
                    @move="move"
                />

                <Form
                    v-bind="SkillCategoryController.update.form(category.id)"
                    class="flex min-w-0 flex-1 items-center gap-2"
                    v-slot="{ errors, processing }"
                >
                    <Input
                        name="name"
                        :default-value="category.name"
                        class="min-w-0 flex-1"
                        :aria-label="`Rename ${category.name}`"
                    />
                    <Button
                        type="submit"
                        variant="outline"
                        size="icon"
                        :disabled="processing"
                        aria-label="Save name"
                    >
                        <Check class="size-4" />
                    </Button>
                    <InputError :message="errors.name" />
                </Form>

                <span class="w-16 text-right text-xs text-muted-foreground">
                    {{ category.skills.length }}
                    {{ category.skills.length === 1 ? 'skill' : 'skills' }}
                </span>

                <!-- Only an empty category can be deleted, so no skill is lost. -->
                <DeleteAction
                    v-if="category.skills.length === 0"
                    :url="SkillCategoryController.destroy.url(category.id)"
                    :label="category.name"
                    description="The category is empty, so no skills are affected."
                />
                <span v-else class="size-9" aria-hidden="true" />
            </li>
        </ul>

        <Form
            v-bind="SkillCategoryController.store.form()"
            class="mt-4 flex flex-wrap items-start gap-2"
            reset-on-success
            v-slot="{ errors, processing }"
        >
            <div class="grid min-w-48 flex-1 gap-1">
                <Input
                    name="name"
                    placeholder="New category, e.g. Mobile"
                    aria-label="New category name"
                />
                <InputError :message="errors.name" />
            </div>
            <Button type="submit" :disabled="processing">
                <Plus class="size-4" />
                Add category
            </Button>
        </Form>
    </section>
</template>
