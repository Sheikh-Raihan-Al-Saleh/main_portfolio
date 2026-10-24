<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import {
    BookOpen,
    Building2,
    ExternalLink,
    Eye,
    EyeOff,
    ImageIcon,
    LayoutTemplate,
    Link2,
    Mail,
    PanelBottom,
    Plus,
    Search,
    Settings2,
    Sparkles,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import CompanyController from '@/actions/App/Http/Controllers/Admin/CompanyController';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import DeleteAction from '@/components/admin/DeleteAction.vue';
import FileField from '@/components/admin/FileField.vue';
import FooterEditor from '@/components/admin/FooterEditor.vue';
import ErrorList from '@/components/admin/form/ErrorList.vue';
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
import companyRoutes from '@/routes/admin/company';
import type {
    Company,
    LandingSection,
    LandingSectionType,
    LandingSectionTypeOption,
} from '@/types';

type Props = {
    company: Company;
    sections: LandingSection[];
    sectionTypes: LandingSectionTypeOption[];
};

const props = defineProps<Props>();

const nameToken = '{{name}}';
const footerHint =
    'Use tokens like {{name}}, {{email}} or {{github}} — a link whose token is empty is hidden.';

/** The page's two views: the company record, and the home page blocks. */
const view = ref<'details' | 'blocks'>('details');

const views = computed(() => [
    {
        id: 'details' as const,
        label: 'Company details',
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

/** The company form, one topic per tab. `fields` maps errors to their tab. */
const tabs: FormTab[] = [
    {
        id: 'basics',
        label: 'Basics',
        description: 'Name, headline, tagline',
        icon: Building2,
        fields: ['name', 'legal_name', 'headline', 'tagline', 'founded_year'],
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
            'website',
            'status_text',
            'accepting_projects',
        ],
    },
    {
        id: 'socials',
        label: 'Social links',
        description: 'GitHub, LinkedIn, X',
        icon: Link2,
        fields: ['socials'],
    },
    {
        id: 'media',
        label: 'Media',
        description: 'Logo and share image',
        icon: ImageIcon,
        fields: ['logo', 'og_image', 'remove_logo', 'remove_og_image'],
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
        breadcrumbs: [{ title: 'Company', href: companyRoutes.edit() }],
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
</script>

<template>
    <Head title="Company" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="Company"
            description="The studio's own pages: the home route, the About page and the work archive. Your personal portfolio is under Founder."
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
            aria-label="Company sections"
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

        <!-- ── Company details ── -->
        <div v-show="view === 'details'">
            <Form
                v-bind="CompanyController.update.form()"
                v-slot="{ errors, processing }"
            >
                <FormTabs
                    :tabs="tabs"
                    :errors="errors"
                    :processing="processing"
                    submit-label="Save company"
                >
                    <template #tab-basics>
                        <FormSection
                            title="Name & positioning"
                            description="How the studio introduces itself everywhere."
                            :columns="2"
                        >
                            <div class="grid gap-2">
                                <Label for="name">Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    required
                                    :default-value="company.name"
                                />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="legal_name">Legal name</Label>
                                <Input
                                    id="legal_name"
                                    name="legal_name"
                                    :default-value="company.legal_name ?? ''"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Used on invoices and legal pages. Optional.
                                </p>
                                <InputError :message="errors.legal_name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="headline">Headline</Label>
                                <Input
                                    id="headline"
                                    name="headline"
                                    :default-value="company.headline ?? ''"
                                    placeholder="Software studio"
                                />
                                <InputError :message="errors.headline" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="founded_year">Founded</Label>
                                <Input
                                    id="founded_year"
                                    name="founded_year"
                                    :default-value="company.founded_year ?? ''"
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
                                    :default-value="company.tagline ?? ''"
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
                                    :default-value="company.mission ?? ''"
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
                                    :default-value="company.bio ?? ''"
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
                                    :default-value="company.hero_eyebrow ?? ''"
                                    placeholder="Product studio"
                                />
                                <InputError :message="errors.hero_eyebrow" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="hero_title">Heading</Label>
                                <Input
                                    id="hero_title"
                                    name="hero_title"
                                    :default-value="company.hero_title ?? ''"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Use {{ nameToken }} to insert the company
                                    name. The last two words get the gradient.
                                </p>
                                <InputError :message="errors.hero_title" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="hero_statement">Statement</Label>
                                <Input
                                    id="hero_statement"
                                    name="hero_statement"
                                    :default-value="
                                        company.hero_statement ?? ''
                                    "
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
                                        company.primary_cta_label ?? ''
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
                                        company.primary_cta_url ?? ''
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
                                        company.secondary_cta_label ?? ''
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
                                        company.secondary_cta_url ?? ''
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
                                    :default-value="company.public_email ?? ''"
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
                                    :default-value="company.phone ?? ''"
                                />
                                <InputError :message="errors.phone" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="location">Location</Label>
                                <Input
                                    id="location"
                                    name="location"
                                    :default-value="company.location ?? ''"
                                    placeholder="Dhaka, Bangladesh"
                                />
                                <InputError :message="errors.location" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="website">Website</Label>
                                <Input
                                    id="website"
                                    name="website"
                                    type="url"
                                    :default-value="company.website ?? ''"
                                    placeholder="https://example.com"
                                />
                                <InputError :message="errors.website" />
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
                                    <Label for="accepting_projects">
                                        Accepting projects
                                    </Label>
                                    <p class="text-xs text-muted-foreground">
                                        Shows the pulsing badge in the hero and
                                        the footer.
                                    </p>
                                </div>
                                <Switch
                                    id="accepting_projects"
                                    name="accepting_projects"
                                    :default-value="company.accepting_projects"
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="status_text">Status text</Label>
                                <Input
                                    id="status_text"
                                    name="status_text"
                                    :default-value="company.status_text ?? ''"
                                    placeholder="Accepting new projects"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Shown next to the availability dot.
                                </p>
                                <InputError :message="errors.status_text" />
                            </div>
                        </FormSection>
                    </template>

                    <template #tab-socials>
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
                                    :default-value="
                                        company.socials?.github ?? ''
                                    "
                                    placeholder="https://github.com/org"
                                />
                                <InputError
                                    :message="errors['socials.github']"
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="socials-linkedin">LinkedIn</Label>
                                <Input
                                    id="socials-linkedin"
                                    name="socials[linkedin]"
                                    type="url"
                                    :default-value="
                                        company.socials?.linkedin ?? ''
                                    "
                                    placeholder="https://linkedin.com/company/org"
                                />
                                <InputError
                                    :message="errors['socials.linkedin']"
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="socials-x">X</Label>
                                <Input
                                    id="socials-x"
                                    name="socials[x]"
                                    type="url"
                                    :default-value="company.socials?.x ?? ''"
                                />
                                <InputError :message="errors['socials.x']" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="socials-website">Website</Label>
                                <Input
                                    id="socials-website"
                                    name="socials[website]"
                                    type="url"
                                    :default-value="
                                        company.socials?.website ?? ''
                                    "
                                />
                                <InputError
                                    :message="errors['socials.website']"
                                />
                            </div>
                        </FormSection>
                    </template>

                    <template #tab-media>
                        <FormSection
                            title="Images"
                            description="The studio logo and the link preview image."
                            :columns="2"
                        >
                            <div class="grid gap-2">
                                <FileField
                                    name="logo"
                                    label="Logo"
                                    remove-name="remove_logo"
                                    :current-url="company.logo_url"
                                    accept="image/*"
                                    preview
                                    hint="Fallback brand mark when no website logo is set under Founder."
                                />
                                <InputError :message="errors.logo" />
                            </div>

                            <div class="grid gap-2">
                                <FileField
                                    name="og_image"
                                    label="Social share image"
                                    remove-name="remove_og_image"
                                    :current-url="company.og_image_url"
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
                                    :default-value="company.meta_title ?? ''"
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
                                        company.meta_description ?? ''
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
                            <FooterEditor :footer="company.footer" />
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
                            under Home sections.
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
                        :aria-label="`Edit ${typeLabels[section.type]} block`"
                        @click="editSection(section)"
                    >
                        <Settings2 class="size-4" />
                        Edit
                    </Button>

                    <DeleteAction
                        :url="`/admin/landing-sections/${section.id}`"
                        label="Delete block"
                        description="This removes the block and any images it uploaded."
                    />
                </li>
            </ul>
        </section>

        <SectionEditorDialog
            v-model:open="editorOpen"
            :company-id="company.id"
            :section="editingSection"
            :type="newSectionType"
        />
    </div>
</template>
