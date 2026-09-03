<script setup lang="ts">
import { markRaw, reactive } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { contentDefaults } from '@/lib/content';
import type { ContentConfig } from '@/types';

type Props = {
    content: ContentConfig | null;
};

const props = defineProps<Props>();

type SectionKey = keyof ContentConfig['sections'];

type TextField = {
    key: string;
    label: string;
    placeholder: string;
    component: typeof Input | typeof Textarea;
};

const stored = props.content ?? contentDefaults;

function pick<T extends Record<string, string>>(
    source: Partial<T> | undefined,
    defaults: T,
): T {
    return Object.fromEntries(
        Object.entries(defaults).map(([key, value]) => [
            key,
            source?.[key as keyof T] ?? value,
        ]),
    ) as T;
}

const sections: SectionKey[] = [
    'about',
    'skills',
    'projects',
    'experience',
    'contact',
];

const sectionFields: TextField[] = [
    {
        key: 'eyebrow',
        label: 'Eyebrow',
        placeholder: 'The mono label before the title',
        component: markRaw(Input),
    },
    {
        key: 'title',
        label: 'Title',
        placeholder: 'The big headline',
        component: markRaw(Input),
    },
    {
        key: 'highlight',
        label: 'Highlight',
        placeholder: 'Word drawn in brand color (a substring of the title)',
        component: markRaw(Input),
    },
    {
        key: 'description',
        label: 'Description',
        placeholder: 'Supporting paragraph under the title',
        component: markRaw(Textarea),
    },
];

const sectionsValue = reactive({
    about: pick(stored.sections?.about, contentDefaults.sections.about),
    skills: pick(stored.sections?.skills, contentDefaults.sections.skills),
    projects: pick(
        stored.sections?.projects,
        contentDefaults.sections.projects,
    ),
    experience: pick(
        stored.sections?.experience,
        contentDefaults.sections.experience,
    ),
    contact: pick(stored.sections?.contact, contentDefaults.sections.contact),
}) as Record<SectionKey, Record<string, string>>;

const heroFields: TextField[] = [
    {
        key: 'primary_cta_label',
        label: 'Primary CTA label',
        placeholder: 'View my work →',
        component: markRaw(Input),
    },
    {
        key: 'primary_cta_url',
        label: 'Primary CTA URL',
        placeholder: '#projects',
        component: markRaw(Input),
    },
    {
        key: 'secondary_cta_label',
        label: 'Secondary CTA label',
        placeholder: 'Get in touch',
        component: markRaw(Input),
    },
    {
        key: 'secondary_cta_url',
        label: 'Secondary CTA URL',
        placeholder: '#contact',
        component: markRaw(Input),
    },
    {
        key: 'resume_label',
        label: 'Résumé link label',
        placeholder: './resume.pdf',
        component: markRaw(Input),
    },
    {
        key: 'years_label',
        label: 'Stats — years suffix',
        placeholder: 'years',
        component: markRaw(Input),
    },
    {
        key: 'projects_label',
        label: 'Stats — projects suffix',
        placeholder: 'projects',
        component: markRaw(Input),
    },
    {
        key: 'skills_label',
        label: 'Stats — skills suffix',
        placeholder: 'skills',
        component: markRaw(Input),
    },
    {
        key: 'scroll_label',
        label: 'Scroll hint label',
        placeholder: 'scroll',
        component: markRaw(Input),
    },
];

const aboutFields: TextField[] = [
    {
        key: 'role_label',
        label: 'Role label',
        placeholder: 'role',
        component: markRaw(Input),
    },
    {
        key: 'location_label',
        label: 'Location label',
        placeholder: 'location',
        component: markRaw(Input),
    },
    {
        key: 'email_label',
        label: 'Email label',
        placeholder: 'email',
        component: markRaw(Input),
    },
    {
        key: 'phone_label',
        label: 'Phone label',
        placeholder: 'phone',
        component: markRaw(Input),
    },
    {
        key: 'available_open',
        label: 'Availability — when on',
        placeholder: 'Open to work — remote',
        component: markRaw(Input),
    },
    {
        key: 'available_closed',
        label: 'Availability — when off',
        placeholder: 'Selective availability',
        component: markRaw(Input),
    },
];

