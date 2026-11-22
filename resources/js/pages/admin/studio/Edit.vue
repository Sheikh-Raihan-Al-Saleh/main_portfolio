<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import {
    BadgeCheck,
    BookOpen,
    Building2,
    ExternalLink,
    ImageIcon,
    LayoutTemplate,
    Link2,
    Mail,
    PanelBottom,
    Plus,
    Search,
    Sparkles,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import StudioController from '@/actions/App/Http/Controllers/Studio/StudioController';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import FileField from '@/components/admin/FileField.vue';
import FooterEditor from '@/components/admin/FooterEditor.vue';
import ErrorList from '@/components/admin/form/ErrorList.vue';
import FormSection from '@/components/admin/form/FormSection.vue';
import FormTabs from '@/components/admin/form/FormTabs.vue';
import SectionEditorDialog from '@/components/admin/landing/SectionEditorDialog.vue';
import SectionListRow from '@/components/admin/landing/SectionListRow.vue';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import InputError from '@/components/common/InputError.vue';
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
import landingSectionRoutes from '@/routes/admin/landing-sections';
import studioRoutes from '@/routes/admin/studio';
import type { FormTab } from '@/types';
import type {
    LandingSection,
    LandingSectionType,
    SocialPlatform,
} from '@/types';
import type { AdminStudioEditProps } from '@/types';

const props = defineProps<AdminStudioEditProps>();

const nameToken = '{{name}}';
const footerHint =
    'Use tokens like {{name}}, {{email}} or {{github}} — a link whose token is empty is hidden.';

const socialFields: {
    key: SocialPlatform;
    label: string;
    placeholder: string;
}[] = [
    { key: 'github', label: 'GitHub', placeholder: 'https://github.com/org' },
    {
        key: 'linkedin',
        label: 'LinkedIn',
        placeholder: 'https://linkedin.com/company/org',
    },
    { key: 'x', label: 'X', placeholder: 'https://x.com/org' },
    {
        key: 'dribbble',
        label: 'Dribbble',
        placeholder: 'https://dribbble.com/org',
    },
    {
        key: 'youtube',
        label: 'YouTube',
        placeholder: 'https://youtube.com/@org',
    },
    {
        key: 'whatsapp',
        label: 'WhatsApp',
        placeholder: 'https://wa.me/8801...',
    },
    { key: 'website', label: 'Website', placeholder: 'https://example.com' },
];

/** The page's two views: the studio record, and the home page blocks. */
const view = ref<'details' | 'blocks'>('details');

const views = computed(() => [
    {
        id: 'details' as const,
        label: 'Studio details',
        icon: Building2,
        count: undefined,
    },
    {
        id: 'blocks' as const,
        label: 'Home page blocks',
        icon: LayoutTemplate,
        count: props.sections.length,
    },
]);

/** The studio form, one topic per tab. `fields` maps errors to their tab. */
const tabs: FormTab[] = [
    {
        id: 'brand',
        label: 'Brand',
        description: 'Name and logos',
        icon: BadgeCheck,
        fields: [
            'name',
            'short_name',
            'legal_name',
            'logo',
            'logo_dark',
            'logo_mark',
            'remove_logo',
            'remove_logo_dark',
            'remove_logo_mark',
        ],
    },
    {
        id: 'basics',
        label: 'Basics',
        description: 'Headline, tagline',
        icon: Building2,
        fields: ['headline', 'tagline', 'founded_year'],
    },
    {
        id: 'story',
        label: 'Story',
        description: 'Mission and bio',
        icon: BookOpen,
        fields: ['mission', 'bio'],
    },
    {
        id: 'hero',
        label: 'Hero',
        description: 'Heading and buttons',
        icon: Sparkles,
        fields: [
            'hero_eyebrow',
            'hero_title',
            'hero_statement',
            'primary_cta_label',
            'primary_cta_url',
            'secondary_cta_label',
            'secondary_cta_url',
        ],
    },
    {
        id: 'contact',
        label: 'Contact & status',
        description: 'Email, phone, availability',
        icon: Mail,
        fields: [
            'public_email',
            'phone',
            'location',
            'website_url',
            'is_accepting_projects',
        ],
    },
    {
        id: 'socials',
        label: 'Social links',
        description: 'GitHub, LinkedIn and more',
        icon: Link2,
        fields: ['socials'],
    },
    {
        id: 'media',
        label: 'Media',
        description: 'Share image',
        icon: ImageIcon,
        fields: ['og_image', 'remove_og_image'],
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
];

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Studio & brand', href: studioRoutes.edit() }],
    },
});

const {
    items: orderedSections,
    move,
    saving,
} = useReorder(() => props.sections, landingSectionRoutes.reorder().url);

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
</script>

