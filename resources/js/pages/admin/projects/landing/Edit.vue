<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import {
    ExternalLink,
    Eye,
    EyeOff,
    ImageIcon,
    LayoutTemplate,
    MousePointerClick,
    Plus,
    Search,
    Settings2,
    Type,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import LandingPageController from '@/actions/App/Http/Controllers/Admin/LandingPageController';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import DeleteAction from '@/components/admin/DeleteAction.vue';
import FileField from '@/components/admin/FileField.vue';
import FormSection from '@/components/admin/form/FormSection.vue';
import FormTabs from '@/components/admin/form/FormTabs.vue';
import type { FormTab } from '@/components/admin/form/types';
import SectionEditorDialog from '@/components/admin/landing/SectionEditorDialog.vue';
import ReorderButtons from '@/components/admin/ReorderButtons.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { useReorder } from '@/composables/useReorder';
import { cn } from '@/lib/utils';
import projects from '@/routes/admin/projects';
import type {
    LandingSection,
    LandingSectionType,
    LandingSectionTypeOption,
    ProjectLandingPage,
} from '@/types';

type Props = {
    project: { id: number; title: string; slug: string };
    landingPage: ProjectLandingPage | null;
    sections: LandingSection[];
    sectionTypes: LandingSectionTypeOption[];
    previewUrl: string;
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Projects', href: projects.index() }],
    },
});

const {
    items: orderedSections,
    move,
    saving,
} = useReorder(() => props.sections, '/admin/landing-sections/reorder');

const editorOpen = ref(false);
const editingSection = ref<LandingSection | null>(null);
const newSectionType = ref<LandingSectionType | null>(null);

const typeLabels = computed(() =>
    Object.fromEntries(
        props.sectionTypes.map((option) => [option.value, option.label]),
    ),
);

function addSection(type: LandingSectionType) {
    editingSection.value = null;
    newSectionType.value = type;
    editorOpen.value = true;
}

function editSection(section: LandingSection) {
    newSectionType.value = null;
    editingSection.value = section;
    editorOpen.value = true;
}

/** Static /uploads/ URL for an already-stored hero slot, so saved server
 * images preview in the form instead of only freshly picked local files. */
/**
 * The page's two views. A page that has not been saved yet has no sections
 * to show, so it opens on its details.
 */
const view = ref<'details' | 'sections'>(
    props.landingPage ? 'sections' : 'details',
);

const views = computed(() => [
    {
        id: 'sections' as const,
        label: 'Sections',
        icon: LayoutTemplate,
        count: props.sections.length,
    },
    {
        id: 'details' as const,
        label: 'Page details',
        icon: Settings2,
        count: undefined,
    },
]);

/** The page-details form, one topic per tab. */
const tabs: FormTab[] = [
    {
        id: 'copy',
        label: 'Hero copy',
        description: 'Eyebrow and headline',
        icon: Type,
        fields: ['eyebrow', 'headline', 'subheadline'],
    },
    {
        id: 'images',
        label: 'Hero images',
        description: 'Device mockups',
        icon: ImageIcon,
        fields: ['hero_media_paths'],
    },
    {
        id: 'buttons',
        label: 'Buttons & style',
        description: 'CTAs and accent',
        icon: MousePointerClick,
        fields: [
            'primary_cta_label',
            'primary_cta_url',
            'secondary_cta_label',
            'secondary_cta_url',
            'accent_from',
            'accent_to',
        ],
    },
    {
        id: 'seo',
        label: 'SEO & publishing',
        description: 'Search, sharing, visibility',
        icon: Search,
        fields: [
            'seo_title',
            'seo_description',
            'og_image',
            'remove_og_image',
            'is_published',
        ],
    },
];

function heroPreview(index: number): string | null {
    const path = props.landingPage?.hero_media_paths?.[index];

    return path ? `/uploads/${path}` : null;
}
</script>

