<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { FounderContent } from '@/types';
import type { ContentEditorProps } from '@/types';

/**
 * Editor for the founder page's fixed copy (section headings and labels).
 *
 * The server sends the stored copy already merged over the defaults
 * (FounderDefaults::content()), so every field is present and pre-filled.
 * Fields post as content[...] alongside the rest of the founder form.
 */
defineProps<ContentEditorProps>();

const sections: { key: keyof FounderContent['sections']; label: string }[] = [
    { key: 'about', label: 'About' },
    { key: 'skills', label: 'Skills' },
    { key: 'projects', label: 'Projects' },
    { key: 'experience', label: 'Experience' },
    { key: 'contact', label: 'Contact' },
];

const sectionFields = [
    { key: 'eyebrow', label: 'Eyebrow', multiline: false },
    { key: 'title', label: 'Title', multiline: false },
    { key: 'highlight', label: 'Highlighted words', multiline: false },
    { key: 'description', label: 'Description', multiline: true },
] as const;

const heroFields: { key: keyof FounderContent['hero']; label: string }[] = [
    { key: 'resume_label', label: 'Resume button' },
    { key: 'years_label', label: 'Years label' },
    { key: 'projects_label', label: 'Projects label' },
    { key: 'skills_label', label: 'Skills label' },
];

const aboutFields: { key: keyof FounderContent['about']; label: string }[] = [
    { key: 'available_open', label: 'Available for work' },
    { key: 'available_closed', label: 'Not available' },
];
</script>

<template>
    <div class="flex flex-col gap-6">
        <p
            class="rounded-lg border border-dashed border-border bg-muted/40 p-3 text-xs leading-relaxed text-muted-foreground"
        >
            The highlighted words must appear in the title exactly; they are set
            in the accent italic.
        </p>

        <div
            v-for="section in sections"
            :key="section.key"
            class="rounded-lg border border-border p-4"
        >
            <p class="mb-4 text-sm font-semibold">{{ section.label }}</p>
            <div class="grid gap-4 md:grid-cols-2">
                <div
                    v-for="field in sectionFields"
                    :key="field.key"
                    class="grid gap-2"
                    :class="field.multiline && 'md:col-span-2'"
                >
                    <Label :for="`content-${section.key}-${field.key}`">{{
                        field.label
                    }}</Label>
                    <component
                        :is="field.multiline ? Textarea : Input"
                        :id="`content-${section.key}-${field.key}`"
                        :name="`content[sections][${section.key}][${field.key}]`"
                        :default-value="
                            content.sections[section.key][field.key]
                        "
                        :rows="field.multiline ? 2 : undefined"
                    />
                </div>
                <div v-if="section.key === 'about'" class="grid gap-2">
                    <Label for="content-about-message_label"
                        >Founder's note label</Label
                    >
                    <Input
                        id="content-about-message_label"
                        name="content[sections][about][message_label]"
                        :default-value="
                            content.sections.about.message_label ?? ''
                        "
                    />
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-border p-4">
            <p class="mb-4 text-sm font-semibold">Hero labels</p>
            <div class="grid gap-4 md:grid-cols-2">
                <div
                    v-for="field in heroFields"
                    :key="field.key"
                    class="grid gap-2"
                >
                    <Label :for="`content-hero-${field.key}`">{{
                        field.label
                    }}</Label>
                    <Input
                        :id="`content-hero-${field.key}`"
                        :name="`content[hero][${field.key}]`"
                        :default-value="content.hero[field.key]"
                    />
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-border p-4">
            <p class="mb-4 text-sm font-semibold">Availability badge</p>
            <div class="grid gap-4 md:grid-cols-2">
                <div
                    v-for="field in aboutFields"
                    :key="field.key"
                    class="grid gap-2"
                >
                    <Label :for="`content-about-${field.key}`">{{
                        field.label
                    }}</Label>
                    <Input
                        :id="`content-about-${field.key}`"
                        :name="`content[about][${field.key}]`"
                        :default-value="content.about[field.key]"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
