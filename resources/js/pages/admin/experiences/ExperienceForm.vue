<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import FileField from '@/components/admin/FileField.vue';
import TagInput from '@/components/admin/TagInput.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import experiences from '@/routes/admin/experiences';
import type { Experience } from '@/types';

type Props = {
    experience?: Experience;
    action: Record<string, unknown>;
    submitLabel: string;
};

defineProps<Props>();

function dateValue(value: string | null | undefined): string {
    return value ? value.slice(0, 10) : '';
}
</script>

<template>
    <Form
        v-bind="action"
        class="flex max-w-3xl flex-col gap-6"
        v-slot="{ errors, processing }"
    >
        <div class="grid gap-6 sm:grid-cols-2">
            <div class="grid gap-2">
                <Label for="role">Role</Label>
                <Input
                    id="role"
                    name="role"
                    required
                    :default-value="experience?.role"
                    placeholder="Senior Software Engineer"
                />
                <InputError :message="errors.role" />
            </div>

            <div class="grid gap-2">
                <Label for="company">Company</Label>
                <Input
                    id="company"
                    name="company"
                    required
                    :default-value="experience?.company"
                    placeholder="Nexus Software"
                />
                <InputError :message="errors.company" />
            </div>

            <div class="grid gap-2">
                <Label for="employment_type">Employment type</Label>
                <Input
                    id="employment_type"
                    name="employment_type"
                    :default-value="experience?.employment_type ?? ''"
                    placeholder="Full-time"
                />
                <InputError :message="errors.employment_type" />
            </div>

            <div class="grid gap-2">
                <Label for="location">Location</Label>
                <Input
                    id="location"
                    name="location"
                    :default-value="experience?.location ?? ''"
                    placeholder="Remote"
                />
                <InputError :message="errors.location" />
            </div>

            <div class="grid gap-2">
                <Label for="start_date">Start date</Label>
                <Input
                    id="start_date"
                    name="start_date"
                    type="date"
                    required
                    :default-value="dateValue(experience?.start_date)"
                />
                <InputError :message="errors.start_date" />
            </div>

            <div class="grid gap-2">
                <Label for="end_date">End date</Label>
                <Input
                    id="end_date"
                    name="end_date"
                    type="date"
                    :default-value="dateValue(experience?.end_date)"
                />
                <p class="text-xs text-muted-foreground">
                    Leave blank if this is your current role.
                </p>
                <InputError :message="errors.end_date" />
            </div>
        </div>

        <div class="grid gap-2">
            <Label for="company_url">Company URL</Label>
            <Input
                id="company_url"
                name="company_url"
                type="url"
                :default-value="experience?.company_url ?? ''"
                placeholder="https://…"
            />
            <InputError :message="errors.company_url" />
        </div>

        <div class="grid gap-2">
            <Label for="description">Description</Label>
            <Textarea
                id="description"
                name="description"
                rows="4"
                :default-value="experience?.description ?? ''"
                placeholder="What you were responsible for."
            />
            <InputError :message="errors.description" />
        </div>

        <div class="grid gap-2">
            <Label>Highlights</Label>
            <TagInput
                name="highlights"
                :model-value="experience?.highlights"
                placeholder="One achievement per entry, press Enter to add"
            />
            <p class="text-xs text-muted-foreground">
                Rendered as a bullet list under the role.
            </p>
            <InputError :message="errors.highlights" />
        </div>

        <FileField
            name="logo"
            label="Company logo"
            remove-name="remove_logo"
            :current-url="experience?.logo_url"
            accept="image/*"
            preview
            hint="Optional. Square images work best, up to 2 MB."
        />
        <InputError :message="errors.logo" />

        <div class="flex items-center gap-3">
            <Button type="submit" :disabled="processing">
                {{ processing ? 'Saving…' : submitLabel }}
            </Button>
            <Button as-child type="button" variant="ghost">
                <Link :href="experiences.index()">Cancel</Link>
            </Button>
        </div>
    </Form>
</template>
