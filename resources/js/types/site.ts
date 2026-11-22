/**
 * Shapes of the props the public website receives. They mirror the API
 * Resources in app/Http/Resources and the shared props in
 * HandleInertiaRequests.
 */
import type { FooterConfig, SocialPlatform } from './portfolio';

/** The company name and logos, shared on every page. */
export type Brand = {
    name: string;
    short_name: string;
    tagline: string | null;
    logo_url: string | null;
    logo_dark_url: string | null;
    logo_mark_url: string | null;
    website_url: string | null;
};

export type SocialLink = {
    platform: SocialPlatform;
    label: string | null;
    url: string;
};

export type Cta = {
    label: string | null;
    url: string | null;
};

type SiteMeta = {
    title: string | null;
    description: string | null;
};

/** StudioResource */
export type SiteStudio = {
    id: number;
    name: string;
    short_name: string;
    legal_name: string | null;
    headline: string | null;
    tagline: string | null;
    bio: string | null;
    mission: string | null;
    founded_year: number | null;
    years_in_business: number | null;
    is_accepting_projects: boolean;
    location: string | null;
    public_email: string | null;
    phone: string | null;
    website_url: string | null;
    logo_url: string | null;
    logo_dark_url: string | null;
    logo_mark_url: string | null;
    og_image_url: string | null;
    hero: {
        eyebrow: string | null;
        title: string | null;
        statement: string | null;
        primary_cta: Cta;
        secondary_cta: Cta;
    };
    socials: SocialLink[];
    footer: Partial<FooterConfig>;
    meta: SiteMeta;
};

/** FounderResource */
export type SiteFounder = {
    id: number;
    name: string;
    headline: string | null;
    tagline: string | null;
    bio: string | null;
    founder_message: string | null;
    roles: string[];
    is_available_for_work: boolean;
    location: string | null;
    public_email: string | null;
    phone: string | null;
    avatar_url: string | null;
    logo_url: string | null;
    resume_url: string | null;
    og_image_url: string | null;
    hero: {
        title: string | null;
        statement: string | null;
        primary_cta: Cta;
        secondary_cta: Cta;
    };
    socials: SocialLink[];
    footer: Partial<FooterConfig>;
    meta: SiteMeta;
};

export type TechnologyTag = {
    name: string;
    slug: string;
};

/** ProjectCardResource */
export type ProjectCard = {
    id: number;
    title: string;
    slug: string;
    summary: string | null;
    owner: 'studio' | 'founder';
    role: string | null;
    cover_image_url: string | null;
    is_featured: boolean;
    year: number | null;
    technologies: TechnologyTag[];
    url: string;
    landing_url: string | null;
    live_url: string | null;
    repo_url: string | null;
};

/** ProjectResource */
export type ProjectDetail = ProjectCard & {
    description: string | null;
    started_at: string | null;
    completed_at: string | null;
    images: {
        id: number;
        url: string;
        alt: string;
        caption: string | null;
    }[];
};

/**
 * Headline numbers for the studio pages. A figure the studio has no claim to
 * is null, so a page never prints "0 clients".
 */
export type StudioStats = {
    projects: number | null;
    clients: number | null;
    yearsInBusiness: number | null;
};

export type FounderStats = {
    projects: number;
    skills: number;
    yearsExperience: number;
};
