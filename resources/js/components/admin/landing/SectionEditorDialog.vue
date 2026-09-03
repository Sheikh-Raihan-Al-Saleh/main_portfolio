<script setup lang="ts">
import type { RequestPayload } from '@inertiajs/core';
import { router } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
import SectionFields from '@/components/admin/landing/SectionFields.vue';
import { sectionSchemas } from '@/components/admin/landing/sectionSchema';
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
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import type { LandingSection, LandingSectionType } from '@/types';

type Props = {
    open: boolean;
    landingPageId: number;
    /** Editing an existing section, or null when adding a new one. */
    section: LandingSection | null;
    /** Type for a new section. Ignored when `section` is provided. */
    type: LandingSectionType | null;
};

const props = defineProps<Props>();

const emit = defineEmits<{ 'update:open': [boolean]; saved: [] }>();

const activeType = computed<LandingSectionType | null>(
    () => props.section?.type ?? props.type,
);

const schema = computed(() =>
    activeType.value ? sectionSchemas[activeType.value] : null,
);

const form = reactive({
    eyebrow: '',
    heading: '',
    subheading: '',
    is_visible: true,
});

const data = ref<Record<string, unknown>>({});
const errors = ref<Record<string, string>>({});
const processing = ref(false);

/** Reload the form whenever the dialog opens on a different target. */
watch(
    () => [props.open, props.section?.id, props.type] as const,
    () => {
        if (!props.open || !schema.value) {
            return;
        }

        errors.value = {};

        form.eyebrow = props.section?.eyebrow ?? '';
        form.heading = props.section?.heading ?? '';
        form.subheading = props.section?.subheading ?? '';
        form.is_visible = props.section?.is_visible ?? true;

        // Deep clone so edits are discardable until saved.
        data.value = props.section
            ? structuredClone(toPlain(props.section.data))
            : structuredClone(schema.value.defaults);
    },
    { immediate: true },
);

/** Strip Vue proxies so structuredClone does not choke on them. */
function toPlain(value: unknown): Record<string, unknown> {
    return JSON.parse(JSON.stringify(value ?? {})) as Record<string, unknown>;
}

function close() {
    emit('update:open', false);
}

function submit() {
    if (!activeType.value) {
        return;
    }

    processing.value = true;
    errors.value = {};

    /**
     * `data` is a nested object. Inertia serialises it correctly, but its
     * RequestPayload type only describes flat, form-encodable values — hence
     * the cast at the boundary rather than flattening a perfectly good payload.
     */
    const payload = {
        ...form,
        data: data.value,
    } as unknown as RequestPayload;

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
            close();
        },
        onError: (received: Record<string, string>) => {
            errors.value = received;
        },
        onFinish: () => {
            processing.value = false;
        },
    };

    if (props.section) {
        router.put(
            `/admin/landing-sections/${props.section.id}`,
            payload,
            options,
        );

        return;
    }

    router.post(
        '/admin/landing-sections',
        {
            ...(payload as object),
            landing_page_id: props.landingPageId,
            type: activeType.value,
        } as unknown as RequestPayload,
        options,
    );
}
</script>

<template>
    <Dialog
        :open="props.open"
        @update:open="(value) => emit('update:open', value)"
    >
        <DialogContent class="max-h-[90vh] max-w-2xl overflow-y-auto">
            <DialogHeader>
                <DialogTitle>
                    {{ props.section ? 'Edit section' : 'Add section' }}
                </DialogTitle>
                <DialogDescription v-if="schema">
                    {{ schema.description }}
                </DialogDescription>
            </DialogHeader>

            <div v-if="schema" class="flex flex-col gap-5 py-2">
                <div class="grid gap-2">
                    <Label for="section-eyebrow">Eyebrow</Label>
                    <Input
                        id="section-eyebrow"
                        v-model="form.eyebrow"
                        placeholder="Small label above the heading"
                    />
                    <InputError :message="errors.eyebrow" />
                </div>

                <div class="grid gap-2">
                    <Label for="section-heading">Heading</Label>
                    <Input id="section-heading" v-model="form.heading" />
                    <p class="text-xs text-muted-foreground">
                        Sections with a heading appear in the page's jump
                        navigation.
                    </p>
                    <InputError :message="errors.heading" />
                </div>

                <div class="grid gap-2">
                    <Label for="section-subheading">Subheading</Label>
                    <Textarea
                        id="section-subheading"
                        v-model="form.subheading"
                        rows="2"
                    />
                    <InputError :message="errors.subheading" />
                </div>

                <hr class="border-border/60" />

                <SectionFields
                    :fields="schema.fields"
                    v-model="data"
                    :errors="errors"
                />

                <div class="flex items-center gap-3">
                    <Switch v-model="form.is_visible" />
                    <Label>Visible on the published page</Label>
                </div>
            </div>

            <DialogFooter>
                <Button type="button" variant="outline" @click="close"
                    >Cancel</Button
                >
                <Button type="button" :disabled="processing" @click="submit">
                    {{ props.section ? 'Save section' : 'Add section' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
