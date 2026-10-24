<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import FieldGroup from '@/components/admin/form/FieldGroup.vue';
import FormFooter from '@/components/admin/form/FormFooter.vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';
import educations from '@/routes/admin/educations';
import type { Education } from '@/types';

/**
 * The education form. Renders as a card on its own page, or as the body of a
 * modal on the list page (`mode="dialog"`), where it emits `saved` / `cancel`.
 */
type Props = {
    education?: Education;
    action: Record<string, unknown>;
    submitLabel: string;
    mode?: 'page' | 'dialog';
};

withDefaults(defineProps<Props>(), { education: undefined, mode: 'page' });

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
                title="Qualification"
                description="The degree and where you earned it."
                :columns="2"
            >
                <div class="grid gap-2">
                    <Label for="institution">Institution</Label>
                    <Input
                        id="institution"
                        name="institution"
                        required
                        :default-value="education?.institution"
                        placeholder="University of Dhaka"
                    />
                    <InputError :message="errors.institution" />
                </div>

                <div class="grid gap-2">
                    <Label for="degree">Degree</Label>
                    <Input
                        id="degree"
                        name="degree"
                        required
                        :default-value="education?.degree"
                        placeholder="BSc"
                    />
                    <InputError :message="errors.degree" />
                </div>

                <div class="grid gap-2">
                    <Label for="field_of_study">Field of study</Label>
                    <Input
                        id="field_of_study"
                        name="field_of_study"
                        :default-value="education?.field_of_study ?? ''"
                        placeholder="Computer Science"
                    />
                    <InputError :message="errors.field_of_study" />
                </div>

                <div class="grid gap-2">
                    <Label for="grade">Grade</Label>
                    <Input
                        id="grade"
                        name="grade"
                        :default-value="education?.grade ?? ''"
                        placeholder="3.8 / 4.0"
                    />
                    <InputError :message="errors.grade" />
                </div>
            </FieldGroup>

            <FieldGroup
                title="Dates"
                description="Leave the end date blank if you're still studying."
                :columns="2"
            >
                <div class="grid gap-2">
                    <Label for="start_date">Start date</Label>
                    <Input
                        id="start_date"
                        name="start_date"
                        type="date"
                        required
                        :default-value="dateValue(education?.start_date)"
                    />
                    <InputError :message="errors.start_date" />
                </div>

                <div class="grid gap-2">
                    <Label for="end_date">End date</Label>
                    <Input
                        id="end_date"
                        name="end_date"
                        type="date"
                        :default-value="dateValue(education?.end_date)"
                    />
                    <InputError :message="errors.end_date" />
                </div>
            </FieldGroup>

            <FieldGroup
                title="Details"
                description="Optional context shown on your portfolio."
            >
                <div class="grid gap-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        name="description"
                        rows="3"
                        :default-value="education?.description ?? ''"
                        placeholder="Focus areas, thesis, notable coursework."
                    />
                    <InputError :message="errors.description" />
                </div>
            </FieldGroup>
        </div>

        <FormFooter
            :mode="mode"
            :processing="processing"
            :submit-label="submitLabel"
            :cancel-href="educations.index().url"
            @cancel="emit('cancel')"
        />
    </Form>
</template>
