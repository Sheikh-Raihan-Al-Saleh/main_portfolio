<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { CalendarRange, FileText, ImageIcon, Rocket, Type } from '@lucide/vue';
import FileField from '@/components/admin/FileField.vue';
import FormSection from '@/components/admin/form/FormSection.vue';
import FormTabs from '@/components/admin/form/FormTabs.vue';
import type { FormTab } from '@/components/admin/form/types';
import TagInput from '@/components/admin/TagInput.vue';
import InputError from '@/components/InputError.vue';
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
import projects from '@/routes/admin/projects';
import type { Project } from '@/types';

type Props = {
    project?: Project;
    /** Wayfinder form props: `ProjectController.store.form()` or `.update.form(id)`. */
    action: Record<string, unknown>;
    submitLabel: string;
    /** `ProjectContext::options()`, for the context select. */
    contexts: { value: string; label: string }[];
};

const props = defineProps<Props>();

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
        fields: ['title', 'slug', 'context', 'summary'],
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
        fields: ['cover_image', 'remove_cover_image', 'gallery'],
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
            :cancel-href="projects.index().url"
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
                        <Label for="context">Appears on</Label>
                        <Select
                            name="context"
                            :default-value="props.project?.context ?? 'company'"
                        >
                            <SelectTrigger id="context">
                                <SelectValue placeholder="Pick a context" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="option in contexts"
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
                        <InputError :message="errors.context" />
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
                            :model-value="project?.tech_stack"
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
                        />
                        <div
                            v-if="project?.gallery_urls.length"
                            class="mt-2 flex flex-wrap gap-2"
                        >
                            <img
                                v-for="url in project.gallery_urls"
                                :key="url"
                                :src="url"
                                alt=""
                                class="h-16 w-24 rounded-lg border border-border object-cover"
                            />
                        </div>
                        <p
                            v-if="project?.gallery_urls.length"
                            class="text-xs text-muted-foreground"
                        >
                            {{ project.gallery_urls.length }} image(s) already
                            attached. New uploads are appended.
                        </p>
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
