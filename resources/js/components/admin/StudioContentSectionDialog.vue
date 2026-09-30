<script setup lang="ts">
import { Plus, Trash2 } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { resolveIcon } from '@/lib/iconResolver';
import type { StudioContent } from '@/types';

/** A loose JSON object, as stored in the company's `content` column. */
type RecordOf = Record<string, unknown>;

/**
 * The smart modal for one studio story section.
 *
 * The section's shape is declared here (fields + optional repeater), so the
 * dialog can serve all eleven sections from one form: heading copy renders
 * first, then any list items, each editable inline with a live icon preview.
 * The modal is the "smart" part of the module: unsaved edits are discardable,
 * required fields are flagged before submit, and icon names preview as they
 * are typed.
 */

export type SectionKey = keyof Omit<StudioContent, 'ticker'>;

type FieldDef = {
    key: string;
    label: string;
    long?: boolean;
    required?: boolean;
    placeholder?: string;
};

type RepeaterDef = {
    key: 'items' | 'stages' | 'stack' | 'guarantees';
    label: string;
    addLabel: string;
    /** Field definitions for object rows; omitted for string rows. */
    fields?: FieldDef[];
    /** Marks the icon-name field so it can render a live preview. */
    iconField?: boolean;
};

type SectionDef = {
    title: string;
    description: string;
    fields: FieldDef[];
    repeater?: RepeaterDef;
};

