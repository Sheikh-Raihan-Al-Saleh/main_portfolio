/**
 * Props and local types of the pages.
 * Vue files import them from @/types instead of declaring them inline.
 */

import type {
    Client,
    CompletenessScore,
    ContactMessage,
    DashboardActivity,
    Education,
    Experience,
    Founder,
    FounderContent,
    FounderStats,
    LandingSection,
    LandingSectionTypeOption,
    ManageTwoFactorProps,
    MessageSource,
    Paginated,
    Project,
    ProjectCard,
    ProjectDetail,
    ProjectLandingPage,
    SelectOption,
    Skill,
    SkillCategory,
    SocialMap,
    Studio,
    StudioContent,
    StudioStats,
    Technology,
} from './index';
// pages/admin/clients/Index.vue
export type AdminClientsIndexProps = {
    clients: Client[];
    filters: { search: string | null };
};

// pages/admin/dashboard/Index.vue
export type AdminDashboardIndexProps = {
    stats: {
        projects: number;
        publishedProjects: number;
        clients: number;
        skills: number;
        experiences: number;
        educations: number;
        messages: number;
        unreadMessages: number;
        homeBlocks: number;
    };
    activity: DashboardActivity;
    completeness: {
        studio: CompletenessScore;
        founder: CompletenessScore;
    };
    recentMessages: ContactMessage[];
    recentProjects: {
        id: number;
        title: string;
        slug: string;
        is_published: boolean;
        is_featured: boolean;
        updated_at: string;
    }[];
    messageSources: MessageSource[];
};

// pages/admin/educations/Edit.vue
export type AdminEducationsEditProps = {
    education: Education;
};

// pages/admin/educations/EducationForm.vue
export type AdminEducationsEducationFormProps = {
    education?: Education;
    action: Record<string, unknown>;
    submitLabel: string;
    mode?: 'page' | 'dialog';
};

// pages/admin/educations/Index.vue
export type AdminEducationsIndexProps = {
    educations: Education[];
};

// pages/admin/experiences/Edit.vue
export type AdminExperiencesEditProps = {
    experience: Experience;
};

// pages/admin/experiences/ExperienceForm.vue
export type AdminExperiencesExperienceFormProps = {
    experience?: Experience;
    action: Record<string, unknown>;
    submitLabel: string;
    mode?: 'page' | 'dialog';
};

// pages/admin/experiences/Index.vue
export type AdminExperiencesIndexProps = {
    experiences: Experience[];
};

// pages/admin/founder/Edit.vue
export type AdminFounderEditProps = {
    founder: Founder;
    socials: SocialMap;
};

// pages/admin/messages/Index.vue
export type AdminMessagesIndexProps = {
    messages: Paginated<ContactMessage>;
    filters: { filter: string | null };
};

// pages/admin/messages/Show.vue
export type AdminMessagesShowProps = {
    message: ContactMessage;
};

// pages/admin/projects/Create.vue
export type AdminProjectsCreateProps = {
    owners: SelectOption[];
    technologyOptions: string[];
};

// pages/admin/projects/Edit.vue
export type AdminProjectsEditProps = {
    project: Project;
    owners: SelectOption[];
    technologyOptions: string[];
};

// pages/admin/projects/Index.vue
export type AdminProjectsIndexProps = {
    projects: Project[];
    filters: { search: string | null };
};

// pages/admin/projects/ProjectForm.vue
export type AdminProjectsProjectFormProps = {
    project?: Project;
    /** Wayfinder form props: `ProjectController.store.form()` or `.update.form(id)`. */
    action: Record<string, unknown>;
    submitLabel: string;
    /** Who the project belongs to: the studio or the founder. */
    owners: SelectOption[];
    /** Known technology names, suggested while typing the stack. */
    technologyOptions: string[];
};

// pages/admin/projects/landing/Edit.vue
export type AdminProjectsLandingEditProps = {
    project: { id: number; title: string; slug: string };
    landingPage: ProjectLandingPage | null;
    sections: LandingSection[];
    sectionTypes: LandingSectionTypeOption[];
    previewUrl: string;
};

