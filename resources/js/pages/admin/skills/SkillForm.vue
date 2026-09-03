<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
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
import skills from '@/routes/admin/skills';
import type { Skill } from '@/types';

type Props = {
    skill?: Skill;
    categories: Record<string, string>;
    action: Record<string, unknown>;
    submitLabel: string;
};

defineProps<Props>();
</script>

<template>
    <Form
        v-bind="action"
        class="flex max-w-xl flex-col gap-6"
        v-slot="{ errors, processing }"
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

        <div class="grid gap-2">
            <Label for="proficiency">Proficiency (0–100)</Label>
            <Input
                id="proficiency"
                name="proficiency"
                type="number"
                min="0"
                max="100"
                required
                :default-value="skill?.proficiency ?? 75"
            />
            <p class="text-xs text-muted-foreground">
                Drives the width of the meter shown in the Skills section.
            </p>
            <InputError :message="errors.proficiency" />
        </div>

        <div
            class="flex items-center justify-between gap-4 rounded-lg border border-border p-4"
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

        <div class="flex items-center gap-3">
            <Button type="submit" :disabled="processing">
                {{ processing ? 'Saving…' : submitLabel }}
            </Button>
            <Button as-child type="button" variant="ghost">
                <Link :href="skills.index()">Cancel</Link>
            </Button>
        </div>
    </Form>
</template>
