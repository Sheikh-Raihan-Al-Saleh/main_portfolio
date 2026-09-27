<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import FileField from '@/components/admin/FileField.vue';
import TagInput from '@/components/admin/TagInput.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
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
</script>

<template>
    <Form
        v-bind="action"
        class="flex max-w-3xl flex-col gap-6"
        v-slot="{ errors, processing }"
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
            <Label for="slug">Slug</Label>
            <Input
                id="slug"
                name="slug"
                :default-value="project?.slug"
                placeholder="Leave blank to generate from the title"
            />
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
                Company work appears on the home page and in the projects
                archive. Personal work appears on the About page.
            </p>
            <InputError :message="errors.context" />
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

        <div class="grid gap-2">
            <Label for="description">Description</Label>
            <Textarea
                id="description"
                name="description"
                rows="10"
                :default-value="project?.description ?? ''"
                placeholder="The full write-up. Separate paragraphs with a blank line."
            />
            <p class="text-xs text-muted-foreground">
                Plain text. Blank lines become separate paragraphs on the public
                page.
            </p>
            <InputError :message="errors.description" />
        </div>

        <div class="grid gap-2">
            <Label>Tech stack</Label>
            <TagInput
                name="tech_stack"
                :model-value="project?.tech_stack"
                placeholder="Laravel, Vue, MySQL…"
            />
            <InputError :message="errors.tech_stack" />
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
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
        </div>

        <FileField
            name="cover_image"
            label="Cover image"
            remove-name="remove_cover_image"
            :current-url="project?.cover_image_url"
            accept="image/*"
            preview
            hint="JPG, PNG or WebP up to 4 MB. Shown on cards and the project page."
        />
        <InputError :message="errors.cover_image" />

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
            <p
                v-if="project?.gallery_urls.length"
                class="text-xs text-muted-foreground"
            >
                {{ project.gallery_urls.length }} image(s) already attached. New
                uploads are appended.
            </p>
            <InputError :message="errors.gallery" />
        </div>

        <div class="flex flex-col gap-4 rounded-lg border border-border p-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <Label for="is_published">Published</Label>
                    <p class="text-xs text-muted-foreground">
                        Unpublished projects are hidden from the public site.
                    </p>
                </div>
                <Switch
                    id="is_published"
                    name="is_published"
                    :default-value="project ? project.is_published : true"
                />
            </div>

            <div class="flex items-center justify-between gap-4">
                <div>
                    <Label for="is_featured">Featured</Label>
                    <p class="text-xs text-muted-foreground">
                        Featured projects get a badge on their card.
                    </p>
                </div>
                <Switch
                    id="is_featured"
                    name="is_featured"
                    :default-value="project?.is_featured ?? false"
                />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <Button type="submit" :disabled="processing">
                {{ processing ? 'Saving…' : submitLabel }}
            </Button>
            <Button as-child type="button" variant="ghost">
                <Link :href="projects.index()">Cancel</Link>
            </Button>
        </div>
    </Form>
</template>
