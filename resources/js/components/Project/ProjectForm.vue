<script setup lang="ts">
import { Form, usePage } from '@inertiajs/vue3';
import { CalendarRange, FileText, ImageIcon, Rocket, Type } from '@lucide/vue';
import { computed, onBeforeUnmount, ref } from 'vue';
import FileField from '@/components/FileField.vue';
import FormSection from '@/components/Form/FormSection.vue';
import FormTabs from '@/components/Form/FormTabs.vue';
import InputError from '@/components/InputError.vue';
import MediaPreview from '@/components/MediaPreview.vue';
import TagInput from '@/components/TagInput.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { oversizedFileMessage } from '@/composables/media';
import { route } from '@/composables/useRoute';
import type { FormTab } from '@/types';
import type { ProjectFormProps } from '@/types';

const props = withDefaults(defineProps<ProjectFormProps>(), {
    project: undefined,
});

/** Stored gallery images (edit only); ticking one removes it on save. */
const galleryItems = computed(() => props.project?.images ?? []);

const page = usePage();
const gallerySizeError = ref<string | null>(null);

/** Blob URLs of the newly picked gallery images, previewed before saving. */
const newGalleryPreviews = ref<Array<{ url: string; name: string }>>([]);

function releaseGalleryPreviews(): void {
    newGalleryPreviews.value.forEach((preview) =>
        URL.revokeObjectURL(preview.url),
    );
    newGalleryPreviews.value = [];
}

/**
 * Preview the picked gallery images, and reject picks the server would drop
 * so they never submit.
 */
function handleGalleryChange(event: Event) {
    const input = event.target as HTMLInputElement;

    releaseGalleryPreviews();

    gallerySizeError.value = oversizedFileMessage(
        input.files,
        page.props.uploadLimits,
    );

    if (gallerySizeError.value) {
        input.value = '';

        return;
    }

    newGalleryPreviews.value = Array.from(input.files ?? []).map((file) => ({
        url: URL.createObjectURL(file),
        name: file.name,
    }));
}

onBeforeUnmount(releaseGalleryPreviews);

function dateValue(value: string | null | undefined): string {
    return value ? value.slice(0, 10) : '';
}

/** One topic per tab. `fields` maps validation errors to their tab. */
const tabs: FormTab[] = [
    {
        id: 'basics',
        label: 'Basics',
        description: 'Title, where it appears',
        icon: Type,
        fields: ['title', 'slug', 'owner', 'summary'],
    },
    {
        id: 'story',
        label: 'Story',
        description: 'Write-up and stack',
        icon: FileText,
        fields: ['description', 'tech_stack', 'role'],
    },
    {
        id: 'links',
        label: 'Links & dates',
        description: 'Live site, repo, timeline',
        icon: CalendarRange,
        fields: ['repo_url', 'live_url', 'started_at', 'completed_at'],
    },
    {
        id: 'media',
        label: 'Media',
        description: 'Cover and gallery',
        icon: ImageIcon,
        fields: [
            'cover_image',
            'remove_cover_image',
            'gallery',
            'remove_gallery',
        ],
    },
    {
        id: 'publish',
        label: 'Publishing',
        description: 'Visibility and badges',
        icon: Rocket,
        fields: ['is_published', 'is_featured'],
    },
];
</script>

