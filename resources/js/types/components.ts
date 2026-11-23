/**
 * Props and local types of the components and layouts.
 * Vue files import them from @/types instead of declaring them inline.
 */

import type { LinkComponentBaseProps } from '@inertiajs/core';
import type { Method } from '@inertiajs/core';
import type { Component } from 'vue';
import type { HTMLAttributes } from 'vue';
import type {
    AboutContent,
    AppVariant,
    BreadcrumbItem,
    BreadcrumbItem as BreadcrumbItemType,
    CapabilitiesContent,
    Client,
    CtaContent,
    Education,
    EngineeringContent,
    Experience,
    Field,
    FooterConfig,
    FormTab,
    FounderBridgeContent,
    FounderContent,
    FounderStats,
    LandingSection,
    LandingSectionType,
    NavItem,
    ProjectCard,
    ProjectCard as ProjectCardType,
    ProjectDetail,
    SectionCopy,
    SkillCategory,
    SocialLink,
    SocialPlatform,
    StudioContent,
    StudioStats,
    User,
    ValueContent,
} from './index';

// components/common/AlertError.vue
export type AlertErrorProps = {
    errors: string[];
    title?: string;
};

// components/app/AppContent.vue
export type AppContentProps = {
    variant?: AppVariant;
    class?: string;
};

// components/app/AppShell.vue
export type AppShellProps = {
    variant?: AppVariant;
};

// components/app/AppSidebarHeader.vue
export type AppSidebarHeaderProps = {
    breadcrumbs?: BreadcrumbItem[];
};

// components/app/Breadcrumbs.vue
export type BreadcrumbsProps = {
    breadcrumbs: BreadcrumbItemType[];
};

// components/common/Heading.vue
export type HeadingProps = {
    title: string;
    description?: string;
    variant?: 'default' | 'small';
};

// components/common/InputError.vue
export type InputErrorProps = {
    message?: string;
};

