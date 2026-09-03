<script setup lang="ts">
import { Plus, Trash2 } from '@lucide/vue';
import MediaUploadField from '@/components/admin/landing/MediaUploadField.vue';
import type { Field } from '@/components/admin/landing/sectionSchema';
import TagInput from '@/components/admin/TagInput.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';

type Props = {
    fields: Field[];
    /** Laravel validation errors, keyed as `data.items.0.title`. */
    errors: Record<string, string>;
    /** Error-key prefix for the current nesting level. */
    prefix?: string;
};

const props = withDefaults(defineProps<Props>(), { prefix: 'data' });

/** The `data` payload being edited, owned by the parent via v-model. */
const model = defineModel<Record<string, unknown>>({ required: true });

function errorFor(field: Field, index?: number): string | undefined {
    const key =
        index === undefined
            ? `${props.prefix}.${field.key}`
            : `${props.prefix}.${index}${field.key ? `.${field.key}` : ''}`;

    return props.errors[key];
}

function rowsOf(field: Field): Record<string, unknown>[] {
    const value = model.value[field.key];

    return Array.isArray(value) ? (value as Record<string, unknown>[]) : [];
}

/**
 * A repeater whose child has an empty key edits a list of bare strings (the
 * gallery's image paths) rather than a list of objects.
 */
function isScalarRepeater(field: Field): boolean {
    return field.fields?.length === 1 && field.fields[0].key === '';
}

function addRow(field: Field) {
    const rows = rowsOf(field);

    if (isScalarRepeater(field)) {
        model.value[field.key] = [...rows, ''];

        return;
    }

    const blank: Record<string, unknown> = {};

    for (const child of field.fields ?? []) {
        blank[child.key] = child.kind === 'switch' ? false : '';
    }

    model.value[field.key] = [...rows, blank];
}

function removeRow(field: Field, index: number) {
    const rows = [...rowsOf(field)];
    rows.splice(index, 1);
    model.value[field.key] = rows;
}

function stringListValue(field: Field): string[] {
    const value = model.value[field.key];

    return Array.isArray(value) ? (value as string[]) : [];
}
</script>