// pages/admin/skills/Create.vue
export type AdminSkillsCreateProps = {
    categoryOptions: SelectOption<number>[];
};

// pages/admin/skills/Edit.vue
export type AdminSkillsEditProps = {
    skill: Skill;
    categoryOptions: SelectOption<number>[];
};

// pages/admin/skills/Index.vue
export type AdminSkillsIndexProps = {
    /** The founder's categories, in order, each with its skills. */
    categories: SkillCategory[];
};

// pages/admin/skills/SkillCategoryManager.vue
export type AdminSkillsSkillCategoryManagerProps = {
    categories: SkillCategory[];
};

// pages/admin/skills/SkillForm.vue
export type AdminSkillsSkillFormProps = {
    skill?: Skill;
    /** The founder's skill categories. */
    categoryOptions: SelectOption<number>[];
    action: Record<string, unknown>;
    submitLabel: string;
    mode?: 'page' | 'dialog';
};

// pages/admin/studio/Content.vue
export type AdminStudioContentProps = {
    studio: Pick<Studio, 'id' | 'name'>;
    /** The merged studio content: defaults plus any stored edits. */
    content: StudioContent;
};

// pages/admin/studio/Edit.vue
export type AdminStudioEditProps = {
    studio: Studio;
    socials: SocialMap;
    sections: LandingSection[];
    sectionTypes: LandingSectionTypeOption[];
};

// pages/admin/technologies/Index.vue
export type AdminTechnologiesIndexProps = { technologies: Technology[] };

// pages/auth/ForgotPassword.vue
export type AuthForgotPasswordProps = {
    status?: string;
};

// pages/auth/Login.vue
export type AuthLoginProps = {
    status?: string;
    canResetPassword: boolean;
};

// pages/auth/ResetPassword.vue
export type AuthResetPasswordProps = {
    token: string;
    email: string;
    passwordRules: string;
};

// pages/auth/VerifyEmail.vue
export type AuthVerifyEmailProps = {
    status?: string;
};

// pages/public/about/Index.vue
export type PublicAboutIndexProps = {
    clients: Client[];
    stats: StudioStats;
};

// pages/public/founder/Index.vue
export type PublicFounderIndexProps = {
    content: FounderContent;
    skillGroups: SkillCategory[];
    experiences: Experience[];
    educations: Education[];
    projects: ProjectCard[];
    stats: FounderStats;
};

// pages/public/home/Index.vue
export type PublicHomeIndexProps = {
    content: StudioContent;
    projects: ProjectCard[];
    totalProjects: number;
    founderProjects: ProjectCard[];
    totalFounderProjects: number;
    clients: Client[];
    sections: LandingSection[];
    stats: StudioStats;
};

// pages/public/landing/Show.vue
export type PublicLandingShowLandingProject = {
    id: number;
    title: string;
    slug: string;
    summary: string | null;
    role: string | null;
    owner_type: 'studio' | 'founder';
    live_url: string | null;
    repo_url: string | null;
    cover_image_url: string | null;
    started_at: string | null;
    completed_at: string | null;
};
export type PublicLandingShowProps = {
    project: PublicLandingShowLandingProject;
    landingPage: ProjectLandingPage;
    sections: LandingSection[];
};

// pages/public/projects/Index.vue
export type PublicProjectsIndexProps = {
    projects: ProjectCard[];
    filters: { tech: string | null; owner: 'studio' | 'founder' | null };
};

// pages/public/projects/Show.vue
export type PublicProjectsShowProps = {
    project: ProjectDetail;
    related: ProjectCard[];
};

// pages/settings/Security.vue
export type SettingsSecurityProps = {
    passwordRules: string;
} & ManageTwoFactorProps;

// Emitted events

// pages/admin/educations/EducationForm.vue
export type AdminEducationsEducationFormEmits = { saved: []; cancel: [] };

// pages/admin/experiences/ExperienceForm.vue
export type AdminExperiencesExperienceFormEmits = { saved: []; cancel: [] };

// pages/admin/skills/SkillForm.vue
export type AdminSkillsSkillFormEmits = { saved: []; cancel: [] };