// components/account/ManageTwoFactor.vue
export type ManageTwoFactorProps = {
    canManageTwoFactor?: boolean;
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

// components/app/NavFooter.vue
export type NavFooterProps = {
    items: NavItem[];
    class?: string;
};

// components/app/NavMain.vue
export type NavMainProps = {
    items: NavItem[];
    label?: string;
};

// components/common/PasswordInput.vue
export type PasswordInputProps = {
    class?: HTMLAttributes['class'];
};

// components/common/TextLink.vue
export type TextLinkProps = {
    href: LinkComponentBaseProps['href'];
    tabindex?: number;
    method?: Method;
    as?: string;
};

// components/account/TwoFactorSetupModal.vue
export type TwoFactorSetupModalProps = {
    requiresConfirmation: boolean;
    twoFactorEnabled: boolean;
};

// components/app/UserInfo.vue
export type UserInfoProps = {
    user: User;
    showEmail?: boolean;
};

// components/app/UserMenuContent.vue
export type UserMenuContentProps = {
    user: User;
};

// components/admin/AdminPageHeader.vue
export type AdminPageHeaderProps = {
    title: string;
    description?: string;
    eyebrow?: string;
};

// components/admin/CompletenessRing.vue
export type CompletenessRingProps = {
    label: string;
    percent: number;
    missing: string[];
    /** Route the "fix" links point at; missing items append to it. */
    href: string;
    size?: number;
};

// components/admin/ContentEditor.vue
export type ContentEditorProps = { content: FounderContent };

// components/admin/DeleteAction.vue
export type DeleteActionProps = {
    /** Wayfinder route definition for the destroy action. */
    url: string;
    label: string;
    description?: string;
};

// components/admin/DonutChart.vue
export type DonutChartProps = {
    slices: {
        label: string;
        value: number;
        color: string;
    }[];
    /** Label in the hole; defaults to the total. */
    centerLabel?: string;
    size?: number;
};

// components/admin/FileField.vue
export type FileFieldProps = {
    name: string;
    label: string;
    /** Name of the companion boolean field that clears the stored file. */
    removeName?: string;
    currentUrl?: string | null;
    accept?: string;
    hint?: string;
    /** Render the existing file as an image preview rather than a filename. */
    preview?: boolean;
};

// components/admin/FooterEditor.vue
export type FooterEditorProps = {
    footer: FooterConfig | null;
};
export type FooterEditorLinkRow = {
    label: string;
    url: string;
};
export type FooterEditorColumnRow = {
    title: string;
    links: FooterEditorLinkRow[];
};

// components/admin/LineChart.vue
export type LineChartProps = {
    labels: string[];
    series: {
        name: string;
        values: number[];
        color: string;
    }[];
    height?: number;
};

// components/admin/ReorderButtons.vue
export type ReorderButtonsProps = {
    index: number;
    total: number;
    disabled?: boolean;
};

// components/admin/StudioContentSectionDialog.vue
export type StudioContentSectionDialogRecordOf = Record<string, unknown>;
export type StudioContentSectionDialogSectionKey = keyof Omit<
    StudioContent,
    'ticker'
>;
export type StudioContentSectionDialogFieldDef = {
    key: string;
    label: string;
    long?: boolean;
    required?: boolean;
    placeholder?: string;
};
export type StudioContentSectionDialogRepeaterDef = {
    key: 'items' | 'stages' | 'stack' | 'guarantees';
    label: string;
    addLabel: string;
    /** Field definitions for object rows; omitted for string rows. */
    fields?: StudioContentSectionDialogFieldDef[];
    /** Marks the icon-name field so it can render a live preview. */
    iconField?: boolean;
};
export type StudioContentSectionDialogSectionDef = {
    title: string;
    description: string;
    fields: StudioContentSectionDialogFieldDef[];
    repeater?: StudioContentSectionDialogRepeaterDef;
};
export type StudioContentSectionDialogProps = {
    open: boolean;
    sectionKey: StudioContentSectionDialogSectionKey | null;
    /** The merged section content to seed the form with. */
    content: StudioContentSectionDialogRecordOf | null;
};
export type StudioContentSectionDialogExposedDef = {
    fields: StudioContentSectionDialogFieldDef[];
    repeater?: StudioContentSectionDialogRepeaterDef;
};

// components/admin/TagInput.vue
export type TagInputProps = {
    /**
     * Form field name; values are submitted as `name[]`. Optional, because
     * callers that read the list through `v-model` submit it themselves.
     */
    name?: string;
    modelValue?: string[] | null;
    placeholder?: string;
    /** Known values offered while typing (a native datalist). */
    suggestions?: string[];
};

// components/admin/form/ErrorList.vue
export type ErrorListProps = {
    errors: Record<string, string>;
    prefix: string;
};

// components/admin/form/FieldGroup.vue
export type FieldGroupProps = {
    title: string;
    description?: string;
    columns?: 1 | 2;
};

// components/admin/form/FormDialog.vue
export type FormDialogProps = {
    title: string;
    description?: string;
    icon?: Component;
};

// components/admin/form/FormFooter.vue
export type FormFooterProps = {
    processing?: boolean;
    submitLabel: string;
    mode?: 'page' | 'dialog';
    cancelHref?: string;
};

// components/admin/form/FormSection.vue
export type FormSectionProps = {
    title?: string;
    description?: string;
    /** Two columns from `sm` up; three adds a third column from `lg` up. */
    columns?: 1 | 2 | 3;
    class?: string;
};

// components/admin/form/FormTabs.vue
export type FormTabsProps = {
    tabs: FormTab[];
    errors?: Record<string, string>;
    processing?: boolean;
    submitLabel?: string;
    cancelHref?: string | null;
};

// components/admin/landing/MediaUploadField.vue
export type MediaUploadFieldProps = {
    modelValue: string | null;
    label: string;
    /** Upload directory; must be one the backend allowlists. */
    directory: 'steps' | 'gallery' | 'video' | 'poster' | 'avatar';
    /** Accept video files as well as images. */
    video?: boolean;
    hint?: string;
};

// components/admin/landing/SectionEditorDialog.vue
export type SectionEditorDialogProps = {
    open: boolean;
    /**
     * The page a new section is added to: the studio home page, or the
     * landing page of the project given by `projectId`.
     */
    owner: 'studio' | 'project';
    projectId?: number | null;
    /** Editing an existing section, or null when adding a new one. */
    section: LandingSection | null;
    /** Type for a new section. Ignored when `section` is provided. */
    type: LandingSectionType | null;
};

// components/admin/landing/SectionFields.vue
export type SectionFieldsProps = {
    fields: Field[];
    /** Laravel validation errors, keyed as `data.items.0.title`. */
    errors: Record<string, string>;
    /** Error-key prefix for the current nesting level. */
    prefix?: string;
};

// components/admin/landing/SectionListRow.vue
export type SectionListRowProps = {
    section: LandingSection;
    index: number;
    total: number;
    typeLabel: string;
    disabled?: boolean;
    /** What the admin calls a section on this page ("section" or "block"). */
    noun?: string;
};

// components/brand/BrandLogo.vue
export type BrandLogoProps = {
    /** Full logo, or the square mark (also the favicon). */
    variant?: 'full' | 'mark';
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'hero';
    /** "dark" picks the logo made for dark backgrounds, if one was uploaded. */
    tone?: 'light' | 'dark';
    /** Show the name next to a mark-only logo. */
    withName?: boolean;
    class?: string;
};

// components/founder/ExperienceSection.vue
export type ExperienceSectionProps = {
    copy: SectionCopy;
    experiences: Experience[];
    educations: Education[];
};

// components/founder/FounderAbout.vue
export type FounderAboutProps = { copy: SectionCopy };

// components/founder/FounderHero.vue
export type FounderHeroProps = {
    content: FounderContent;
    stats: FounderStats;
};

// components/founder/SkillsSection.vue
export type SkillsSectionProps = {
    copy: SectionCopy;
    groups: SkillCategory[];
};

// components/landing/HeroDevicesCarousel.vue
export type HeroDevicesCarouselProps = {
    images: string[];
};

// components/landing/SectionList.vue
export type SectionListProps = {
    sections: LandingSection[];
    /**
     * How a section's DOM id is derived, so the anchor can be jumped to and
     * picked up by the scroll spy. The id is passed down to the block.
     */
    anchorFor: (section: LandingSection) => string;
};

// components/landing/SectionShell.vue
export type SectionShellProps = {
    eyebrow?: string | null;
    heading?: string | null;
    subheading?: string | null;
    anchor?: string;
    /** A tinted band, to separate consecutive blocks. */
    muted?: boolean;
    wide?: boolean;
    class?: string;
};

// components/landing/frames/BrowserFrame.vue
export type BrowserFrameProps = {
    /** Shown in the fake address bar. Host only — the full URL is noise. */
    url?: string | null;
    class?: string;
};

// components/landing/frames/DeviceFrame.vue
export type DeviceFrameProps = {
    variant?: 'phone' | 'tablet' | 'laptop';
    class?: string;
};

// components/landing/sections/CtaSection.vue
export type CtaSectionProps = {
    section: Extract<LandingSection, { type: 'cta' }>;
    anchor?: string;
};

// components/landing/sections/DemoEmbedSection.vue
export type DemoEmbedSectionProps = {
    section: Extract<LandingSection, { type: 'demo_embed' }>;
    anchor?: string;
};

// components/landing/sections/DemoVideoSection.vue
export type DemoVideoSectionProps = {
    section: Extract<LandingSection, { type: 'demo_video' }>;
    anchor?: string;
};

// components/landing/sections/FaqSection.vue
export type FaqSectionProps = {
    section: Extract<LandingSection, { type: 'faq' }>;
    anchor?: string;
};

// components/landing/sections/FeaturesSection.vue
export type FeaturesSectionProps = {
    section: Extract<LandingSection, { type: 'features' }>;
    anchor?: string;
};

// components/landing/sections/GallerySection.vue
export type GallerySectionProps = {
    section: Extract<LandingSection, { type: 'gallery' }>;
    anchor?: string;
};

// components/landing/sections/RichTextSection.vue
export type RichTextSectionProps = {
    section: Extract<LandingSection, { type: 'richtext' }>;
    anchor?: string;
};

// components/landing/sections/StatsSection.vue
export type StatsSectionProps = {
    section: Extract<LandingSection, { type: 'stats' }>;
    anchor?: string;
};

// components/landing/sections/TechSection.vue
export type TechSectionProps = {
    section: Extract<LandingSection, { type: 'tech' }>;
    anchor?: string;
};

// components/landing/sections/TestimonialsSection.vue
export type TestimonialsSectionProps = {
    section: Extract<LandingSection, { type: 'testimonials' }>;
    anchor?: string;
};

// components/landing/sections/WalkthroughSection.vue
export type WalkthroughSectionProps = {
    section: Extract<LandingSection, { type: 'demo_walkthrough' }>;
    anchor?: string;
};

// components/media/SmartImage.vue
export type SmartImageProps = {
    src: string | null | undefined;
    alt: string;
    /** CSS aspect-ratio for the frame, e.g. "16/10". Omit to size by content. */
    ratio?: string;
    /** object-fit for the image. */
    fit?: 'cover' | 'contain';
    eager?: boolean;
    class?: string;
    imgClass?: string;
};

// components/media/SmartVideo.vue
export type SmartVideoProps = {
    src: string | null | undefined;
    poster?: string | null;
    autoplay?: boolean;
    loop?: boolean;
    ratio?: string;
    label?: string;
    class?: string;
};

// components/project/ProjectCard.vue
export type ProjectCardProps = {
    project: ProjectCard;
    /** "feature" is the large, wide card that leads a grid. */
    variant?: 'default' | 'feature';
    index?: number;
    eager?: boolean;
};

// components/project/ProjectGallery.vue
export type ProjectGalleryProps = { images: ProjectDetail['images'] };

// components/project/ProjectGrid.vue
export type ProjectGridProps = {
    projects: ProjectCardType[];
    /** Show the first project as a wide feature card. */
    feature?: boolean;
    numbered?: boolean;
};

// components/site/AnimatedCounter.vue
export type AnimatedCounterProps = {
    value: number;
    suffix?: string;
    duration?: number;
};

// components/site/ContactForm.vue
export type ContactFormProps = {
    site: 'studio' | 'founder';
    messagePlaceholder?: string;
    submitLabel?: string;
    replyNote?: string | null;
};

// components/site/ContactSection.vue
export type ContactSectionProps = {
    site: 'studio' | 'founder';
    eyebrow: string;
    title: string;
    highlight?: string | null;
    description?: string | null;
    email?: string | null;
    phone?: string | null;
    location?: string | null;
    socials?: SocialLink[];
    emailHeading?: string;
    emailNote?: string | null;
    placeholder?: string;
    submitLabel?: string;
    replyNote?: string | null;
};

// components/site/LiquidBlob.vue
export type LiquidBlobProps = {
    class?: string;
};

// components/site/LiquidImage.vue
export type LiquidImageProps = {
    src: string;
    alt?: string;
    class?: string;
    imgClass?: string;
    eager?: boolean;
};

// components/site/Marquee.vue
export type MarqueeProps = {
    /** Seconds for one full loop. */
    duration?: number;
    gap?: string;
    reverse?: boolean;
};

// components/site/Reveal.vue
export type RevealProps = {
    delay?: number;
    y?: number;
    as?: 'div' | 'li' | 'section' | 'article' | 'span';
};

// components/site/SectionHeading.vue
export type SectionHeadingProps = {
    eyebrow?: string | null;
    title: string;
    highlight?: string | null;
    description?: string | null;
    align?: 'left' | 'split';
    size?: 'md' | 'lg';
    class?: string;
};

// components/site/SiteHeader.vue
export type SiteHeaderNavLink = { label: string; href: string };

// components/site/SocialIcon.vue
export type SocialIconProps = { name: SocialPlatform };

// components/site/SocialLinks.vue
export type SocialLinksProps = {
    links: SocialLink[];
    class?: string;
};

// components/studio/AboutSection.vue
export type AboutSectionProps = { content: AboutContent };

// components/studio/CapabilitiesSection.vue
export type CapabilitiesSectionProps = { content: CapabilitiesContent };

// components/studio/ClientStrip.vue
export type ClientStripProps = {
    clients: Client[];
    label?: string;
};

// components/studio/CtaBanner.vue
export type CtaBannerProps = { content: CtaContent };

// components/studio/FounderBridge.vue
export type FounderBridgeProps = { content: FounderBridgeContent };

// components/studio/ProcessSection.vue
export type ProcessSectionProps = { content: EngineeringContent };

// components/studio/StudioHero.vue
export type StudioHeroProps = {
    stats: StudioStats;
    ticker: string[];
};

// components/studio/ValueSection.vue
export type ValueSectionProps = { content: ValueContent };

// components/studio/WorkSection.vue
export type WorkSectionProps = {
    id?: string;
    eyebrow: string;
    title: string;
    highlight?: string | null;
    description?: string | null;
    projects: ProjectCard[];
    total?: number;
    archiveHref?: string;
    archiveLabel?: string;
    emptyText?: string;
    feature?: boolean;
    /** Show the archive link even when every project is already listed. */
    alwaysShowArchive?: boolean;
};

// layouts/AppLayout.vue
export type AppLayoutProps = {
    breadcrumbs?: BreadcrumbItem[];
};

// layouts/AuthLayout.vue
export type AuthLayoutProps = {
    title?: string;
    description?: string;
};

// layouts/app/AppSidebarLayout.vue
export type AppSidebarLayoutProps = {
    breadcrumbs?: BreadcrumbItem[];
};

// layouts/auth/AuthEditorialLayout.vue
export type AuthEditorialLayoutShowcase = { title: string; cover: string }[];
export type AuthEditorialLayoutProps = {
    title?: string;
    description?: string;
};

// Emitted events

// components/admin/ReorderButtons.vue
export type ReorderButtonsEmits = {
    move: [index: number, direction: -1 | 1];
};

// components/admin/StudioContentSectionDialog.vue
export type StudioContentSectionDialogEmits = {
    'update:open': [boolean];
    /** Emitted with the edited clone; the parent decides when to persist. */
    save: [
        StudioContentSectionDialogSectionKey,
        StudioContentSectionDialogRecordOf,
    ];
};

// components/admin/TagInput.vue
export type TagInputEmits = { 'update:modelValue': [string[]] };

// components/admin/form/FormFooter.vue
export type FormFooterEmits = { cancel: [] };

// components/admin/landing/MediaUploadField.vue
export type MediaUploadFieldEmits = { 'update:modelValue': [string | null] };

// components/admin/landing/SectionEditorDialog.vue
export type SectionEditorDialogEmits = { 'update:open': [boolean]; saved: [] };

// components/admin/landing/SectionListRow.vue
export type SectionListRowEmits = {
    move: [index: number, direction: -1 | 1];
    edit: [];
};
