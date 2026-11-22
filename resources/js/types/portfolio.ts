/**
 * Model shapes as the admin panel receives them (Eloquent JSON), plus the
 * page-copy and footer structures shared by the studio and the founder.
 */

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

/** The { platform: url } map the admin social-links fields edit. */
export type SocialMap = Partial<Record<SocialPlatform, string>>;

export type SocialPlatform =
    | 'github'
    | 'linkedin'
    | 'x'
    | 'dribbble'
    | 'youtube'
    | 'whatsapp'
    | 'website';

export type SectionCopy = {
    eyebrow: string;
    title: string;
    highlight: string;
    description: string;
    message_label?: string;
};

/** The founder page's editable copy (FounderDefaults::content()). */
export type FounderContent = {
    sections: {
        about: SectionCopy;
        skills: SectionCopy;
        projects: SectionCopy;
        experience: SectionCopy;
        contact: SectionCopy;
    };
    hero: {
        resume_label: string;
        years_label: string;
        projects_label: string;
        skills_label: string;
    };
    about: {
        available_open: string;
        available_closed: string;
    };
};

/** The studio record, as edited in Admin → Studio. */
export type Studio = {
    id: number;
    name: string;
    short_name: string | null;
    legal_name: string | null;
    slug: string;
    headline: string | null;
    tagline: string | null;
    bio: string | null;
    mission: string | null;
    founded_year: number | null;
    is_accepting_projects: boolean;
    location: string | null;
    public_email: string | null;
    phone: string | null;
    website_url: string | null;
    hero_eyebrow: string | null;
    hero_title: string | null;
    hero_statement: string | null;
    primary_cta_label: string | null;
    primary_cta_url: string | null;
    secondary_cta_label: string | null;
    secondary_cta_url: string | null;
    footer: FooterConfig | null;
    meta_title: string | null;
    meta_description: string | null;
    logo_url: string | null;
    logo_dark_url: string | null;
    logo_mark_url: string | null;
    og_image_url: string | null;
};

/** The founder record, as edited in Admin → Founder. */
export type Founder = {
    id: number;
    name: string;
    slug: string;
    headline: string | null;
    tagline: string | null;
    bio: string | null;
    founder_message: string | null;
    roles: string[] | null;
    is_available_for_work: boolean;
    location: string | null;
    public_email: string | null;
    phone: string | null;
    hero_title: string | null;
    hero_statement: string | null;
    primary_cta_label: string | null;
    primary_cta_url: string | null;
    secondary_cta_label: string | null;
    secondary_cta_url: string | null;
    footer: FooterConfig | null;
    content: FounderContent;
    meta_title: string | null;
    meta_description: string | null;
    avatar_url: string | null;
    logo_url: string | null;
    resume_url: string | null;
    og_image_url: string | null;
};

export type Technology = {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    projects_count?: number;
};

export type ProjectImage = {
    id: number;
    path: string;
    url: string | null;
    alt: string | null;
    caption: string | null;
    sort_order: number;
};

export type ProjectOwner = 'studio' | 'founder';

/** A project as the admin list and form receive it. */
export type Project = {
    id: number;
    owner_type: ProjectOwner;
    title: string;
    slug: string;
    summary: string | null;
    description?: string | null;
    role: string | null;
    repo_url: string | null;
    live_url: string | null;
    is_featured: boolean;
    is_published: boolean;
    sort_order: number;
    started_at: string | null;
    completed_at: string | null;
    cover_image_url: string | null;
    technologies: Pick<Technology, 'id' | 'name'>[];
    images?: ProjectImage[];
    images_count?: number;
    has_landing_page?: boolean;
};

/** A client or partner of the studio. */
export type Client = {
    id: number;
    name: string;
    logo_url: string | null;
    website_url: string | null;
    industry: string | null;
    summary: string | null;
    sort_order: number;
    is_visible: boolean;
};

export type Skill = {
    id: number;
    skill_category_id: number;
    name: string;
    proficiency: number;
    icon: string | null;
    is_featured: boolean;
    sort_order: number;
};

export type SkillCategory = {
    id: number;
    name: string;
    slug: string;
    sort_order: number;
    skills: Skill[];
};

export type ExperienceHighlight = {
    id: number;
    body: string;
    sort_order: number;
};

export type Experience = {
    id: number;
    company: string;
    role: string;
    employment_type: string | null;
    location: string | null;
    start_date: string;
    end_date: string | null;
    is_current: boolean;
    description: string | null;
    highlights: ExperienceHighlight[];
    company_url: string | null;
    logo_url: string | null;
    sort_order: number;
};

export type Education = {
    id: number;
    institution: string;
    degree: string | null;
    field_of_study: string | null;
    start_date: string | null;
    end_date: string | null;
    grade: string | null;
    description: string | null;
    sort_order: number;
};

export type ContactMessage = {
    id: number;
    recipient_type: ProjectOwner | null;
    name: string;
    email: string;
    subject: string | null;
    message: string;
    ip_address: string | null;
    user_agent: string | null;
    read_at: string | null;
    created_at: string;
};

export type SelectOption<T = string> = {
    value: T;
    label: string;
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
