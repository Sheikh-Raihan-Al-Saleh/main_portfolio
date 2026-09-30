/**
 * Mirrors App\Support\StudioContent::defaults().
 *
 * The company's `content` column stores admin edits; the model merges them
 * over these defaults before shipping `studio_content` to the client, so
 * every key below is always present at render time. Keep the two in step:
 * the PHP class is what validates and fills, these types describe what the
 * page may read.
 */

export type IconItem = {
    icon: string;
    title: string;
    summary: string;
    tech?: string[];
};

export type StackLayer = {
    label: string;
    meta: string;
};

export type StageItem = {
    icon: string;
    label: string;
    tagline: string;
    detail: string;
    tech: string[];
};

/** A numbered section heading shared by every story chapter. */
export type SectionHeadingCopy = {
    eyebrow: string;
    title: string;
    highlight: string;
    description: string;
};

export type CapabilitiesContent = SectionHeadingCopy & {
    kicker: string;
    stack_heading: string;
    stack_intro: string;
    footer_note: string;
    footer_link: string;
    items: IconItem[];
    stack: StackLayer[];
};

export type EngineeringContent = SectionHeadingCopy & {
    stages: StageItem[];
};

export type ValueContent = SectionHeadingCopy & {
    guarantees: string[];
    items: IconItem[];
};

export type WorkContent = SectionHeadingCopy & {
    featured_label: string;
    case_study_label: string;
    live_label: string;
    source_label: string;
    archive_label: string;
    empty_text: string;
};

export type FounderWorkContent = SectionHeadingCopy & {
    archive_label: string;
    empty_text: string;
};

export type AboutContent = {
    eyebrow: string;
    title: string;
    highlight: string;
    mission_label: string;
    more_label: string;
};

export type FounderBridgeContent = {
    studio_label: string;
    studio_heading: string;
    studio_text: string;
    studio_link: string;
    founder_label: string;
    founder_heading: string;
    founder_text: string;
    founder_link: string;
};

export type CtaContent = SectionHeadingCopy & {
    button_label: string;
    email_label: string;
    status_available: string;
    status_unavailable: string;
};

export type ClientsContent = SectionHeadingCopy;

export type ContactContent = SectionHeadingCopy & {
    email_heading: string;
    email_note: string;
    form_placeholder: string;
    form_submit: string;
    form_reply_note: string;
};

export type TickerContent = {
    items: string[];
};

export type StudioContent = {
    ticker: TickerContent;
    capabilities: CapabilitiesContent;
    engineering: EngineeringContent;
    value: ValueContent;
    work: WorkContent;
    founder_work: FounderWorkContent;
    about: AboutContent;
    founder_bridge: FounderBridgeContent;
    cta: CtaContent;
    clients: ClientsContent;
    contact: ContactContent;
};

/** The analytics dashboard payload (Admin\DashboardController::index). */
export type DashboardActivity = {
    labels: string[];
    messages: number[];
    projects: number[];
};

export type CompletenessScore = {
    percent: number;
    missing: string[];
};

export type MessageSource = {
    source: string;
    count: number;
};
