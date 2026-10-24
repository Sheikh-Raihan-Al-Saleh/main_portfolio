<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref } from 'vue';
import FieldGroup from '@/components/admin/form/FieldGroup.vue';
import FormFooter from '@/components/admin/form/FormFooter.vue';
import InputError from '@/components/InputError.vue';
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
import { cn } from '@/lib/utils';
import skills from '@/routes/admin/skills';
import type { Skill } from '@/types';

/**
 * The skill form. Renders as a card on its own page, or as the body of a
 * modal on the list page (`mode="dialog"`), where it emits `saved` / `cancel`.
 */
type Props = {
    skill?: Skill;
    categories: Record<string, string>;
    action: Record<string, unknown>;
    submitLabel: string;
    mode?: 'page' | 'dialog';
};

const props = withDefaults(defineProps<Props>(), {
    skill: undefined,
    mode: 'page',
});

const emit = defineEmits<{ saved: []; cancel: [] }>();

/** Mirrors the number input, so the live meter previews the value. */
const level = ref(props.skill?.proficiency ?? 75);
</script>

<template>
    <Form
        v-bind="action"
        :class="
            cn(
                'flex min-h-0 flex-col',
                mode === 'page' && 'ui-card max-w-xl p-6 sm:p-8',
            )
        "
        v-slot="{ errors, processing }"
        @success="emit('saved')"
    >
        <div
            :class="
                cn(
                    'flex flex-col gap-6',
                    mode === 'dialog' && 'min-h-0 overflow-y-auto px-6 py-6',
                )
            "
        >
            <FieldGroup
                title="Skill"
                description="The technology and the group it belongs to."
                :columns="2"
            >
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        name="name"
                        required
                        :default-value="skill?.name"
                        placeholder="TypeScript"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="category">Category</Label>
                    <Select
                        name="category"
                        :default-value="skill?.category ?? 'language'"
                    >
                        <SelectTrigger id="category">
                            <SelectValue placeholder="Pick a category" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="(label, key) in categories"
                                :key="key"
                                :value="key"
                            >
                                {{ label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.category" />
                </div>
            </FieldGroup>

            <FieldGroup
                title="Proficiency"
                description="Drives the width of the bar in the Skills section."
            >
                <div class="grid gap-3">
                    <div class="flex items-center gap-3">
                        <Input
                            id="proficiency"
                            v-model="level"
                            name="proficiency"
                            type="number"
                            min="0"
                            max="100"
                            required
                            class="w-24"
                            aria-label="Proficiency from 0 to 100"
                        />
                        <div
                            class="h-2 flex-1 overflow-hidden rounded-full bg-muted"
                            aria-hidden="true"
                        >
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-[var(--grad-from)] to-[var(--grad-to)] transition-[width] duration-300"
                                :style="{
                                    width: `${Math.max(0, Math.min(100, Number(level) || 0))}%`,
                                }"
                            />
                        </div>
                        <span
                            class="w-10 text-right text-sm font-semibold tabular-nums"
                            >{{ level }}%</span
                        >
                    </div>
                    <InputError :message="errors.proficiency" />
                </div>

                <div
                    class="flex items-center justify-between gap-4 rounded-xl border border-border bg-muted/40 p-4"
                >
                    <div>
                        <Label for="is_featured">Featured</Label>
                        <p class="text-xs text-muted-foreground">
                            Highlight this as one of your core skills.
                        </p>
                    </div>
                    <Switch
                        id="is_featured"
                        name="is_featured"
                        :default-value="skill?.is_featured ?? false"
                    />
                </div>
            </FieldGroup>
        </div>

        <FormFooter
            :mode="mode"
            :processing="processing"
            :submit-label="submitLabel"
            :cancel-href="skills.index().url"
            @cancel="emit('cancel')"
        />
    </Form>
</template>
