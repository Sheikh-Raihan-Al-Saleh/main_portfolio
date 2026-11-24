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
    RouteFormAttributes,
    SelectOption,
    Skill,
    SkillCategory,
    SocialMap,
    Studio,
    StudioContent,
    StudioStats,
    Technology,
} from './index';
// pages/client/ClientIndex.vue
export type ClientIndexProps = {
    clients: Client[];
    filters: { search: string | null };
};

// pages/Dashboard.vue
export type DashboardProps = {
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

// pages/education/EditEducation.vue
export type EditEducationProps = {
    education: Education;
};

// components/Education/EducationForm.vue
export type EducationFormProps = {
    education?: Education;
    action: Record<string, unknown>;
    submitLabel: string;
    mode?: 'page' | 'dialog';
};

// pages/education/EducationIndex.vue
export type EducationIndexProps = {
    educations: Education[];
};

// pages/experience/EditExperience.vue
export type EditExperienceProps = {
    experience: Experience;
};

// components/Experience/ExperienceForm.vue
export type ExperienceFormProps = {
    experience?: Experience;
    action: Record<string, unknown>;
    submitLabel: string;
    mode?: 'page' | 'dialog';
};

// pages/experience/ExperienceIndex.vue
export type ExperienceIndexProps = {
    experiences: Experience[];
};

// pages/founder/EditFounder.vue
export type EditFounderProps = {
    founder: Founder;
    socials: SocialMap;
};

// pages/contact/MessageIndex.vue
export type MessageIndexProps = {
    messages: Paginated<ContactMessage>;
    filters: { filter: string | null };
};

// pages/contact/ShowMessage.vue
export type ShowMessageProps = {
    message: ContactMessage;
};

// pages/project/CreateProject.vue
export type CreateProjectProps = {
    owners: SelectOption[];
    technologyOptions: string[];
};

// pages/project/EditProject.vue
export type EditProjectProps = {
    project: Project;
    owners: SelectOption[];
    technologyOptions: string[];
};

// pages/project/ProjectIndex.vue
export type ProjectIndexProps = {
    projects: Project[];
    filters: { search: string | null };
};

// components/Project/ProjectForm.vue
export type ProjectFormProps = {
    project?: Project;
    /** Form attributes: `routeForm('projectStore')` or `routeForm('projectUpdate', slug)`. */
    action: RouteFormAttributes;
    submitLabel: string;
    /** Who the project belongs to: the studio or the founder. */
    owners: SelectOption[];
    /** Known technology names, suggested while typing the stack. */
    technologyOptions: string[];
};

// pages/landingpage/EditLandingPage.vue
export type EditLandingPageProps = {
    project: { id: number; title: string; slug: string };
    landingPage: ProjectLandingPage | null;
    sections: LandingSection[];
    sectionTypes: LandingSectionTypeOption[];
    previewUrl: string;
};

// pages/skill/CreateSkill.vue
export type CreateSkillProps = {
    categoryOptions: SelectOption<number>[];
};

// pages/skill/EditSkill.vue
export type EditSkillProps = {
    skill: Skill;
    categoryOptions: SelectOption<number>[];
};

// pages/skill/SkillIndex.vue
export type SkillIndexProps = {
    /** The founder's categories, in order, each with its skills. */
    categories: SkillCategory[];
};

// components/Skill/SkillCategoryManager.vue
export type SkillCategoryManagerProps = {
    categories: SkillCategory[];
};

// components/Skill/SkillForm.vue
export type SkillFormProps = {
    skill?: Skill;
    /** The founder's skill categories. */
    categoryOptions: SelectOption<number>[];
    action: Record<string, unknown>;
    submitLabel: string;
    mode?: 'page' | 'dialog';
};

// pages/studio/StudioContent.vue
export type StudioContentProps = {
    studio: Pick<Studio, 'id' | 'name'>;
    /** The merged studio content: defaults plus any stored edits. */
    content: StudioContent;
};

// pages/studio/EditStudio.vue
export type EditStudioProps = {
    studio: Studio;
    socials: SocialMap;
    sections: LandingSection[];
    sectionTypes: LandingSectionTypeOption[];
};

// pages/technology/TechnologyIndex.vue
export type TechnologyIndexProps = { technologies: Technology[] };

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

// pages/studio/AboutPage.vue
export type AboutPageProps = {
    clients: Client[];
    stats: StudioStats;
};

// pages/founder/FounderPage.vue
export type FounderPageProps = {
    content: FounderContent;
    skillGroups: SkillCategory[];
    experiences: Experience[];
    educations: Education[];
    projects: ProjectCard[];
    stats: FounderStats;
};

// pages/studio/HomePage.vue
export type HomePageProps = {
    content: StudioContent;
    projects: ProjectCard[];
    totalProjects: number;
    founderProjects: ProjectCard[];
    totalFounderProjects: number;
    clients: Client[];
    sections: LandingSection[];
    stats: StudioStats;
};

// pages/landingpage/ShowLandingPage.vue
export type ShowLandingPageProject = {
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
export type ShowLandingPageProps = {
    project: ShowLandingPageProject;
    landingPage: ProjectLandingPage;
    sections: LandingSection[];
};

// pages/project/ProjectArchive.vue
export type ProjectArchiveProps = {
    projects: ProjectCard[];
    filters: { tech: string | null; owner: 'studio' | 'founder' | null };
};

// pages/project/ShowProject.vue
export type ShowProjectProps = {
    project: ProjectDetail;
    related: ProjectCard[];
};

// pages/settings/Security.vue
export type SettingsSecurityProps = {
    passwordRules: string;
} & ManageTwoFactorProps;

// Emitted events

// components/Education/EducationForm.vue
export type EducationFormEmits = { saved: []; cancel: [] };

// components/Experience/ExperienceForm.vue
export type ExperienceFormEmits = { saved: []; cancel: [] };

// components/Skill/SkillForm.vue
export type SkillFormEmits = { saved: []; cancel: [] };