<template>
    <Form v-bind="action" v-slot="{ errors, processing }">
        <FormTabs
            :tabs="tabs"
            :errors="errors"
            :processing="processing"
            :submit-label="submitLabel"
            :cancel-href="route('projectIndex')"
        >
            <template #tab-basics>
                <FormSection
                    title="Project"
                    description="The name and the one-liner shown on the project card."
                >
                    <div class="grid gap-2">
                        <Label for="title">Title</Label>
                        <Input
                            id="title"
                            name="title"
                            required
                            :default-value="project?.title"
                            placeholder="Realtime Analytics Dashboard"
                        />
                        <InputError :message="errors.title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="summary">Summary</Label>
                        <Textarea
                            id="summary"
                            name="summary"
                            rows="2"
                            :default-value="project?.summary ?? ''"
                            placeholder="One or two sentences shown on the project card."
                        />
                        <InputError :message="errors.summary" />
                    </div>
                </FormSection>

                <FormSection
                    title="Where it lives"
                    description="Its URL, and which of the two sites shows it."
                    :columns="2"
                >
                    <div class="grid gap-2">
                        <Label for="slug">Slug</Label>
                        <Input
                            id="slug"
                            name="slug"
                            :default-value="project?.slug"
                            placeholder="Generated from the title"
                        />
                        <p class="text-xs text-muted-foreground">
                            /projects/<span class="font-mono">{{
                                project?.slug ?? 'your-slug'
                            }}</span>
                        </p>
                        <InputError :message="errors.slug" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="owner">Belongs to</Label>
                        <Select
                            name="owner"
                            :default-value="
                                props.project?.owner_type ?? 'studio'
                            "
                        >
                            <SelectTrigger id="owner">
                                <SelectValue placeholder="Studio or founder" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="option in owners"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p class="text-xs text-muted-foreground">
                            Company work: home page and archive. Personal work:
                            your portfolio.
                        </p>
                        <InputError :message="errors.owner" />
                    </div>
                </FormSection>
            </template>

            <template #tab-story>
                <FormSection
                    title="Write-up"
                    description="Plain text. Blank lines become separate paragraphs on the public page."
                >
                    <div class="grid gap-2">
                        <Label for="description" class="sr-only"
                            >Description</Label
                        >
                        <Textarea
                            id="description"
                            name="description"
                            rows="10"
                            :default-value="project?.description ?? ''"
                            placeholder="The full write-up. Separate paragraphs with a blank line."
                        />
                        <InputError :message="errors.description" />
                    </div>
                </FormSection>

                <FormSection
                    title="Stack & role"
                    description="What it was built with, and what you did."
                >
                    <div class="grid gap-2">
                        <Label>Tech stack</Label>
                        <TagInput
                            name="tech_stack"
                            :model-value="
                                project?.technologies.map(
                                    (technology) => technology.name,
                                )
                            "
                            :suggestions="technologyOptions"
                            placeholder="Laravel, Vue, MySQL…"
                        />
                        <InputError :message="errors.tech_stack" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="role">Your role</Label>
                        <Input
                            id="role"
                            name="role"
                            :default-value="project?.role ?? ''"
                            placeholder="Lead Developer"
                        />
                        <InputError :message="errors.role" />
                    </div>
                </FormSection>
            </template>

            <template #tab-links>
                <FormSection
                    title="Links"
                    description="Shown as buttons on the project page. Leave empty to hide."
                    :columns="2"
                >
                    <div class="grid gap-2">
                        <Label for="live_url">Live URL</Label>
                        <Input
                            id="live_url"
                            name="live_url"
                            type="url"
                            :default-value="project?.live_url ?? ''"
                            placeholder="https://…"
                        />
                        <InputError :message="errors.live_url" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="repo_url">Repository URL</Label>
                        <Input
                            id="repo_url"
                            name="repo_url"
                            type="url"
                            :default-value="project?.repo_url ?? ''"
                            placeholder="https://github.com/…"
                        />
                        <InputError :message="errors.repo_url" />
                    </div>
                </FormSection>

                <FormSection
                    title="Timeline"
                    description="Leave Completed empty for ongoing work."
                    :columns="2"
                >
                    <div class="grid gap-2">
                        <Label for="started_at">Started</Label>
                        <Input
                            id="started_at"
                            name="started_at"
                            type="date"
                            :default-value="dateValue(project?.started_at)"
                        />
                        <InputError :message="errors.started_at" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="completed_at">Completed</Label>
                        <Input
                            id="completed_at"
                            name="completed_at"
                            type="date"
                            :default-value="dateValue(project?.completed_at)"
                        />
                        <InputError :message="errors.completed_at" />
                    </div>
                </FormSection>
            </template>

            <template #tab-media>
                <FormSection
                    title="Cover image"
                    description="Shown on cards and at the top of the project page."
                >
                    <div class="grid gap-2">
                        <FileField
                            name="cover_image"
                            label="Cover image"
                            remove-name="remove_cover_image"
                            :current-url="project?.cover_image_url"
                            accept="image/*"
                            preview
                            hint="JPG, PNG or WebP up to 4 MB."
                        />
                        <InputError :message="errors.cover_image" />
                    </div>
                </FormSection>

                <FormSection
                    title="Gallery"
                    description="Screenshots shown below the write-up."
                >
                    <div class="grid gap-2">
                        <Label for="gallery">Add gallery images</Label>
                        <Input
                            id="gallery"
                            name="gallery[]"
                            type="file"
                            accept="image/*"
                            multiple
                            class="file:mr-3 file:text-sm file:text-foreground"
                            @change="handleGalleryChange"
                        />
                        <p
                            v-if="gallerySizeError"
                            class="text-xs text-destructive"
                        >
                            {{ gallerySizeError }}
                        </p>

                        <div
                            v-if="newGalleryPreviews.length"
                            class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"
                        >
                            <figure
                                v-for="preview in newGalleryPreviews"
                                :key="preview.url"
                                class="grid gap-1"
                            >
                                <div class="aspect-[4/3]">
                                    <MediaPreview
                                        :src="preview.url"
                                        :alt="preview.name"
                                        fill
                                    />
                                </div>
                                <figcaption
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    New: {{ preview.name }}
                                </figcaption>
                            </figure>
                        </div>

                        <div
                            v-if="galleryItems.length"
                            class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"
                        >
                            <label
                                v-for="item in galleryItems"
                                :key="item.id"
                                class="group relative block aspect-[4/3] cursor-pointer"
                                :title="`Remove ${item.alt ?? 'image'}`"
                            >
                                <MediaPreview
                                    :src="item.url ?? ''"
                                    :alt="item.alt ?? ''"
                                    fill
                                    class="transition group-has-[:checked]:opacity-30"
                                />
                                <input
                                    type="checkbox"
                                    name="remove_gallery[]"
                                    :value="item.id"
                                    class="absolute top-2 right-2 size-4 accent-destructive"
                                    :aria-label="`Remove gallery image ${item.id}`"
                                />
                            </label>
                        </div>
                        <p
                            v-if="galleryItems.length"
                            class="text-xs text-muted-foreground"
                        >
                            {{ galleryItems.length }} image(s) attached. Tick an
                            image to remove it on save; new uploads are
                            appended.
                        </p>
                        <InputError :message="errors.remove_gallery" />
                        <InputError :message="errors.gallery" />
                    </div>
                </FormSection>
            </template>

            <template #tab-publish>
                <FormSection
                    title="Visibility"
                    description="Control whether and how the project shows on the site."
                >
                    <div
                        class="flex items-center justify-between gap-4 rounded-xl border border-border bg-muted/40 p-4"
                    >
                        <div>
                            <Label for="is_published">Published</Label>
                            <p class="text-xs text-muted-foreground">
                                Unpublished projects are hidden from the public
                                site.
                            </p>
                        </div>
                        <Switch
                            id="is_published"
                            name="is_published"
                            :default-value="
                                project ? project.is_published : true
                            "
                        />
                    </div>

                    <div
                        class="flex items-center justify-between gap-4 rounded-xl border border-border bg-muted/40 p-4"
                    >
                        <div>
                            <Label for="is_featured">Featured</Label>
                            <p class="text-xs text-muted-foreground">
                                Featured projects get a badge, and the first one
                                opens the work section as a case study.
                            </p>
                        </div>
                        <Switch
                            id="is_featured"
                            name="is_featured"
                            :default-value="project?.is_featured ?? false"
                        />
                    </div>
                </FormSection>
            </template>
        </FormTabs>
    </Form>
</template>