<template>
    <Head title="Studio & brand" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="Studio & brand"
            description="The company behind the site. Its name and logo appear on every page: header, footer, login, this admin panel, the browser tab and emails."
        >
            <template #actions>
                <Button as-child variant="outline" size="sm">
                    <a href="/about" target="_blank" rel="noopener">
                        <ExternalLink class="size-4" />
                        View about
                    </a>
                </Button>
                <Button as-child variant="outline" size="sm">
                    <a href="/" target="_blank" rel="noopener">
                        <ExternalLink class="size-4" />
                        View home
                    </a>
                </Button>
            </template>
        </AdminPageHeader>

        <!-- Two jobs on one page: the company record, and the home page blocks -->
        <div
            role="tablist"
            aria-label="Studio sections"
            class="mb-6 inline-flex rounded-full border border-border bg-card p-1"
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

        <!-- ── Studio details ── -->
        <div v-show="view === 'details'">
            <Form
                v-bind="StudioController.update.form()"
                v-slot="{ errors, processing }"
            >
                <FormTabs
                    :tabs="tabs"
                    :errors="errors"
                    :processing="processing"
                    submit-label="Save studio"
                >
                    <template #tab-brand>
                        <FormSection
                            title="Company name"
                            description="Shown in the header, footer, login page, admin sidebar, page titles and emails."
                            :columns="2"
                        >
                            <div class="grid gap-2">
                                <Label for="name">Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    required
                                    :default-value="studio.name"
                                />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="short_name">Short name</Label>
                                <Input
                                    id="short_name"
                                    name="short_name"
                                    :default-value="studio.short_name ?? ''"
                                    placeholder="Acme"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Used where space is tight. Optional.
                                </p>
                                <InputError :message="errors.short_name" />
                            </div>

                            <div class="grid gap-2 sm:col-span-2">
                                <Label for="legal_name">Legal name</Label>
                                <Input
                                    id="legal_name"
                                    name="legal_name"
                                    :default-value="studio.legal_name ?? ''"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Used on invoices and legal pages. Optional.
                                </p>
                                <InputError :message="errors.legal_name" />
                            </div>
                        </FormSection>

                        <FormSection
                            title="Logos"
                            description="PNG, WebP or SVG, up to 2 MB. The logo is shown large across the site, so upload a crisp, high-resolution file."
                            :columns="3"
                        >
                            <div class="grid gap-2">
                                <FileField
                                    name="logo"
                                    label="Logo"
                                    remove-name="remove_logo"
                                    :current-url="studio.logo_url"
                                    accept="image/png,image/webp,image/svg+xml"
                                    preview
                                    hint="Full logo for light backgrounds."
                                />
                                <InputError :message="errors.logo" />
                            </div>

                            <div class="grid gap-2">
                                <FileField
                                    name="logo_dark"
                                    label="Logo on dark"
                                    remove-name="remove_logo_dark"
                                    :current-url="studio.logo_dark_url"
                                    accept="image/png,image/webp,image/svg+xml"
                                    preview
                                    hint="Optional version for dark backgrounds."
                                />
                                <InputError :message="errors.logo_dark" />
                            </div>

                            <div class="grid gap-2">
                                <FileField
                                    name="logo_mark"
                                    label="Logo mark"
                                    remove-name="remove_logo_mark"
                                    :current-url="studio.logo_mark_url"
                                    accept="image/png,image/webp,image/svg+xml"
                                    preview
                                    hint="Square icon. Also the browser tab icon."
                                />
                                <InputError :message="errors.logo_mark" />
                            </div>
                        </FormSection>

                        <FormSection
                            title="Preview"
                            description="How the saved brand renders at its real sizes."
                        >
                            <div
                                class="grid gap-6 rounded-xl border border-border bg-background p-6"
                            >
                                <div class="flex flex-wrap items-center gap-8">
                                    <BrandLogo size="lg" />
                                    <BrandLogo variant="mark" size="xl" />
                                </div>
                                <div class="rounded-lg bg-ink p-5">
                                    <BrandLogo size="lg" tone="dark" />
                                </div>
                            </div>
                        </FormSection>
                    </template>

                    <template #tab-basics>
                        <FormSection
                            title="Positioning"
                            description="How the studio introduces itself everywhere."
                            :columns="2"
                        >
                            <div class="grid gap-2">
                                <Label for="headline">Headline</Label>
                                <Input
                                    id="headline"
                                    name="headline"
                                    :default-value="studio.headline ?? ''"
                                    placeholder="Software studio"
                                />
                                <InputError :message="errors.headline" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="founded_year">Founded</Label>
                                <Input
                                    id="founded_year"
                                    name="founded_year"
                                    :default-value="studio.founded_year ?? ''"
                                    placeholder="2020"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Used to show "years in business".
                                </p>
                                <InputError :message="errors.founded_year" />
                            </div>

                            <div class="grid gap-2 sm:col-span-2">
                                <Label for="tagline">Tagline</Label>
                                <Textarea
                                    id="tagline"
                                    name="tagline"
                                    rows="2"
                                    :default-value="studio.tagline ?? ''"
                                />
                                <InputError :message="errors.tagline" />
                            </div>
                        </FormSection>
                    </template>

                    <template #tab-story>
                        <FormSection
                            title="Mission"
                            description="One sentence on why the company exists. Appended to the contact section intro."
                        >
                            <div class="grid gap-2">
                                <Label for="mission" class="sr-only"
                                    >Mission</Label
                                >
                                <Textarea
                                    id="mission"
                                    name="mission"
                                    rows="3"
                                    :default-value="studio.mission ?? ''"
                                />
                                <InputError :message="errors.mission" />
                            </div>
                        </FormSection>

                        <FormSection
                            title="Bio"
                            description="The studio story on the About page. Blank lines become separate paragraphs."
                        >
                            <div class="grid gap-2">
                                <Label for="bio" class="sr-only">Bio</Label>
                                <Textarea
                                    id="bio"
                                    name="bio"
                                    rows="7"
                                    :default-value="studio.bio ?? ''"
                                />
                                <InputError :message="errors.bio" />
                            </div>
                        </FormSection>
                    </template>

                    <template #tab-hero>
                        <FormSection
                            title="Hero copy"
                            description="The first screen of the home page."
                        >
                            <div class="grid gap-2">
                                <Label for="hero_eyebrow">Eyebrow</Label>
                                <Input
                                    id="hero_eyebrow"
                                    name="hero_eyebrow"
                                    :default-value="studio.hero_eyebrow ?? ''"
                                    placeholder="Product studio"
                                />
                                <InputError :message="errors.hero_eyebrow" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="hero_title">Heading</Label>
                                <Input
                                    id="hero_title"
                                    name="hero_title"
                                    :default-value="studio.hero_title ?? ''"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Use {{ nameToken }} to insert the company
                                    name. The last two words are set in the
                                    accent italic.
                                </p>
                                <InputError :message="errors.hero_title" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="hero_statement">Statement</Label>
                                <Input
                                    id="hero_statement"
                                    name="hero_statement"
                                    :default-value="studio.hero_statement ?? ''"
                                />
                                <p class="text-xs text-muted-foreground">
                                    The muted line that follows the heading.
                                </p>
                                <InputError :message="errors.hero_statement" />
                            </div>
                        </FormSection>

                        <FormSection
                            title="Buttons"
                            description="The two calls to action under the hero."
                            :columns="2"
                        >
                            <div class="grid gap-2">
                                <Label for="primary_cta_label"
                                    >Primary button</Label
                                >
                                <Input
                                    id="primary_cta_label"
                                    name="primary_cta_label"
                                    :default-value="
                                        studio.primary_cta_label ?? ''
                                    "
                                    placeholder="Start a project"
                                />
                                <InputError
                                    :message="errors.primary_cta_label"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="primary_cta_url">Links to</Label>
                                <Input
                                    id="primary_cta_url"
                                    name="primary_cta_url"
                                    :default-value="
                                        studio.primary_cta_url ?? ''
                                    "
                                    placeholder="#contact"
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
                                    :default-value="
                                        studio.secondary_cta_label ?? ''
                                    "
                                    placeholder="See our work"
                                />
                                <InputError
                                    :message="errors.secondary_cta_label"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="secondary_cta_url">Links to</Label>
                                <Input
                                    id="secondary_cta_url"
                                    name="secondary_cta_url"
                                    :default-value="
                                        studio.secondary_cta_url ?? ''
                                    "
                                    placeholder="#work"
                                />
                                <InputError
                                    :message="errors.secondary_cta_url"
                                />
                            </div>
                        </FormSection>
                    </template>

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
                                    :default-value="studio.public_email ?? ''"
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
                                    :default-value="studio.phone ?? ''"
                                />
                                <InputError :message="errors.phone" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="location">Location</Label>
                                <Input
                                    id="location"
                                    name="location"
                                    :default-value="studio.location ?? ''"
                                    placeholder="Dhaka, Bangladesh"
                                />
                                <InputError :message="errors.location" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="website_url">Website</Label>
                                <Input
                                    id="website_url"
                                    name="website_url"
                                    type="url"
                                    :default-value="studio.website_url ?? ''"
                                    placeholder="https://example.com"
                                />
                                <InputError :message="errors.website_url" />
                            </div>
                        </FormSection>

                        <FormSection
                            title="Availability"
                            description="The status dot in the hero, the CTA and the footer."
                        >
                            <div
                                class="flex items-center justify-between gap-4 rounded-xl border border-border bg-muted/40 p-4"
                            >
                                <div>
                                    <Label for="is_accepting_projects">
                                        Accepting projects
                                    </Label>
                                    <p class="text-xs text-muted-foreground">
                                        Shows the pulsing badge in the hero and
                                        the footer.
                                    </p>
                                </div>
                                <Switch
                                    id="is_accepting_projects"
                                    name="is_accepting_projects"
                                    :default-value="
                                        studio.is_accepting_projects
                                    "
                                />
                            </div>
                        </FormSection>
                    </template>

                    <template #tab-socials>
                        <FormSection
                            title="Social links"
                            description="Full URLs. Leave a field empty to hide that icon."
                            :columns="2"
                        >
                            <div
                                v-for="field in socialFields"
                                :key="field.key"
                                class="grid gap-2"
                            >
                                <Label :for="`socials-${field.key}`">{{
                                    field.label
                                }}</Label>
                                <Input
                                    :id="`socials-${field.key}`"
                                    :name="`socials[${field.key}]`"
                                    type="url"
                                    :default-value="socials[field.key] ?? ''"
                                    :placeholder="field.placeholder"
                                />
                                <InputError
                                    :message="errors[`socials.${field.key}`]"
                                />
                            </div>
                        </FormSection>
                    </template>

                    <template #tab-media>
                        <FormSection
                            title="Share image"
                            description="The image shown when a link to the site is shared."
                            :columns="2"
                        >
                            <div class="grid gap-2">
                                <FileField
                                    name="og_image"
                                    label="Social share image"
                                    remove-name="remove_og_image"
                                    :current-url="studio.og_image_url"
                                    accept="image/*"
                                    preview
                                    hint="Used as the Open Graph preview. 1200×630 recommended."
                                />
                                <InputError :message="errors.og_image" />
                            </div>
                        </FormSection>
                    </template>

                    <template #tab-seo>
                        <FormSection
                            title="Search & sharing"
                            description="How the home page appears in search results and link previews."
                        >
                            <div class="grid gap-2">
                                <Label for="meta_title">Meta title</Label>
                                <Input
                                    id="meta_title"
                                    name="meta_title"
                                    maxlength="70"
                                    :default-value="studio.meta_title ?? ''"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Up to 70 characters.
                                </p>
                                <InputError :message="errors.meta_title" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="meta_description">
                                    Meta description
                                </Label>
                                <Textarea
                                    id="meta_description"
                                    name="meta_description"
                                    rows="3"
                                    maxlength="180"
                                    :default-value="
                                        studio.meta_description ?? ''
                                    "
                                />
                                <p class="text-xs text-muted-foreground">
                                    Up to 180 characters.
                                </p>
                                <InputError
                                    :message="errors.meta_description"
                                />
                            </div>
                        </FormSection>
                    </template>

                    <template #tab-footer>
                        <FormSection
                            title="Footer columns & legal links"
                            :description="footerHint"
                        >
                            <FooterEditor :footer="studio.footer" />
                            <ErrorList :errors="errors" prefix="footer." />
                        </FormSection>
                    </template>
                </FormTabs>
            </Form>
        </div>

        <!-- ── Home page blocks ── -->
        <section v-show="view === 'blocks'" class="flex flex-col gap-5">
            <div
                class="ui-card flex flex-wrap items-center justify-between gap-4 p-5 sm:p-6"
            >
                <div class="flex items-start gap-3">
                    <span class="ui-icon-tile shrink-0"
                        ><LayoutTemplate class="size-5"
                    /></span>
                    <div>
                        <h2 class="admin-title text-lg font-bold">
                            Home page blocks
                        </h2>
                        <p class="max-w-2xl text-sm text-muted-foreground">
                            Composed between the founder-work preview and the
                            closing CTA. The hero and story sections are edited
                            under Home page copy.
                        </p>
                    </div>
                </div>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button>
                            <Plus class="size-4" />
                            Add block
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
                v-if="!orderedSections.length"
                class="ui-card flex flex-col items-center gap-3 border-dashed p-10 text-center"
            >
                <span class="ui-icon-tile"
                    ><LayoutTemplate class="size-5"
                /></span>
                <p class="font-semibold">No blocks yet</p>
                <p class="max-w-sm text-sm text-muted-foreground">
                    Add features, stats or an FAQ to fill out the home page.
                </p>
            </div>

            <ul v-else class="flex flex-col gap-3">
                <SectionListRow
                    v-for="(section, index) in orderedSections"
                    :key="section.id"
                    :section="section"
                    :index="index"
                    :total="orderedSections.length"
                    :type-label="typeLabels[section.type]"
                    :disabled="saving"
                    noun="block"
                    @move="move"
                    @edit="editSection(section)"
                />
            </ul>
        </section>

        <SectionEditorDialog
            v-model:open="editorOpen"
            owner="studio"
            :section="editingSection"
            :type="newSectionType"
        />
    </div>
</template>
