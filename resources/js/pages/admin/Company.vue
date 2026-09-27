<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import {
    Building2,
    ExternalLink,
    FileText,
    LayoutTemplate,
    Link2,
    Mail,
    Plus,
    Search,
    Settings2,
    Eye,
    EyeOff,
    Sparkles,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import CompanyController from '@/actions/App/Http/Controllers/Admin/CompanyController';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import DeleteAction from '@/components/admin/DeleteAction.vue';
import FileField from '@/components/admin/FileField.vue';
import FooterEditor from '@/components/admin/FooterEditor.vue';
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

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Company', href: companyRoutes.edit() }],
    },
});

const { items: orderedSections, move, saving } = useReorder(
    () => props.sections,
    '/admin/landing-sections/reorder',
);

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

        <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_360px]">
            <!-- Home page sections -->
            <section class="order-2 lg:order-1">
                <div class="mb-1 flex items-center gap-2">
                    <LayoutTemplate
                        class="size-4 text-muted-foreground"
                    />
                    <h2 class="text-lg font-semibold">Home page blocks</h2>
                </div>
                <p
                    class="mb-4 text-sm text-muted-foreground"
                >
                    Composed between the work grid and the contact form. The
                    hero is always the company record itself, and the work grid
                    shows published projects filed as company work.
                </p>

                <div class="mb-4 flex items-center justify-end gap-4">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button size="sm">
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

                <p
                    v-if="!orderedSections.length"
                    class="rounded-lg border border-dashed border-border/70 p-8 text-center text-sm text-muted-foreground"
                >
                    No blocks yet. Add features, stats or an FAQ to fill out the
                    page.
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
                                {{ section.heading || typeLabels[section.type] }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ typeLabels[section.type] }}
                            </p>
                        </div>

                        <span
                            :title="
                                section.is_visible ? 'Visible' : 'Hidden'
                            "
                            class="text-muted-foreground"
                        >
                            <Eye v-if="section.is_visible" class="size-4" />
                            <EyeOff v-else class="size-4" />
                        </span>

                        <Button
                            variant="ghost"
                            size="icon"
                            :aria-label="`Edit ${typeLabels[section.type]} block`"
                            @click="editSection(section)"
                        >
                            <Settings2 class="size-4" />
                        </Button>

                        <DeleteAction
                            :url="`/admin/landing-sections/${section.id}`"
                            label="Delete block"
                            description="This removes the block and any images it uploaded."
                        />
                    </li>
                </ul>
            </section>

            <!-- Company details -->
            <aside class="order-1 lg:order-2">
                <Form
                    v-bind="CompanyController.update.form()"
                    class="flex flex-col gap-5"
                    v-slot="{ errors, processing }"
                >
                    <h2 class="text-lg font-semibold">Company details</h2>

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
                        <Label for="tagline">Tagline</Label>
                        <Textarea
                            id="tagline"
                            name="tagline"
                            rows="2"
                            :default-value="company.tagline ?? ''"
                        />
                        <InputError :message="errors.tagline" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="mission">Mission</Label>
                        <Textarea
                            id="mission"
                            name="mission"
                            rows="3"
                            :default-value="company.mission ?? ''"
                        />
                        <p class="text-xs text-muted-foreground">
                            One sentence on why the company exists. Appended to
                            the contact section intro.
                        </p>
                        <InputError :message="errors.mission" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="bio">Bio</Label>
                        <Textarea
                            id="bio"
                            name="bio"
                            rows="5"
                            :default-value="company.bio ?? ''"
                        />
                        <InputError :message="errors.bio" />
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
                            Used to show "years in business" in the hero.
                        </p>
                        <InputError :message="errors.founded_year" />
                    </div>

                    <hr class="border-border/60" />

                    <div class="flex items-center gap-2">
                        <Mail class="size-4 text-muted-foreground" />
                        <h3 class="text-sm font-semibold tracking-wide uppercase">
                            Contact
                        </h3>
                    </div>

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

                    <div
                        class="flex items-center justify-between gap-4 rounded-lg border border-border bg-gradient-to-r from-emerald-500/5 to-transparent p-4"
                    >
                        <div>
                            <Label for="accepting_projects">
                                Accepting projects
                            </Label>
                            <p class="text-xs text-muted-foreground">
                                Shows the pulsing badge in the hero and the
                                footer.
                            </p>
                        </div>
                        <Switch
                            id="accepting_projects"
                            name="accepting_projects"
                            :default-value="company.accepting_projects"
                        />
                    </div>

                    <hr class="border-border/60" />

                    <div class="flex items-center gap-2">
                        <Sparkles class="size-4 text-muted-foreground" />
                        <h3 class="text-sm font-semibold tracking-wide uppercase">
                            Hero
                        </h3>
                    </div>

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
                            Use {{ nameToken }} to insert the company name.
                        </p>
                        <InputError :message="errors.hero_title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="hero_statement">Statement</Label>
                        <Input
                            id="hero_statement"
                            name="hero_statement"
                            :default-value="company.hero_statement ?? ''"
                        />
                        <p class="text-xs text-muted-foreground">
                            The muted line that follows the heading.
                        </p>
                        <InputError :message="errors.hero_statement" />
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="primary_cta_label">
                                Primary CTA
                            </Label>
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
                            <Label for="primary_cta_url">URL</Label>
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
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="secondary_cta_label">
                                Secondary CTA
                            </Label>
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
                            <Label for="secondary_cta_url">URL</Label>
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
                    </div>

                    <hr class="border-border/60" />

                    <div class="flex items-center gap-2">
                        <Link2 class="size-4 text-muted-foreground" />
                        <h3 class="text-sm font-semibold tracking-wide uppercase">
                            Social links
                        </h3>
                    </div>

                    <div class="grid gap-2">
                        <Label for="socials-github">GitHub</Label>
                        <Input
                            id="socials-github"
                            name="socials[github]"
                            type="url"
                            :default-value="company.socials?.github ?? ''"
                            placeholder="https://github.com/org"
                        />
                        <InputError :message="errors['socials.github']" />
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
                        <InputError :message="errors['socials.linkedin']" />
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
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
                    </div>

                    <hr class="border-border/60" />

                    <div class="flex items-center gap-2">
                        <FileText class="size-4 text-muted-foreground" />
                        <h3 class="text-sm font-semibold tracking-wide uppercase">
                            Files
                        </h3>
                    </div>

                    <FileField
                        name="logo"
                        label="Logo"
                        remove-name="remove_logo"
                        :current-url="company.logo_url"
                        accept="image/*"
                        preview
                        hint="Shown in the admin header and the Open Graph preview."
                    />
                    <InputError :message="errors.logo" />

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

                    <hr class="border-border/60" />

                    <div class="flex items-center gap-2">
                        <Search class="size-4 text-muted-foreground" />
                        <h3 class="text-sm font-semibold tracking-wide uppercase">
                            SEO
                        </h3>
                    </div>

                    <div class="grid gap-2">
                        <Label for="meta_title">Meta title</Label>
                        <Input
                            id="meta_title"
                            name="meta_title"
                            maxlength="70"
                            :default-value="company.meta_title ?? ''"
                        />
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
                            :default-value="company.meta_description ?? ''"
                        />
                        <InputError :message="errors.meta_description" />
                    </div>

                    <hr class="border-border/60" />

                    <div class="flex items-center gap-2">
                        <Building2 class="size-4 text-muted-foreground" />
                        <h3 class="text-sm font-semibold tracking-wide uppercase">
                            Footer
                        </h3>
                    </div>

                    <FooterEditor :footer="company.footer" />

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
                                ).filter(([key]) =>
                                    key.startsWith('footer.'),
                                )"
                                :key="key"
                            >
                                {{ message }}
                            </li>
                        </ul>
                    </div>

                    <Button
                        type="submit"
                        :disabled="processing"
                        class="w-fit"
                    >
                        {{ processing ? 'Saving…' : 'Save company' }}
                    </Button>
                </Form>
            </aside>
        </div>

        <SectionEditorDialog
            v-model:open="editorOpen"
            :company-id="company.id"
            :section="editingSection"
            :type="newSectionType"
        />
    </div>
</template>