const toastFields: TextField[] = [
    {
        key: 'toast_title',
        label: 'Toast title',
        placeholder: 'Message sent',
        component: markRaw(Input),
    },
    {
        key: 'toast_description',
        label: 'Toast description',
        placeholder: "Thanks for reaching out! I'll get back to you soon.",
        component: markRaw(Input),
    },
];

const heroValue = reactive(pick(stored.hero, contentDefaults.hero)) as Record<
    string,
    string
>;
const aboutValue = reactive(
    pick(stored.about, contentDefaults.about),
) as Record<string, string>;
const toastValue = reactive(
    pick(stored.contact, contentDefaults.contact),
) as Record<string, string>;

const highlightHint =
    'Highlight must be an exact substring of the title to be colored.';
</script>

<template>
    <div class="flex flex-col gap-6">
        <p
            class="rounded-lg border border-dashed border-border bg-muted/40 p-3 text-xs leading-relaxed text-muted-foreground"
        >
            Every heading, button and label on the public site is driven from
            these fields. Leave one blank to restore the fallback copy.
        </p>

        <div class="flex flex-col gap-4">
            <div>
                <p class="text-sm font-semibold">Section headings</p>
                <p class="text-xs text-muted-foreground">
                    About, Skills, Projects, Experience and Contact.
                </p>
            </div>

            <div
                v-for="section in sections"
                :key="section"
                class="rounded-lg border border-border p-4"
            >
                <p class="mb-4 text-sm font-semibold capitalize">
                    {{ section }}
                </p>
                <div class="grid gap-4 md:grid-cols-2">
                    <div
                        v-for="field in sectionFields"
                        :key="field.key"
                        class="grid gap-2"
                    >
                        <Label :for="`content-section-${section}-${field.key}`">
                            {{ field.label }}
                        </Label>
                        <component
                            :is="field.component"
                            :id="`content-section-${section}-${field.key}`"
                            :name="`content[sections][${section}][${field.key}]`"
                            v-model="sectionsValue[section][field.key]"
                            :placeholder="field.placeholder"
                        />
                        <p
                            v-if="field.key === 'highlight'"
                            class="text-xs text-muted-foreground"
                        >
                            {{ highlightHint }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <div>
                <p class="text-sm font-semibold">Hero</p>
                <p class="text-xs text-muted-foreground">
                    Call-to-action buttons, stats suffixes and the scroll hint.
                </p>
            </div>

            <div
                class="grid gap-4 rounded-lg border border-border p-4 sm:grid-cols-2"
            >
                <div
                    v-for="field in heroFields"
                    :key="field.key"
                    class="grid gap-2"
                >
                    <Label :for="`content-hero-${field.key}`">
                        {{ field.label }}
                    </Label>
                    <component
                        :is="field.component"
                        :id="`content-hero-${field.key}`"
                        :name="`content[hero][${field.key}]`"
                        v-model="heroValue[field.key]"
                        :placeholder="field.placeholder"
                    />
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <div>
                <p class="text-sm font-semibold">About facts</p>
                <p class="text-xs text-muted-foreground">
                    Labels next to the role, location, email and phone facts
                    plus the availability chip.
                </p>
            </div>

            <div
                class="grid gap-4 rounded-lg border border-border p-4 sm:grid-cols-2"
            >
                <div
                    v-for="field in aboutFields"
                    :key="field.key"
                    class="grid gap-2"
                >
                    <Label :for="`content-about-${field.key}`">
                        {{ field.label }}
                    </Label>
                    <component
                        :is="field.component"
                        :id="`content-about-${field.key}`"
                        :name="`content[about][${field.key}]`"
                        v-model="aboutValue[field.key]"
                        :placeholder="field.placeholder"
                    />
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <div>
                <p class="text-sm font-semibold">Contact form toast</p>
                <p class="text-xs text-muted-foreground">
                    Shown after a message is submitted successfully.
                </p>
            </div>

            <div
                class="grid gap-4 rounded-lg border border-border p-4 md:grid-cols-2"
            >
                <div
                    v-for="field in toastFields"
                    :key="field.key"
                    class="grid gap-2"
                >
                    <Label :for="`content-toast-${field.key}`">
                        {{ field.label }}
                    </Label>
                    <component
                        :is="field.component"
                        :id="`content-toast-${field.key}`"
                        :name="`content[contact][${field.key}]`"
                        v-model="toastValue[field.key]"
                        :placeholder="field.placeholder"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
