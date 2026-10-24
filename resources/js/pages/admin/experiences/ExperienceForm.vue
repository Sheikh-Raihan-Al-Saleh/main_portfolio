<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import FileField from '@/components/admin/FileField.vue';
import FieldGroup from '@/components/admin/form/FieldGroup.vue';
import FormFooter from '@/components/admin/form/FormFooter.vue';
import TagInput from '@/components/admin/TagInput.vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';
import experiences from '@/routes/admin/experiences';
import type { Experience } from '@/types';

/**
 * The experience form, grouped into role, dates, details and logo. Renders
 * as a card on its own page, or as the body of a modal on the list page
 * (`mode="dialog"`), where it emits `saved` / `cancel` instead of linking.
 */
type Props = {
    experience?: Experience;
    action: Record<string, unknown>;
    submitLabel: string;
    mode?: 'page' | 'dialog';
};

withDefaults(defineProps<Props>(), { experience: undefined, mode: 'page' });

const emit = defineEmits<{ saved: []; cancel: [] }>();

function dateValue(value: string | null | undefined): string {
    return value ? value.slice(0, 10) : '';
}
</script>

<template>
    <Form
        v-bind="action"
        :class="
            cn(
                'flex min-h-0 flex-col',
                mode === 'page' && 'ui-card max-w-3xl p-6 sm:p-8',
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
                title="Role"
                description="What you did and where."
                :columns="2"
            >
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

                <div class="grid gap-2 sm:col-span-2">
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
            </FieldGroup>

            <FieldGroup
                title="Dates"
                description="Leave the end date blank for your current role."
                :columns="2"
            >
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
                    <InputError :message="errors.end_date" />
                </div>
            </FieldGroup>

            <FieldGroup
                title="Details"
                description="Shown under the role on your portfolio."
            >
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
                        Rendered as a checklist under the role.
                    </p>
                    <InputError :message="errors.highlights" />
                </div>
            </FieldGroup>

            <FieldGroup
                title="Logo"
                description="Optional. Square images work best, up to 2 MB."
            >
                <div class="grid gap-2">
                    <FileField
                        name="logo"
                        label="Company logo"
                        remove-name="remove_logo"
                        :current-url="experience?.logo_url"
                        accept="image/*"
                        preview
                    />
                    <InputError :message="errors.logo" />
                </div>
            </FieldGroup>
        </div>

        <FormFooter
            :mode="mode"
            :processing="processing"
            :submit-label="submitLabel"
            :cancel-href="experiences.index().url"
            @cancel="emit('cancel')"
        />
    </Form>
</template>
