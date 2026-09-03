<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import educations from '@/routes/admin/educations';
import type { Education } from '@/types';

type Props = {
    education?: Education;
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
                <p class="text-xs text-muted-foreground">
                    Leave blank if you're still studying.
                </p>
                <InputError :message="errors.end_date" />
            </div>
        </div>

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

        <div class="flex items-center gap-3">
            <Button type="submit" :disabled="processing">
                {{ processing ? 'Saving…' : submitLabel }}
            </Button>
            <Button as-child type="button" variant="ghost">
                <Link :href="educations.index()">Cancel</Link>
            </Button>
        </div>
    </Form>
</template>