<template>
    <Head :title="`Landing page — ${project.title}`" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="Landing page"
            :description="`Marketing case study for ${project.title}`"
        >
            <template #actions>
                <Button
                    v-if="landingPage?.is_published"
                    as-child
                    variant="outline"
                    size="sm"
                >
                    <a :href="previewUrl" target="_blank" rel="noopener">
                        <ExternalLink class="size-4" />
                        Preview
                    </a>
                </Button>

                <DeleteAction
                    v-if="landingPage"
                    :url="`/admin/projects/${project.slug}/landing`"
                    label="Delete landing page"
                    description="This removes the landing page, all of its sections and their uploaded media. The project itself is kept."
                />
            </template>
        </AdminPageHeader>

        <!-- Two jobs on one page: the page itself, and its sections -->
        <div
            role="tablist"
            aria-label="Landing page"
            class="mb-6 inline-flex rounded-full border border-border bg-card p-1 shadow-[var(--shadow-soft)]"
        >
            <button
                v-for="option in views"
                :key="option.id"
                type="button"
                role="tab"
                :aria-selected="view === option.id"
                :class="
                    cn(
                        'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition-colors',
                        view === option.id
                            ? 'bg-primary text-primary-foreground shadow-sm'
                            : 'text-muted-foreground hover:text-foreground',
                    )
                "
                @click="view = option.id"
            >
                <component :is="option.icon" class="size-4" />
                {{ option.label }}
                <span
                    v-if="option.count !== undefined"
                    :class="
                        cn(
                            'rounded-full px-1.5 text-xs',
                            view === option.id
                                ? 'bg-white/20'
                                : 'bg-muted text-muted-foreground',
                        )
                    "
                    >{{ option.count }}</span
                >
            </button>
        </div>

        <!-- ── Page details ── -->
        <div v-show="view === 'details'">
            <Form
                v-bind="LandingPageController.update.form(project.slug)"
                v-slot="{ errors, processing }"
            >
                <FormTabs
                    :tabs="tabs"
                    :errors="errors"
                    :processing="processing"
                    submit-label="Save page details"
                >
                    <template #tab-copy>
                        <FormSection
                            title="Hero copy"
                            description="The headline block at the top of the case study."
                        >
                            <div class="grid gap-2">
                                <Label for="eyebrow">Eyebrow</Label>
                                <Input
                                    id="eyebrow"
                                    name="eyebrow"
                                    :default-value="
                                        landingPage?.eyebrow ?? 'Case study'
                                    "
                                />
                                <InputError :message="errors.eyebrow" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="headline">Headline</Label>
                                <Input
                                    id="headline"
                                    name="headline"
                                    :default-value="landingPage?.headline ?? ''"
                                    :placeholder="project.title"
                                />
                                <InputError :message="errors.headline" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="subheadline">Subheadline</Label>
                                <Textarea
                                    id="subheadline"
                                    name="subheadline"
                                    rows="3"
                                    :default-value="
                                        landingPage?.subheadline ?? ''
                                    "
                                />
                                <InputError :message="errors.subheadline" />
                            </div>
                        </FormSection>
                    </template>

                    <template #tab-images>
                        <FormSection
                            title="Device mockups"
                            description="Up to three screenshots that rotate through the laptop, tablet and phone frames in the hero."
                        >
                            <div class="grid gap-5 lg:grid-cols-3">
                                <div class="grid gap-2">
                                    <FileField
                                        name="hero_media_paths[0]"
                                        label="Desktop · 1920×1080"
                                        :current-url="heroPreview(0)"
                                        accept="image/*"
                                        hint="Uploading replaces the current image."
                                        preview
                                    />
                                    <InputError
                                        :message="errors['hero_media_paths.0']"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <FileField
                                        name="hero_media_paths[1]"
                                        label="Tablet · 768×576"
                                        :current-url="heroPreview(1)"
                                        accept="image/*"
                                        hint="Uploading replaces the current image."
                                        preview
                                    />
                                    <InputError
                                        :message="errors['hero_media_paths.1']"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <FileField
                                        name="hero_media_paths[2]"
                                        label="Phone · 375×667"
                                        :current-url="heroPreview(2)"
                                        accept="image/*"
                                        hint="Uploading replaces the current image."
                                        preview
                                    />
                                    <InputError
                                        :message="errors['hero_media_paths.2']"
                                    />
                                </div>
                            </div>
                        </FormSection>
                    </template>

                    <template #tab-buttons>
                        <FormSection
                            title="Calls to action"
                            description="The two buttons under the headline. Leave a URL empty to hide that button."
                            :columns="2"
                        >
                            <div class="grid gap-2">
                                <Label for="primary_cta_label"
                                    >Primary button</Label
                                >
                                <Input
                                    id="primary_cta_label"
                                    name="primary_cta_label"
                                    placeholder="See it live"
                                    :default-value="
                                        landingPage?.primary_cta_label ?? ''
                                    "
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="primary_cta_url">Links to</Label>
                                <Input
                                    id="primary_cta_url"
                                    name="primary_cta_url"
                                    :default-value="
                                        landingPage?.primary_cta_url ?? ''
                                    "
                                />
                                <InputError :message="errors.primary_cta_url" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="secondary_cta_label"
                                    >Secondary button</Label
                                >
                                <Input
                                    id="secondary_cta_label"
                                    name="secondary_cta_label"
                                    placeholder="Get in touch"
                                    :default-value="
                                        landingPage?.secondary_cta_label ?? ''
                                    "
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="secondary_cta_url">Links to</Label>
                                <Input
                                    id="secondary_cta_url"
                                    name="secondary_cta_url"
                                    :default-value="
                                        landingPage?.secondary_cta_url ??
                                        '/#contact'
                                    "
                                />
                            </div>
                        </FormSection>

                        <FormSection
                            title="Accent colours"
                            description="Tints this page's eyebrow and highlights. Optional — the site accent is used otherwise."
                            :columns="2"
                        >
                            <div class="grid gap-2">
                                <Label for="accent_from">Accent from</Label>
                                <Input
                                    id="accent_from"
                                    name="accent_from"
                                    type="color"
                                    class="h-10 p-1"
                                    :default-value="
                                        landingPage?.accent_from ?? '#6366f1'
                                    "
                                />
                                <InputError :message="errors.accent_from" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="accent_to">Accent to</Label>
                                <Input
                                    id="accent_to"
                                    name="accent_to"
                                    type="color"
                                    class="h-10 p-1"
                                    :default-value="
                                        landingPage?.accent_to ?? '#a855f7'
                                    "
                                />
                                <InputError :message="errors.accent_to" />
                            </div>
                        </FormSection>
                    </template>

                    <template #tab-seo>
                        <FormSection
                            title="Search & sharing"
                            description="How the case study appears in search results and link previews."
                        >
                            <div class="grid gap-2">
                                <Label for="seo_title">SEO title</Label>
                                <Input
                                    id="seo_title"
                                    name="seo_title"
                                    :default-value="
                                        landingPage?.seo_title ?? ''
                                    "
                                />
                                <InputError :message="errors.seo_title" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="seo_description"
                                    >SEO description</Label
                                >
                                <Textarea
                                    id="seo_description"
                                    name="seo_description"
                                    rows="2"
                                    :default-value="
                                        landingPage?.seo_description ?? ''
                                    "
                                />
                                <InputError :message="errors.seo_description" />
                            </div>

                            <div class="grid gap-2">
                                <FileField
                                    name="og_image"
                                    label="Social share image"
                                    remove-name="remove_og_image"
                                    :current-url="landingPage?.og_image_url"
                                    accept="image/*"
                                    preview
                                />
                                <InputError :message="errors.og_image" />
                            </div>
                        </FormSection>

                        <FormSection
                            title="Publishing"
                            description="Unpublished pages are only visible here."
                        >
                            <div
                                class="flex items-center justify-between gap-4 rounded-xl border border-border bg-muted/40 p-4"
                            >
                                <div>
                                    <Label for="is_published">Published</Label>
                                    <p class="text-xs text-muted-foreground">
                                        Makes the page live at its /p/ URL.
                                    </p>
                                </div>
                                <Switch
                                    id="is_published"
                                    name="is_published"
                                    :default-value="
                                        landingPage?.is_published ?? false
                                    "
                                />
                            </div>
                        </FormSection>
                    </template>
                </FormTabs>
            </Form>
        </div>

        <!-- ── Sections ── -->
        <section v-show="view === 'sections'" class="flex flex-col gap-5">
            <div
                class="ui-card flex flex-wrap items-center justify-between gap-4 p-5 sm:p-6"
            >
                <div class="flex items-start gap-3">
                    <span class="ui-icon-tile shrink-0"
                        ><LayoutTemplate class="size-5"
                    /></span>
                    <div>
                        <h2 class="admin-title text-lg font-bold">Sections</h2>
                        <p class="max-w-2xl text-sm text-muted-foreground">
                            The blocks under the hero, in order: features,
                            stats, demo, FAQ and more.
                        </p>
                    </div>
                </div>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button :disabled="!landingPage">
                            <Plus class="size-4" />
                            Add section
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem
                            v-for="option in sectionTypes"
                            :key="option.value"
                            @select="addSection(option.value)"
                        >
                            {{ option.label }}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            <div
                v-if="!landingPage || !orderedSections.length"
                class="ui-card flex flex-col items-center gap-3 border-dashed p-10 text-center"
            >
                <span class="ui-icon-tile"
                    ><LayoutTemplate class="size-5"
                /></span>
                <p class="font-semibold">
                    {{
                        landingPage ? 'No sections yet' : 'Save the page first'
                    }}
                </p>
                <p class="max-w-sm text-sm text-muted-foreground">
                    {{
                        landingPage
                            ? 'Add a block to build out the case study.'
                            : 'Fill in the page details and save, then start adding sections.'
                    }}
                </p>
                <Button
                    v-if="!landingPage"
                    variant="outline"
                    size="sm"
                    @click="view = 'details'"
                >
                    Go to page details
                </Button>
            </div>

            <ul v-else class="flex flex-col gap-3">
                <li
                    v-for="(section, index) in orderedSections"
                    :key="section.id"
                    class="ui-card ui-card-hover flex items-center gap-4 p-4"
                >
                    <ReorderButtons
                        :index="index"
                        :total="orderedSections.length"
                        :disabled="saving"
                        @move="move"
                    />

                    <span
                        class="grid size-9 shrink-0 place-items-center rounded-xl bg-muted text-xs font-semibold text-muted-foreground"
                        >{{ index + 1 }}</span
                    >

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">
                            {{ section.heading || typeLabels[section.type] }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ typeLabels[section.type] }}
                        </p>
                    </div>

                    <span
                        :class="
                            cn(
                                'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium',
                                section.is_visible
                                    ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                    : 'bg-muted text-muted-foreground',
                            )
                        "
                    >
                        <Eye v-if="section.is_visible" class="size-3.5" />
                        <EyeOff v-else class="size-3.5" />
                        {{ section.is_visible ? 'Visible' : 'Hidden' }}
                    </span>

                    <Button
                        variant="outline"
                        size="sm"
                        :aria-label="`Edit ${typeLabels[section.type]} section`"
                        @click="editSection(section)"
                    >
                        <Settings2 class="size-4" />
                        Edit
                    </Button>

                    <DeleteAction
                        :url="`/admin/landing-sections/${section.id}`"
                        label="Delete section"
                        description="This removes the section and any images it uploaded."
                    />
                </li>
            </ul>
        </section>

        <SectionEditorDialog
            v-if="landingPage"
            v-model:open="editorOpen"
            :landing-page-id="landingPage.id"
            :section="editingSection"
            :type="newSectionType"
        />
    </div>
</template>
