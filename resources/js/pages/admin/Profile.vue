<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import {
    ExternalLink,
    FileText,
    Heading1,
    Languages,
    Link2,
    Mail,
    PanelBottom,
    Search,
    User,
} from '@lucide/vue';
import SiteProfileController from '@/actions/App/Http/Controllers/Admin/SiteProfileController';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import ContentEditor from '@/components/admin/ContentEditor.vue';
import FileField from '@/components/admin/FileField.vue';
import FooterEditor from '@/components/admin/FooterEditor.vue';
import TagInput from '@/components/admin/TagInput.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import profileRoutes from '@/routes/admin/profile';
import settingsProfileRoutes from '@/routes/profile';
import type { Profile } from '@/types';

type Props = {
    profile: Profile;
};

defineProps<Props>();

const nameToken = '{{name}}';
const heroTitlePlaceholder = "Hi, I'm {{name}}.";

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Founder', href: profileRoutes.edit() }],
    },
});
</script>

<template>
    <Head title="Founder" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="Founder"
            description="Your personal portfolio at /founder, and the founder card shown on the studio's pages."
        >
            <template #actions>
                <Button as-child variant="outline" size="sm">
                    <a href="/founder" target="_blank" rel="noopener">
                        <ExternalLink class="size-4" />
                        View portfolio
                    </a>
                </Button>
            </template>
        </AdminPageHeader>

        <!-- This form edits the public founder record, not the login account. -->
        <p
            class="mt-4 flex max-w-3xl flex-wrap items-center gap-x-2 gap-y-1 rounded-lg border border-border bg-muted/40 px-4 py-3 text-sm text-muted-foreground"
        >
            <User class="size-4 shrink-0" aria-hidden="true" />
            <span>
                This is the public founder profile. Your sign-in name, email and
                password live in
            </span>
            <Link
                :href="settingsProfileRoutes.edit()"
                class="font-medium text-brand hover:underline"
            >
                account settings
            </Link>
            <span>.</span>
        </p>

        <Form
            v-bind="SiteProfileController.update.form()"
            class="flex max-w-3xl flex-col gap-8"
            v-slot="{ errors, processing }"
        >
            <section class="flex flex-col gap-6">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-blue-500/10 p-1.5">
                        <User class="size-4 text-blue-600 dark:text-blue-400" />
                    </span>
                    <h2 class="text-sm font-semibold tracking-wide uppercase">
                        Identity
                    </h2>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            required
                            :default-value="profile.name"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="headline">Headline</Label>
                        <Input
                            id="headline"
                            name="headline"
                            :default-value="profile.headline ?? ''"
                            placeholder="Software Engineer"
                        />
                        <InputError :message="errors.headline" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="tagline">Tagline</Label>
                    <Input
                        id="tagline"
                        name="tagline"
                        :default-value="profile.tagline ?? ''"
                        placeholder="I build fast, accessible web applications end to end."
                    />
                    <p class="text-xs text-muted-foreground">
                        The sentence under your name in the hero.
                    </p>
                    <InputError :message="errors.tagline" />
                </div>

                <div class="grid gap-2">
                    <Label>Rotating roles</Label>
                    <TagInput
                        name="roles"
                        :model-value="profile.roles"
                        placeholder="Software Engineer, Full-Stack Developer…"
                    />
                    <p class="text-xs text-muted-foreground">
                        Cycled in the hero headline. Add at least one.
                    </p>
                    <InputError :message="errors.roles" />
                </div>

                <div class="grid gap-2">
                    <Label for="bio">Bio</Label>
                    <Textarea
                        id="bio"
                        name="bio"
                        rows="8"
                        :default-value="profile.bio ?? ''"
                    />
                    <p class="text-xs text-muted-foreground">
                        Shown in the About section. Blank lines become separate
                        paragraphs.
                    </p>
                    <InputError :message="errors.bio" />
                </div>

                <div class="grid gap-2">
                    <Label for="founder_message">Founder's message</Label>
                    <Textarea
                        id="founder_message"
                        name="founder_message"
                        rows="6"
                        :default-value="profile.founder_message ?? ''"
                    />
                    <p class="text-xs text-muted-foreground">
                        Shown as a pull quote at the top of the About section,
                        above your bio. This is where you explain why the company
                        exists. Blank lines become separate paragraphs.
                    </p>
                    <InputError :message="errors.founder_message" />
                </div>

                <div
                    class="flex items-center justify-between gap-4 rounded-lg border border-border bg-gradient-to-r from-emerald-500/5 to-transparent p-4"
                >
                    <div>
                        <Label for="available_for_work"
                            >Available for work</Label
                        >
                        <p class="text-xs text-muted-foreground">
                            Shows the pulsing availability badge in the hero.
                        </p>
                    </div>
                    <Switch
                        id="available_for_work"
                        name="available_for_work"
                        :default-value="profile.available_for_work"
                    />
                </div>
            </section>

            <section class="flex flex-col gap-6 border-t pt-8">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-violet-500/10 p-1.5">
                        <Mail
                            class="size-4 text-violet-600 dark:text-violet-400"
                        />
                    </span>
                    <h2 class="text-sm font-semibold tracking-wide uppercase">
                        Contact
                    </h2>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="public_email">Public email</Label>
                        <Input
                            id="public_email"
                            name="public_email"
                            type="email"
                            :default-value="profile.public_email ?? ''"
                        />
                        <p class="text-xs text-muted-foreground">
                            Contact form notifications are sent here.
                        </p>
                        <InputError :message="errors.public_email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="phone">Phone</Label>
                        <Input
                            id="phone"
                            name="phone"
                            :default-value="profile.phone ?? ''"
                        />
                        <InputError :message="errors.phone" />
                    </div>

                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="location">Location</Label>
                        <Input
                            id="location"
                            name="location"
                            :default-value="profile.location ?? ''"
                            placeholder="Dhaka, Bangladesh"
                        />
                        <InputError :message="errors.location" />
                    </div>
                </div>
            </section>

            <section class="flex flex-col gap-6 border-t pt-8">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-cyan-500/10 p-1.5">
                        <Link2
                            class="size-4 text-cyan-600 dark:text-cyan-400"
                        />
                    </span>
                    <h2 class="text-sm font-semibold tracking-wide uppercase">
                        Social links
                    </h2>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="socials-github">GitHub</Label>
                        <Input
                            id="socials-github"
                            name="socials[github]"
                            type="url"
                            :default-value="profile.socials?.github ?? ''"
                            placeholder="https://github.com/username"
                        />
                        <InputError :message="errors['socials.github']" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="socials-linkedin">LinkedIn</Label>
                        <Input
                            id="socials-linkedin"
                            name="socials[linkedin]"
                            type="url"
                            :default-value="profile.socials?.linkedin ?? ''"
                            placeholder="https://linkedin.com/in/username"
                        />
                        <InputError :message="errors['socials.linkedin']" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="socials-x">X</Label>
                        <Input
                            id="socials-x"
                            name="socials[x]"
                            type="url"
                            :default-value="profile.socials?.x ?? ''"
                            placeholder="https://x.com/username"
                        />
                        <InputError :message="errors['socials.x']" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="socials-website">Website</Label>
                        <Input
                            id="socials-website"
                            name="socials[website]"
                            type="url"
                            :default-value="profile.socials?.website ?? ''"
                            placeholder="https://example.com"
                        />
                        <InputError :message="errors['socials.website']" />
                    </div>
                </div>
            </section>

            <section class="flex flex-col gap-6 border-t pt-8">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-amber-500/10 p-1.5">
                        <FileText
                            class="size-4 text-amber-600 dark:text-amber-400"
                        />
                    </span>
                    <h2 class="text-sm font-semibold tracking-wide uppercase">
                        Files
                    </h2>
                </div>

                <FileField
                    name="avatar"
                    label="Avatar"
                    remove-name="remove_avatar"
                    :current-url="profile.avatar_url"
                    accept="image/*"
                    preview
                    hint="Shown in the hero. Square images work best, up to 2 MB."
                />
                <InputError :message="errors.avatar" />

                <FileField
                    name="logo"
                    label="Website logo"
                    remove-name="remove_logo"
                    :current-url="profile.logo_url"
                    accept="image/*"
                    preview
                    hint="Shown in the studio and founder navbars and footers, and the admin sidebar. A wide transparent PNG works best, up to 2 MB."
                />
                <InputError :message="errors.logo" />

                <FileField
                    name="resume"
                    label="Résumé (PDF)"
                    remove-name="remove_resume"
                    :current-url="profile.resume_url"
                    accept="application/pdf"
                    hint="Served at /resume. PDF only, up to 8 MB."
                />
                <InputError :message="errors.resume" />

                <FileField
                    name="og_image"
                    label="Social share image"
                    remove-name="remove_og_image"
                    :current-url="profile.og_image_url"
                    accept="image/*"
                    preview
                    hint="Used as the Open Graph preview. 1200×630 recommended."
                />
                <InputError :message="errors.og_image" />
            </section>

            <section class="flex flex-col gap-6 border-t pt-8">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-amber-500/10 p-1.5">
                        <Heading1
                            class="size-4 text-amber-600 dark:text-amber-400"
                        />
                    </span>
                    <h2 class="text-sm font-semibold tracking-wide uppercase">
                        Hero heading
                    </h2>
                </div>

                <div class="grid gap-2">
                    <Label for="hero_title">Heading line</Label>
                    <Input
                        id="hero_title"
                        name="hero_title"
                        :default-value="profile.hero_title ?? ''"
                        :placeholder="heroTitlePlaceholder"
                    />
                    <p class="text-xs text-muted-foreground">
                        Use {{ nameToken }} to insert your name; the first name
                        is highlighted in brand color.
                    </p>
                    <InputError :message="errors.hero_title" />
                </div>

                <div class="grid gap-2">
                    <Label for="hero_statement">Statement</Label>
                    <Input
                        id="hero_statement"
                        name="hero_statement"
                        :default-value="profile.hero_statement ?? ''"
                        placeholder="I build things for the web."
                    />
                    <p class="text-xs text-muted-foreground">
                        The muted line that follows the heading.
                    </p>
                    <InputError :message="errors.hero_statement" />
                </div>
            </section>

            <section class="flex flex-col gap-6 border-t pt-8">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-emerald-500/10 p-1.5">
                        <Search
                            class="size-4 text-emerald-600 dark:text-emerald-400"
                        />
                    </span>
                    <h2 class="text-sm font-semibold tracking-wide uppercase">
                        SEO
                    </h2>
                </div>

                <div class="grid gap-2">
                    <Label for="meta_title">Meta title</Label>
                    <Input
                        id="meta_title"
                        name="meta_title"
                        maxlength="70"
                        :default-value="profile.meta_title ?? ''"
                    />
                    <InputError :message="errors.meta_title" />
                </div>

                <div class="grid gap-2">
                    <Label for="meta_description">Meta description</Label>
                    <Textarea
                        id="meta_description"
                        name="meta_description"
                        rows="3"
                        maxlength="180"
                        :default-value="profile.meta_description ?? ''"
                    />
                    <InputError :message="errors.meta_description" />
                </div>
            </section>

            <section class="flex flex-col gap-6 border-t pt-8">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-purple-500/10 p-1.5">
                        <PanelBottom
                            class="size-4 text-purple-600 dark:text-purple-400"
                        />
                    </span>
                    <h2 class="text-sm font-semibold tracking-wide uppercase">
                        Footer
                    </h2>
                </div>

                <FooterEditor :footer="profile.footer" />

                <div
                    v-if="
                        Object.entries(errors).some(([key]) =>
                            key.startsWith('footer.'),
                        )
                    "
                    class="rounded-lg border border-destructive/40 bg-destructive/5 p-3"
                >
                    <ul
                        class="list-inside list-disc space-y-1 text-xs text-destructive"
                    >
                        <li
                            v-for="[key, message] in Object.entries(
                                errors,
                            ).filter(([key]) => key.startsWith('footer.'))"
                            :key="key"
                        >
                            {{ message }}
                        </li>
                    </ul>
                </div>
            </section>

            <section class="flex flex-col gap-6 border-t pt-8">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-cyan-500/10 p-1.5">
                        <Languages
                            class="size-4 text-cyan-600 dark:text-cyan-400"
                        />
                    </span>
                    <h2 class="text-sm font-semibold tracking-wide uppercase">
                        Site copy
                    </h2>
                </div>

                <ContentEditor :content="profile.content" />

                <div
                    v-if="
                        Object.entries(errors).some(([key]) =>
                            key.startsWith('content.'),
                        )
                    "
                    class="rounded-lg border border-destructive/40 bg-destructive/5 p-3"
                >
                    <ul
                        class="list-inside list-disc space-y-1 text-xs text-destructive"
                    >
                        <li
                            v-for="[key, message] in Object.entries(
                                errors,
                            ).filter(([key]) => key.startsWith('content.'))"
                            :key="key"
                        >
                            {{ message }}
                        </li>
                    </ul>
                </div>
            </section>

            <div class="flex items-center gap-3 border-t pt-6">
                <Button
                    type="submit"
                    :disabled="processing"
                    class="bg-gradient-to-r from-brand to-brand-accent text-white hover:brightness-110"
                >
                    {{ processing ? 'Saving…' : 'Save profile' }}
                </Button>
            </div>
        </Form>
    </div>
</template>