const SECTION_DEFS = {
    capabilities: {
        title: 'What we build',
        description:
            'The chapter-one heading and the six capability cards under the 3D stack.',
        fields: [
            { key: 'eyebrow', label: 'Eyebrow', required: true },
            { key: 'title', label: 'Heading', required: true },
            { key: 'highlight', label: 'Highlighted words', placeholder: 'end to end' },
            { key: 'description', label: 'Description', long: true },
            { key: 'kicker', label: 'Stack kicker' },
            { key: 'stack_heading', label: 'Stack heading', long: true },
            { key: 'stack_intro', label: 'Stack intro', long: true },
            { key: 'footer_note', label: 'Footer note' },
            { key: 'footer_link', label: 'Footer link text' },
        ],
        repeater: {
            key: 'items',
            label: 'Capability cards',
            addLabel: 'Add capability',
            fields: [
                { key: 'icon', label: 'Lucide icon name', placeholder: 'Blocks' },
                { key: 'title', label: 'Title', required: true },
                { key: 'summary', label: 'Summary', long: true, required: true },
            ],
            iconField: true,
        },
    },
    engineering: {
        title: 'Engineering pipeline',
        description:
            'The chapter-three heading and the interactive pipeline stages.',
        fields: [
            { key: 'eyebrow', label: 'Eyebrow', required: true },
            { key: 'title', label: 'Heading', required: true },
            { key: 'highlight', label: 'Highlighted words', placeholder: 'fit together' },
            { key: 'description', label: 'Description', long: true },
        ],
        repeater: {
            key: 'stages',
            label: 'Pipeline stages',
            addLabel: 'Add stage',
            fields: [
                { key: 'icon', label: 'Lucide icon name', placeholder: 'Network' },
                { key: 'label', label: 'Label', required: true },
                { key: 'tagline', label: 'Tagline', required: true },
                { key: 'detail', label: 'Detail', long: true, required: true },
            ],
            iconField: true,
        },
    },
    value: {
        title: 'Why work with us',
        description: 'The chapter-four heading, value cards and guarantees.',
        fields: [
            { key: 'eyebrow', label: 'Eyebrow', required: true },
            { key: 'title', label: 'Heading', required: true },
            { key: 'highlight', label: 'Highlighted words', placeholder: 'serious delivery' },
            { key: 'description', label: 'Description', long: true },
        ],
        repeater: {
            key: 'items',
            label: 'Value cards',
            addLabel: 'Add value',
            fields: [
                { key: 'icon', label: 'Lucide icon name', placeholder: 'Handshake' },
                { key: 'title', label: 'Title', required: true },
                { key: 'summary', label: 'Summary', long: true, required: true },
            ],
            iconField: true,
        },
    },
    work: {
        title: 'Selected work',
        description: 'The chapter-two heading and the case-study labels.',
        fields: [
            { key: 'eyebrow', label: 'Eyebrow', required: true },
            { key: 'title', label: 'Heading', required: true },
            { key: 'highlight', label: 'Highlighted words', placeholder: 'built' },
            { key: 'description', label: 'Description', long: true },
            { key: 'featured_label', label: 'Featured label' },
            { key: 'case_study_label', label: 'Case study button' },
            { key: 'live_label', label: 'Live site button' },
            { key: 'source_label', label: 'Source link' },
            { key: 'archive_label', label: 'Archive button' },
            { key: 'empty_text', label: 'Empty-state text' },
        ],
    },
    founder_work: {
        title: "Founder's own work",
        description:
            'The chapter-six heading and the archive link under the founder preview.',
        fields: [
            { key: 'eyebrow', label: 'Eyebrow', required: true },
            { key: 'title', label: 'Heading', required: true },
            { key: 'highlight', label: 'Highlighted words', placeholder: 'client' },
            { key: 'description', label: 'Description', long: true },
            { key: 'archive_label', label: 'Archive button' },
            { key: 'empty_text', label: 'Empty-state text' },
        ],
    },
    about: {
        title: 'About',
        description: 'The chapter-five heading and mission label.',
        fields: [
            { key: 'eyebrow', label: 'Eyebrow', required: true },
            { key: 'title', label: 'Heading', required: true },
            { key: 'highlight', label: 'Highlighted words', placeholder: 'small' },
            { key: 'mission_label', label: 'Mission label' },
            { key: 'more_label', label: 'About link text' },
        ],
    },
    founder_bridge: {
        title: 'Studio ↔ Founder bridge',
        description:
            'The two-panel seam. {{name}} and {{role}} resolve to the live records.',
        fields: [
            { key: 'studio_label', label: 'Studio label', required: true },
            { key: 'studio_heading', label: 'Studio heading' },
            { key: 'studio_text', label: 'Studio text', long: true, required: true },
            { key: 'studio_link', label: 'Studio link text', required: true },
            { key: 'founder_label', label: 'Founder label', required: true },
            { key: 'founder_heading', label: 'Founder heading' },
            { key: 'founder_text', label: 'Founder text', long: true, required: true },
            { key: 'founder_link', label: 'Founder link text', required: true },
        ],
    },
    cta: {
        title: 'Closing CTA',
        description: 'The final call to action above the contact form.',
        fields: [
            { key: 'eyebrow', label: 'Eyebrow', required: true },
            { key: 'title', label: 'Heading', required: true },
            { key: 'highlight', label: 'Highlighted words', placeholder: "Let's build it." },
            { key: 'description', label: 'Description', long: true },
            { key: 'button_label', label: 'Button label', required: true },
            { key: 'email_label', label: 'Email button label' },
            { key: 'status_available', label: 'Status text (available)' },
            { key: 'status_unavailable', label: 'Status text (unavailable)' },
        ],
    },
    clients: {
        title: 'Client logos',
        description: 'The heading above the client logo wall.',
        fields: [
            { key: 'eyebrow', label: 'Eyebrow', required: true },
            { key: 'title', label: 'Heading', required: true },
            { key: 'highlight', label: 'Highlighted words', placeholder: 'for' },
            { key: 'description', label: 'Description', long: true },
        ],
    },
    contact: {
        title: 'Contact',
        description: 'The chapter-seven heading and the form labels.',
        fields: [
            { key: 'eyebrow', label: 'Eyebrow', required: true },
            { key: 'title', label: 'Heading', required: true },
            { key: 'highlight', label: 'Highlighted words', placeholder: 'building' },
            { key: 'description', label: 'Description', long: true },
            { key: 'email_heading', label: 'Email panel heading' },
            { key: 'email_note', label: 'Email panel note', long: true },
            { key: 'form_placeholder', label: 'Form placeholder', long: true },
            { key: 'form_submit', label: 'Submit button' },
            { key: 'form_reply_note', label: 'Reply note' },
        ],
    },
} satisfies Record<SectionKey, SectionDef>;