<template>
    <div class="flex flex-col gap-5">
        <template v-for="field in props.fields" :key="field.key || field.label">
            <!-- Repeaters -->
            <div v-if="field.kind === 'repeater'" class="grid gap-3">
                <Label>{{ field.label }}</Label>

                <div
                    v-for="(row, index) in rowsOf(field)"
                    :key="index"
                    class="relative rounded-lg border border-border/70 p-4"
                >
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        class="absolute top-2 right-2 text-muted-foreground hover:text-destructive"
                        :aria-label="`Remove item ${index + 1}`"
                        @click="removeRow(field, index)"
                    >
                        <Trash2 class="size-4" />
                    </Button>

                    <!-- Bare-string rows (gallery images) -->
                    <template v-if="isScalarRepeater(field)">
                        <MediaUploadField
                            :model-value="(row as unknown as string) || null"
                            :label="field.fields![0].label"
                            :directory="field.fields![0].directory!"
                            @update:model-value="
                                (value) => {
                                    const rows = [...rowsOf(field)];
                                    rows[index] = value as unknown as Record<
                                        string,
                                        unknown
                                    >;
                                    model[field.key] = rows;
                                }
                            "
                        />
                        <InputError
                            :message="errorFor(field.fields![0], index)"
                        />
                    </template>

                    <!-- Object rows -->
                    <div v-else class="grid gap-4 pr-8">
                        <template
                            v-for="child in field.fields"
                            :key="child.key"
                        >
                            <div v-if="child.kind === 'media'">
                                <MediaUploadField
                                    :model-value="
                                        (row[child.key] as string) ?? null
                                    "
                                    :label="child.label"
                                    :directory="child.directory!"
                                    :video="child.video"
                                    :hint="child.hint"
                                    @update:model-value="
                                        (value) => (row[child.key] = value)
                                    "
                                />
                                <InputError :message="errorFor(child, index)" />
                            </div>

                            <div
                                v-else-if="child.kind === 'textarea'"
                                class="grid gap-2"
                            >
                                <Label>{{ child.label }}</Label>
                                <Textarea
                                    :model-value="
                                        (row[child.key] as string) ?? ''
                                    "
                                    rows="3"
                                    @update:model-value="
                                        (value) => (row[child.key] = value)
                                    "
                                />
                                <InputError :message="errorFor(child, index)" />
                            </div>

                            <div v-else class="grid gap-2">
                                <Label>{{ child.label }}</Label>
                                <Input
                                    :model-value="
                                        (row[child.key] as string) ?? ''
                                    "
                                    :type="
                                        child.kind === 'number'
                                            ? 'number'
                                            : 'text'
                                    "
                                    :step="
                                        child.kind === 'number'
                                            ? 'any'
                                            : undefined
                                    "
                                    :placeholder="child.placeholder"
                                    @update:model-value="
                                        (value) => (row[child.key] = value)
                                    "
                                />
                                <p
                                    v-if="child.hint"
                                    class="text-xs text-muted-foreground"
                                >
                                    {{ child.hint }}
                                </p>
                                <InputError :message="errorFor(child, index)" />
                            </div>
                        </template>
                    </div>
                </div>

                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="w-fit"
                    @click="addRow(field)"
                >
                    <Plus class="size-4" />
                    {{ field.addLabel ?? 'Add item' }}
                </Button>

                <InputError
                    :message="props.errors[`${props.prefix}.${field.key}`]"
                />
            </div>

            <!-- Free-form string list -->
            <div v-else-if="field.kind === 'stringList'" class="grid gap-2">
                <Label>{{ field.label }}</Label>
                <TagInput
                    :model-value="stringListValue(field)"
                    :placeholder="field.placeholder"
                    @update:model-value="(value) => (model[field.key] = value)"
                />
                <InputError :message="errorFor(field)" />
            </div>

            <!-- Media -->
            <div v-else-if="field.kind === 'media'">
                <MediaUploadField
                    :model-value="(model[field.key] as string) ?? null"
                    :label="field.label"
                    :directory="field.directory!"
                    :video="field.video"
                    :hint="field.hint"
                    @update:model-value="(value) => (model[field.key] = value)"
                />
                <InputError :message="errorFor(field)" />
            </div>

            <!-- Switch -->
            <div
                v-else-if="field.kind === 'switch'"
                class="flex items-center gap-3"
            >
                <Switch
                    :model-value="Boolean(model[field.key])"
                    @update:model-value="(value) => (model[field.key] = value)"
                />
                <div>
                    <Label>{{ field.label }}</Label>
                    <p v-if="field.hint" class="text-xs text-muted-foreground">
                        {{ field.hint }}
                    </p>
                </div>
            </div>

            <!-- Select -->
            <div v-else-if="field.kind === 'select'" class="grid gap-2">
                <Label>{{ field.label }}</Label>
                <Select
                    :model-value="(model[field.key] as string) ?? ''"
                    @update:model-value="(value) => (model[field.key] = value)"
                >
                    <SelectTrigger class="max-w-sm">
                        <SelectValue
                            :placeholder="`Choose ${field.label.toLowerCase()}`"
                        />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in field.options"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errorFor(field)" />
            </div>

            <!-- Textarea -->
            <div v-else-if="field.kind === 'textarea'" class="grid gap-2">
                <Label>{{ field.label }}</Label>
                <Textarea
                    :model-value="(model[field.key] as string) ?? ''"
                    :rows="field.key === 'markdown' ? 12 : 3"
                    :placeholder="field.placeholder"
                    :class="
                        field.key === 'markdown'
                            ? 'font-mono text-sm'
                            : undefined
                    "
                    @update:model-value="(value) => (model[field.key] = value)"
                />
                <p v-if="field.hint" class="text-xs text-muted-foreground">
                    {{ field.hint }}
                </p>
                <InputError :message="errorFor(field)" />
            </div>

            <!-- Text / number -->
            <div v-else class="grid gap-2">
                <Label>{{ field.label }}</Label>
                <Input
                    :model-value="(model[field.key] as string) ?? ''"
                    :type="field.kind === 'number' ? 'number' : 'text'"
                    :step="field.kind === 'number' ? 'any' : undefined"
                    :placeholder="field.placeholder"
                    class="max-w-xl"
                    @update:model-value="(value) => (model[field.key] = value)"
                />
                <p v-if="field.hint" class="text-xs text-muted-foreground">
                    {{ field.hint }}
                </p>
                <InputError :message="errorFor(field)" />
            </div>
        </template>
    </div>
</template>
