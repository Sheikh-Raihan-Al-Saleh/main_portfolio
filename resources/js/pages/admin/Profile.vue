<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import {
    BookOpen,
    ExternalLink,
    Heading1,
    ImageIcon,
    Languages,
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
import ErrorList from '@/components/admin/form/ErrorList.vue';
import FormSection from '@/components/admin/form/FormSection.vue';
import FormTabs from '@/components/admin/form/FormTabs.vue';
import type { FormTab } from '@/components/admin/form/types';
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
const footerHint =
    'Use tokens like {{name}}, {{email}} or {{github}} — a link whose token is empty is hidden.';

/** The profile form, one topic per tab. `fields` maps errors to their tab. */
const tabs: FormTab[] = [
    {
        id: 'identity',
        label: 'Identity',
        description: 'Name, headline, roles',
        icon: User,
        fields: ['name', 'headline', 'tagline', 'roles', 'available_for_work'],
    },
    {
        id: 'story',
        label: 'Story',
        description: 'Bio and founder message',
        icon: BookOpen,
        fields: ['bio', 'founder_message'],
    },
    {
        id: 'hero',
        label: 'Hero',
        description: 'The portfolio headline',
        icon: Heading1,
        fields: ['hero_title', 'hero_statement'],
    },
    {
        id: 'contact',
        label: 'Contact & socials',
        description: 'Email, phone, links',
        icon: Mail,
        fields: ['public_email', 'phone', 'location', 'socials'],
    },
    {
        id: 'media',
        label: 'Media',
        description: 'Avatar, logo, résumé',
        icon: ImageIcon,
        fields: [
            'avatar',
            'logo',
            'resume',
            'og_image',
            'remove_avatar',
            'remove_logo',
            'remove_resume',
            'remove_og_image',
        ],
    },
    {
        id: 'seo',
        label: 'SEO',
        description: 'Search & link previews',
        icon: Search,
        fields: ['meta_title', 'meta_description'],
    },
    {
        id: 'footer',
        label: 'Footer',
        description: 'Columns and legal links',
        icon: PanelBottom,
        fields: ['footer'],
    },
    {
        id: 'copy',
        label: 'Site copy',
        description: 'Headings and labels',
        icon: Languages,
        fields: ['content'],
    },
];

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
            class="mt-6"
            v-slot="{ errors, processing }"
        >
            <FormTabs
                :tabs="tabs"
                :errors="errors"
                :processing="processing"
                submit-label="Save profile"
            >
                <!-- ── Identity ── -->
                <template #tab-identity>
                    <FormSection
                        title="Who you are"
                        description="Your name and the one-line summary under it."
                        :columns="2"
                    >
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

                        <div class="grid gap-2 sm:col-span-2">
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

                        <div class="grid gap-2 sm:col-span-2">
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
                    </FormSection>

                    <FormSection
                        title="Availability"
                        description="Shown as a pulsing badge in the hero and in the footer."
                    >
                        <div
                            class="flex items-center justify-between gap-4 rounded-xl border border-border bg-muted/40 p-4"
                        >
                            <div>
                                <Label for="available_for_work"
                                    >Available for work</Label
                                >
                                <p class="text-xs text-muted-foreground">
                                    Shows the pulsing availability badge in the
                                    hero.
                                </p>
                            </div>
                            <Switch
                                id="available_for_work"
                                name="available_for_work"
                                :default-value="profile.available_for_work"
                            />
                        </div>
                    </FormSection>
                </template>

                <!-- ── Story ── -->
                <template #tab-story>
                    <FormSection
                        title="Bio"
                        description="Shown in the About section. Blank lines become separate paragraphs."
                    >
                        <div class="grid gap-2">
                            <Label for="bio" class="sr-only">Bio</Label>
                            <Textarea
                                id="bio"
                                name="bio"
                                rows="8"
                                :default-value="profile.bio ?? ''"
                            />
                            <InputError :message="errors.bio" />
                        </div>
                    </FormSection>

                    <FormSection
                        title="Founder's message"
                        description="A pull quote above your bio — where you explain why the company exists. Blank lines become separate paragraphs."
                    >
                        <div class="grid gap-2">
                            <Label for="founder_message" class="sr-only"
                                >Founder's message</Label
                            >
                            <Textarea
                                id="founder_message"
                                name="founder_message"
                                rows="6"
                                :default-value="profile.founder_message ?? ''"
                            />
                            <InputError :message="errors.founder_message" />
                        </div>
                    </FormSection>
                </template>

                <!-- ── Hero ── -->
                <template #tab-hero>
                    <FormSection
                        title="Hero heading"
                        description="The first thing visitors read on /founder."
                    >
                        <div class="grid gap-2">
                            <Label for="hero_title">Heading line</Label>
                            <Input
                                id="hero_title"
                                name="hero_title"
                                :default-value="profile.hero_title ?? ''"
                                :placeholder="heroTitlePlaceholder"
                            />
                            <p class="text-xs text-muted-foreground">
                                Use {{ nameToken }} to insert your name; your
                                name is highlighted in the accent colour.
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
                    </FormSection>
                </template>

                <!-- ── Contact ── -->
                <template #tab-contact>
                    <FormSection
                        title="Contact details"
                        description="Shown in the contact section and the footer."
                        :columns="2"
                    >
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
                    </FormSection>

                    <FormSection
                        title="Social links"
                        description="Full URLs. Leave a field empty to hide that icon."
                        :columns="2"
                    >
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
                    </FormSection>
                </template>

                <!-- ── Media ── -->
                <template #tab-media>
                    <FormSection
                        title="Images"
                        description="Your portrait, the website logo and the social share preview."
                        :columns="2"
                    >
                        <div class="grid gap-2">
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
                        </div>

                        <div class="grid gap-2">
                            <FileField
                                name="logo"
                                label="Website logo"
                                remove-name="remove_logo"
                                :current-url="profile.logo_url"
                                accept="image/*"
                                preview
                                hint="Shown in both sites' navbars and footers, and the admin sidebar. A wide transparent PNG works best, up to 2 MB."
                            />
                            <InputError :message="errors.logo" />
                        </div>

                        <div class="grid gap-2 sm:col-span-2">
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
                        </div>
                    </FormSection>

                    <FormSection
                        title="Résumé"
                        description="Served at /resume and linked from the header."
                    >
                        <div class="grid gap-2">
                            <FileField
                                name="resume"
                                label="Résumé (PDF)"
                                remove-name="remove_resume"
                                :current-url="profile.resume_url"
                                accept="application/pdf"
                                hint="PDF only, up to 8 MB."
                            />
                            <InputError :message="errors.resume" />
                        </div>
                    </FormSection>
                </template>

                <!-- ── SEO ── -->
                <template #tab-seo>
                    <FormSection
                        title="Search & sharing"
                        description="How /founder appears in search results and link previews."
                    >
                        <div class="grid gap-2">
                            <Label for="meta_title">Meta title</Label>
                            <Input
                                id="meta_title"
                                name="meta_title"
                                maxlength="70"
                                :default-value="profile.meta_title ?? ''"
                            />
                            <p class="text-xs text-muted-foreground">
                                Up to 70 characters.
                            </p>
                            <InputError :message="errors.meta_title" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="meta_description"
                                >Meta description</Label
                            >
                            <Textarea
                                id="meta_description"
                                name="meta_description"
                                rows="3"
                                maxlength="180"
                                :default-value="profile.meta_description ?? ''"
                            />
                            <p class="text-xs text-muted-foreground">
                                Up to 180 characters.
                            </p>
                            <InputError :message="errors.meta_description" />
                        </div>
                    </FormSection>
                </template>

                <!-- ── Footer ── -->
                <template #tab-footer>
                    <FormSection
                        title="Footer columns & legal links"
                        :description="footerHint"
                    >
                        <FooterEditor :footer="profile.footer" />
                        <ErrorList :errors="errors" prefix="footer." />
                    </FormSection>
                </template>

                <!-- ── Site copy ── -->
                <template #tab-copy>
                    <FormSection
                        title="Section headings & labels"
                        description="Every piece of fixed text on your portfolio, grouped by section."
                    >
                        <ContentEditor :content="profile.content" />
                        <ErrorList :errors="errors" prefix="content." />
                    </FormSection>
                </template>
            </FormTabs>
        </Form>
    </div>
</template>