type Props = {
    open: boolean;
    sectionKey: SectionKey | null;
    /** The merged section content to seed the form with. */
    content: RecordOf | null;
};

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:open': [boolean];
    /** Emitted with the edited clone; the parent decides when to persist. */
    save: [SectionKey, RecordOf];
}>();

const def = computed(() =>
    props.sectionKey
        ? (SECTION_DEFS as Record<SectionKey, SectionDef>)[props.sectionKey]
        : null,
);

const form = ref<RecordOf>({});
const errors = ref<Record<string, string>>({});

watch(
    () => [props.open, props.sectionKey] as const,
    () => {
        if (!props.open) {
            return;
        }

        errors.value = {};
        form.value = JSON.parse(
            JSON.stringify(props.content ?? {}),
        ) as RecordOf;
    },
    { immediate: true },
);

/**
 * Object rows for the repeater. The guarantees section stores a bare string
 * list, so its rows are wrapped as `{ text }` and unwrapped on save.
 */
const repeaterRows = computed<RecordOf[]>(() => {
    const repeater = def.value?.repeater;

    if (!repeater) {
        return [];
    }

    const value = form.value[repeater.key];

    if (!Array.isArray(value)) {
        return [];
    }

    if (repeater.key === 'guarantees') {
        return (value as string[]).map((text) => ({ text }));
    }

    return value as RecordOf[];
});

function writeRows(rows: RecordOf[]) {
    const repeater = def.value?.repeater;

    if (!repeater) {
        return;
    }

    if (repeater.key === 'guarantees') {
        form.value.guarantees = rows.map((row) => String(row.text ?? ''));

        return;
    }

    form.value[repeater.key] = rows;
}

function addRow() {
    const repeater = def.value?.repeater;

    if (!repeater) {
        return;
    }

    const blank: RecordOf =
        repeater.key === 'guarantees'
            ? { text: '' }
            : Object.fromEntries(
                  (repeater.fields ?? []).map((field) => [
                      field.key,
                      field.key === 'tech' ? [] : '',
                  ]),
              );

    // Capability rows carry a tech list the section renders as chips.
    if (props.sectionKey === 'capabilities' && repeater.key !== 'guarantees') {
        blank.tech = [];
    }

    writeRows([...repeaterRows.value, blank]);
}

function removeRow(index: number) {
    const rows = [...repeaterRows.value];

    rows.splice(index, 1);
    writeRows(rows);
}

function close() {
    emit('update:open', false);
}

function save() {
    if (!props.sectionKey || !def.value) {
        return;
    }

    // Required-field gate before submit, so the round trip is not wasted on
    // an empty heading.
    const problems: Record<string, string> = {};

    for (const field of def.value.fields) {
        if (field.required && !String(form.value[field.key] ?? '').trim()) {
            problems[field.key] = `${field.label} is required.`;
        }
    }

    errors.value = problems;

    if (Object.keys(problems).length > 0) {
        return;
    }

    emit(
        'save',
        props.sectionKey,
        JSON.parse(JSON.stringify(form.value)) as RecordOf,
    );
    close();
}

/** Expose only what the template needs from the def, fully typed. */
type ExposedDef = {
    fields: FieldDef[];
    repeater?: RepeaterDef;
};

const sectionDef = computed<ExposedDef | null>(() =>
    def.value ? { fields: def.value.fields, repeater: def.value.repeater } : null,
);

const previewIcon = computed(() =>
    resolveIcon(
        (form.value.icon as string | undefined) ?? null,
    ),
);
</script>

