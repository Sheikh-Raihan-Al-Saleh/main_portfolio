<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ExternalLink, Eye, EyeOff, Plus, Settings2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import LandingPageController from '@/actions/App/Http/Controllers/Admin/LandingPageController';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import DeleteAction from '@/components/admin/DeleteAction.vue';
import FileField from '@/components/admin/FileField.vue';
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

        <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_360px]">
            <!-- Sections -->
            <section class="order-2 lg:order-1">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold">Sections</h2>

                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button size="sm" :disabled="!landingPage">
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

                <p
                    v-if="!landingPage"
                    class="rounded-lg border border-dashed border-border/70 p-8 text-center text-sm text-muted-foreground"
                >
                    Save the page details first, then start adding sections.
                </p>

                <p
                    v-else-if="!orderedSections.length"
                    class="rounded-lg border border-dashed border-border/70 p-8 text-center text-sm text-muted-foreground"
                >
                    No sections yet. Add a hero-supporting block to get started.
                </p>

                <ul v-else class="flex flex-col gap-2">
                    <li
                        v-for="(section, index) in orderedSections"
                        :key="section.id"
                        class="flex items-center gap-3 rounded-lg border border-border/70 bg-card p-3"
                    >
                        <ReorderButtons
                            :index="index"
                            :total="orderedSections.length"
                            :disabled="saving"
                            @move="move"
                        />

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">
                                {{
                                    section.heading || typeLabels[section.type]
                                }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ typeLabels[section.type] }}
                            </p>
                        </div>

                        <span
                            :title="section.is_visible ? 'Visible' : 'Hidden'"
                            class="text-muted-foreground"
                        >
                            <Eye v-if="section.is_visible" class="size-4" />
                            <EyeOff v-else class="size-4" />
                        </span>

                        <Button
                            variant="ghost"
                            size="icon"
                            :aria-label="`Edit ${typeLabels[section.type]} section`"
                            @click="editSection(section)"
                        >
                            <Settings2 class="size-4" />
                        </Button>

                        <DeleteAction
                            :url="`/admin/landing-sections/${section.id}`"
                            label="Delete section"
                            description="This removes the section and any images it uploaded."
                        />
                    </li>
                </ul>
            </section>

            <!-- Page details -->
            <aside class="order-1 lg:order-2">
                <h2 class="mb-4 text-lg font-semibold">Page details</h2>

                <Form
                    v-bind="LandingPageController.update.form(project.slug)"
                    class="flex flex-col gap-5"
                    v-slot="{ errors, processing }"
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
                            :default-value="landingPage?.subheadline ?? ''"
                        />
                        <InputError :message="errors.subheadline" />
                    </div>

                    <div class="grid gap-4 border-t border-border pt-4">
                        <div>
                            <h3 class="mb-4 text-sm font-semibold">
                                Hero Images
                            </h3>
                            <p class="mb-4 text-xs text-muted-foreground">
                                Upload up to 3 images that will rotate in the
                                hero section. Recommended: Desktop (1920x1080),
                                Tablet (768x576), Mobile (375x667)
                            </p>
                        </div>

                        <!-- Desktop/Laptop image -->
                        <FileField
                            name="hero_media_paths[0]"
                            label="1. Desktop/Laptop (recommended: 1920x1080)"
                            :current-url="heroPreview(0)"
                            accept="image/*"
                            hint="Shown on desktop devices. Uploading replaces the current image."
                            preview
                        />
                        <InputError :message="errors['hero_media_paths.0']" />

                        <!-- Tablet image -->
                        <FileField
                            name="hero_media_paths[1]"
                            label="2. Tablet (recommended: 768x576)"
                            :current-url="heroPreview(1)"
                            accept="image/*"
                            hint="Shown on tablet devices. Uploading replaces the current image."
                            preview
                        />
                        <InputError :message="errors['hero_media_paths.1']" />

                        <!-- Mobile/Phone image -->
                        <FileField
                            name="hero_media_paths[2]"
                            label="3. Mobile/Phone (recommended: 375x667)"
                            :current-url="heroPreview(2)"
                            accept="image/*"
                            hint="Shown on mobile devices. Uploading replaces the current image."
                            preview
                        />
                        <InputError :message="errors['hero_media_paths.2']" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="grid gap-2">
                            <Label for="primary_cta_label">Primary CTA</Label>
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
                            <Label for="primary_cta_url">URL</Label>
                            <Input
                                id="primary_cta_url"
                                name="primary_cta_url"
                                :default-value="
                                    landingPage?.primary_cta_url ?? ''
                                "
                            />
                        </div>
                    </div>
                    <InputError :message="errors.primary_cta_url" />

                    <div class="grid grid-cols-2 gap-3">
                        <div class="grid gap-2">
                            <Label for="secondary_cta_label"
                                >Secondary CTA</Label
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
                            <Label for="secondary_cta_url">URL</Label>
                            <Input
                                id="secondary_cta_url"
                                name="secondary_cta_url"
                                :default-value="
                                    landingPage?.secondary_cta_url ??
                                    '/#contact'
                                "
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
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
                    </div>

                    <div class="grid gap-2">
                        <Label for="seo_title">SEO title</Label>
                        <Input
                            id="seo_title"
                            name="seo_title"
                            :default-value="landingPage?.seo_title ?? ''"
                        />
                        <InputError :message="errors.seo_title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="seo_description">SEO description</Label>
                        <Textarea
                            id="seo_description"
                            name="seo_description"
                            rows="2"
                            :default-value="landingPage?.seo_description ?? ''"
                        />
                        <InputError :message="errors.seo_description" />
                    </div>

                    <FileField
                        name="og_image"
                        label="Social share image"
                        remove-name="remove_og_image"
                        :current-url="landingPage?.og_image_url"
                        accept="image/*"
                        preview
                    />

                    <div class="flex items-center gap-3">
                        <Switch
                            name="is_published"
                            :default-value="landingPage?.is_published ?? false"
                        />
                        <Label>Published</Label>
                    </div>

                    <Button type="submit" :disabled="processing" class="w-fit">
                        Save page details
                    </Button>
                </Form>
            </aside>
        </div>

        <SectionEditorDialog
            v-if="landingPage"
            v-model:open="editorOpen"
            :landing-page-id="landingPage.id"
            :section="editingSection"
            :type="newSectionType"
        />
    </div>
</template>
