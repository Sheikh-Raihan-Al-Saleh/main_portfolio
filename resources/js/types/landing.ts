/**
 * Mirrors App\Enums\LandingSectionType and the `data` payload each case
 * declares in its `rules()`. Keep the two in step: the PHP enum validates what
 * is written, these types describe what the page may read.
 */
export type LandingSectionType =
    | 'features'
    | 'demo_video'
    | 'demo_embed'
    | 'demo_walkthrough'
    | 'stats'
    | 'gallery'
    | 'testimonials'
    | 'faq'
    | 'tech'
    | 'cta'
    | 'richtext';

export type FeatureItem = {
    icon: string | null;
    title: string;
    text: string | null;
};

export type DemoVideoData = {
    provider: 'upload' | 'youtube' | 'vimeo';
    video_path?: string | null;
    video_url?: string | null;
    poster_path?: string | null;
    caption?: string | null;
    autoplay?: boolean | null;
};

export type DemoEmbedData = {
    url: string;
    aspect: '16/9' | '4/3' | 'mobile';
    chrome: 'browser' | 'device' | 'none';
    allow_fullscreen?: boolean | null;
};

export type WalkthroughStep = {
    image_path: string;
    title: string;
    caption: string | null;
};

export type DemoWalkthroughData = {
    frame: 'browser' | 'laptop' | 'phone';
    steps: WalkthroughStep[];
};

export type StatItem = {
    value: number;
    suffix: string | null;
    label: string;
};

export type TestimonialItem = {
    quote: string;
    name: string;
    role: string | null;
    avatar_path: string | null;
};

export type FaqItem = {
    question: string;
    answer: string;
};

export type CtaData = {
    label: string;
    url: string;
    note: string | null;
};

/** Payload shapes keyed by the type that owns them. */
export type LandingSectionDataMap = {
    features: { items: FeatureItem[] };
    demo_video: DemoVideoData;
    demo_embed: DemoEmbedData;
    demo_walkthrough: DemoWalkthroughData;
    stats: { items: StatItem[] };
    gallery: { images: string[] };
    testimonials: { items: TestimonialItem[] };
    faq: { items: FaqItem[] };
    tech: { items: string[] };
    cta: CtaData;
    richtext: { markdown: string };
};

type BaseSection = {
    id: number;
    eyebrow: string | null;
    heading: string | null;
    subheading: string | null;
    body: string | null;
    is_visible: boolean;
};

/**
 * Discriminated union over `type`, so narrowing a section also narrows its
 * `data` to the right shape.
 */
export type LandingSection = {
    [K in LandingSectionType]: BaseSection & {
        type: K;
        data: LandingSectionDataMap[K];
        /** Server-rendered, sanitised HTML. Present on richtext sections only. */
        body_html?: string;
    };
}[LandingSectionType];

export type ProjectLandingPage = {
    id: number;
    project_id: number;
    eyebrow: string | null;
    headline: string | null;
    subheadline: string | null;
    hero_media_path: string | null;
    hero_video_url: string | null;
    primary_cta_label: string | null;
    primary_cta_url: string | null;
    secondary_cta_label: string | null;
    secondary_cta_url: string | null;
    accent_from: string | null;
    accent_to: string | null;
    seo_title: string | null;
    seo_description: string | null;
    og_image_path: string | null;
    is_published: boolean;
    hero_media_url: string | null;
    hero_media_urls: string[];
    og_image_url: string | null;
};

export type LandingSectionTypeOption = {
    value: LandingSectionType;
    label: string;
};