<template>
    <Dialog
        :open="props.open"
        @update:open="(value) => emit('update:open', value)"
    >
        <DialogContent
            class="max-h-[90vh] max-w-2xl overflow-y-auto"
        >
            <DialogHeader>
                <DialogTitle>
                    {{ def?.title ?? 'Edit section' }}
                </DialogTitle>
                <DialogDescription v-if="def">
                    {{ def.description }}
                </DialogDescription>
            </DialogHeader>

            <div v-if="sectionDef" class="flex flex-col gap-5 py-2">
                <div
                    v-for="field in sectionDef.fields"
                    :key="field.key"
                    class="grid gap-2"
                >
                    <Label :for="`sc-${field.key}`">
                        {{ field.label }}
                        <span
                            v-if="field.required"
                            class="text-destructive"
                            aria-hidden="true"
                            >*</span
                        >
                    </Label>

                    <Textarea
                        v-if="field.long"
                        :id="`sc-${field.key}`"
                        :model-value="String(form[field.key] ?? '')"
                        rows="3"
                        :placeholder="field.placeholder"
                        @update:model-value="(value) => (form[field.key] = value)"
                    />
                    <Input
                        v-else
                        :id="`sc-${field.key}`"
                        :model-value="String(form[field.key] ?? '')"
                        :placeholder="field.placeholder"
                        @update:model-value="(value) => (form[field.key] = value)"
                    />

                    <InputError :message="errors[field.key]" />
                </div>

                <!-- Repeater rows -->
                <div v-if="sectionDef.repeater" class="grid gap-3">
                    <Label>{{ sectionDef.repeater.label }}</Label>

                    <div
                        v-for="(row, index) in repeaterRows"
                        :key="index"
                        class="relative rounded-lg border border-border/70 p-4"
                    >
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="absolute top-2 right-2 text-muted-foreground hover:text-destructive"
                            :aria-label="`Remove item ${index + 1}`"
                            @click="removeRow(index)"
                        >
                            <Trash2 class="size-4" />
                        </Button>

                        <!-- Object rows -->
                        <div class="grid gap-4 pr-8">
                            <div
                                v-for="field in sectionDef.repeater!.fields"
                                :key="field.key"
                                class="grid gap-2"
                            >
                                <Label>
                                    {{ field.label }}
                                    <span
                                        v-if="field.required"
                                        class="text-destructive"
                                        aria-hidden="true"
                                        >*</span
                                    >
                                </Label>

                                <!-- Live icon preview for icon fields -->
                                <div
                                    v-if="
                                        sectionDef.repeater!.iconField &&
                                        field.key === 'icon'
                                    "
                                    class="flex items-center gap-2"
                                >
                                    <span
                                        class="studio-icon grid size-9 shrink-0 place-items-center rounded-lg border border-border bg-muted/40"
                                    >
                                        <component
                                            :is="previewIcon"
                                            class="size-4"
                                        />
                                    </span>
                                    <Input
                                        :model-value="String(row[field.key] ?? '')"
                                        :placeholder="field.placeholder"
                                        class="flex-1"
                                        @update:model-value="
                                            (value) => (row[field.key] = value)
                                        "
                                    />
                                </div>

                                <Textarea
                                    v-else-if="field.long"
                                    :model-value="String(row[field.key] ?? '')"
                                    rows="3"
                                    :placeholder="field.placeholder"
                                    @update:model-value="
                                        (value) => (row[field.key] = value)
                                    "
                                />
                                <Input
                                    v-else
                                    :model-value="String(row[field.key] ?? '')"
                                    :placeholder="field.placeholder"
                                    @update:model-value="
                                        (value) => (row[field.key] = value)
                                    "
                                />

                                <InputError
                                    :message="errors[`${field.key}.${index}`]"
                                />
                            </div>
                        </div>
                    </div>

                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        class="w-fit"
                        @click="addRow"
                    >
                        <Plus class="size-4" />
                        {{ sectionDef.repeater.addLabel }}
                    </Button>
                </div>
            </div>

            <DialogFooter>
                <Button type="button" variant="outline" @click="close">
                    Cancel
                </Button>
                <Button type="button" @click="save"> Save section </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
