export type Social = {
    github?: string | null;
    linkedin?: string | null;
    x?: string | null;
    website?: string | null;
};

export type FooterLink = {
    label: string;
    url: string | null;
};

export type FooterColumn = {
    title: string;
    links: FooterLink[];
};

export type FooterConfig = {
    status_text: string | null;
    status_text_unavailable: string | null;
    copyright: string | null;
    back_to_top: string | null;
    columns: FooterColumn[];
    legal_links: FooterLink[];
};

export type SectionCopy = {
    eyebrow: string;
    title: string;
    highlight: string;
    description: string;
    message_label?: string;
};

export type HeroCopy = {
    primary_cta_label: string;
    primary_cta_url: string;
    secondary_cta_label: string;
    secondary_cta_url: string;
    resume_label: string;
    years_label: string;
    projects_label: string;
    skills_label: string;
    scroll_label: string;
};

export type AboutCopy = {
    role_label: string;
    location_label: string;
    email_label: string;
    phone_label: string;
    available_open: string;
    available_closed: string;
};

export type ContactCopy = {
    toast_title: string;
    toast_description: string;
};

export type ContentConfig = {
    sections: {
        about: SectionCopy;
        skills: SectionCopy;
        projects: SectionCopy;
        experience: SectionCopy;
        contact: SectionCopy;
    };
    hero: HeroCopy;
    about: AboutCopy;
    contact: ContactCopy;
};

export type Profile = {
    id: number;
    name: string;
    hero_title: string | null;
    hero_statement: string | null;
    headline: string | null;
    tagline: string | null;
    bio: string | null;
    /** A note from the founder, shown at the top of the About section. */
    founder_message: string | null;
    location: string | null;
    public_email: string | null;
    phone: string | null;
    avatar_path: string | null;
    logo_path: string | null;
    resume_path: string | null;
    og_image_path: string | null;
    available_for_work: boolean;
    socials: Social | null;
    roles: string[] | null;
    footer: FooterConfig | null;
    content: ContentConfig | null;
    meta_title: string | null;
    meta_description: string | null;
    avatar_url: string | null;
    logo_url: string | null;
    resume_url: string | null;
    og_image_url: string | null;
};

/**
 * The company behind the home route. Distinct from `Profile`, which is the
 * founder presented on the About route.
 */
export type Company = {
    id: number;
    name: string;
    legal_name: string | null;
    headline: string | null;
    tagline: string | null;
    bio: string | null;
    mission: string | null;
    location: string | null;
    public_email: string | null;
    phone: string | null;
    website: string | null;
    founded_year: string | null;
    logo_path: string | null;
    og_image_path: string | null;
    hero_eyebrow: string | null;
    hero_title: string | null;
    hero_statement: string | null;
    primary_cta_label: string | null;
    primary_cta_url: string | null;
    secondary_cta_label: string | null;
    secondary_cta_url: string | null;
    status_text: string | null;
    accepting_projects: boolean;
    socials: Social | null;
    footer: FooterConfig | null;
    meta_title: string | null;
    meta_description: string | null;
    logo_url: string | null;
    og_image_url: string | null;
};

/**
 * The fields the footer renders, whichever record owns it. Lets one footer
 * component serve both the company and the founder.
 */
export type FooterOwner = {
    name: string;
    headline: string | null;
    tagline: string | null;
    public_email: string | null;
    phone: string | null;
    location: string | null;
    socials: Social | null;
    footer: FooterConfig | null;
    available_for_work?: boolean;
    accepting_projects?: boolean;
};

export type ProjectContext = 'company' | 'personal';

/** A client or partner the studio has delivered for. */
export type Client = {
    id: number;
    company_id: number;
    name: string;
    logo_path: string | null;
    logo_url: string | null;
    website_url: string | null;
    industry: string | null;
    summary: string | null;
    sort_order: number;
    is_visible: boolean;
};

export type Project = {
    id: number;
    title: string;
    slug: string;
    /** Whether this project is shown on the company home page or the About page. */
    context: ProjectContext;
    summary: string | null;
    description: string | null;
    cover_image_path: string | null;
    gallery: string[] | null;
    tech_stack: string[] | null;
    repo_url: string | null;
    live_url: string | null;
    role: string | null;
    is_featured: boolean;
    is_published: boolean;
    has_landing_page: boolean;
    sort_order: number;
    started_at: string | null;
    completed_at: string | null;
    updated_at: string;
    cover_image_url: string | null;
    gallery_urls: string[];
    /** Set only when the project has a *published* marketing landing page. */
    landing_url: string | null;
};

export type SkillCategory =
    'language' | 'framework' | 'frontend' | 'database' | 'devops' | 'tool';

export type Skill = {
    id: number;
    name: string;
    category: SkillCategory | string;
    proficiency: number;
    icon: string | null;
    is_featured: boolean;
    sort_order: number;
};

export type SkillGroup = {
    key: string;
    label: string;
    skills: Skill[];
};

export type Experience = {
    id: number;
    company: string;
    role: string;
    employment_type: string | null;
    location: string | null;
    start_date: string;
    end_date: string | null;
    description: string | null;
    highlights: string[] | null;
    company_url: string | null;
    logo_path: string | null;
    logo_url: string | null;
    sort_order: number;
};

export type Education = {
    id: number;
    institution: string;
    degree: string;
    field_of_study: string | null;
    start_date: string;
    end_date: string | null;
    grade: string | null;
    description: string | null;
    sort_order: number;
};

export type ContactMessage = {
    id: number;
    name: string;
    email: string;
    subject: string | null;
    message: string;
    ip_address: string | null;
    user_agent: string | null;
    read_at: string | null;
    created_at: string;
};

export type PortfolioStats = {
    projects: number;
    skills: number;
    yearsExperience: number;
};

/**
 * Headline numbers for the public company pages. A figure the studio has no
 * claim to is null rather than 0, so a page never prints "0 clients served";
 * every consumer drops the null figures instead of rendering them.
 */
export type CompanyStats = {
    projects: number | null;
    clients: number | null;
    foundedYear: number | null;
    yearsInBusiness: number | null;
};

/** Laravel's length-aware paginator payload. */
export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};
