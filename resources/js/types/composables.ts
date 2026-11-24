import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { MaybeComputedElementRef } from '@vueuse/core';
import type { ComputedRef, DeepReadonly, Ref } from 'vue';
import type {
    Client,
    Education,
    Experience,
    Project,
    Skill,
} from './portfolio';
import type { ResolvedAppearance, ThemeAppearance } from './ui';

/**
 * Types of the composables in resources/js/composables: what each one
 * returns, and the options it accepts.
 */

/** Any record with a numeric primary key (projects, clients, skills...). */
export type Identifiable = { id: number };

export type UseAppearanceReturn = {
    appearance: Ref<ThemeAppearance>;
    resolvedAppearance: ComputedRef<ResolvedAppearance>;
    updateAppearance: (value: ThemeAppearance) => void;
};

export type UseCurrentUrlReturn = {
    currentUrl: DeepReadonly<ComputedRef<string>>;
    isCurrentUrl: (
        urlToCheck: NonNullable<InertiaLinkProps['href']>,
        currentUrl?: string,
        startsWith?: boolean,
    ) => boolean;
    isCurrentOrParentUrl: (
        urlToCheck: NonNullable<InertiaLinkProps['href']>,
        currentUrl?: string,
    ) => boolean;
    whenCurrentUrl: <T, F = null>(
        urlToCheck: NonNullable<InertiaLinkProps['href']>,
        ifTrue: T,
        ifFalse?: F,
    ) => T | F;
};

export type UseInitialsReturn = {
    getInitials: (fullName?: string) => string;
};

export type UseTwoFactorAuthReturn = {
    qrCodeSvg: Ref<string | null>;
    manualSetupKey: Ref<string | null>;
    recoveryCodesList: Ref<string[]>;
    errors: Ref<string[]>;
    hasSetupData: ComputedRef<boolean>;
    clearSetupData: () => void;
    clearErrors: () => void;
    clearTwoFactorAuthData: () => void;
    fetchQrCode: () => Promise<void>;
    fetchSetupKey: () => Promise<void>;
    fetchSetupData: () => Promise<void>;
    fetchRecoveryCodes: () => Promise<void>;
};

export type UseReorderReturn<T extends Identifiable> = {
    /** The list in its current (possibly just moved) order. */
    items: Ref<T[]>;
    /** Move the item at `index` one place up (-1) or down (1) and save. */
    move: (index: number, direction: -1 | 1) => void;
    /** True while the new order is being saved. */
    saving: Ref<boolean>;
};

export type UseEditDialogReturn<T> = {
    /** Whether the create/edit dialog is open. */
    dialogOpen: Ref<boolean>;
    /** The record being edited, or null when the dialog creates a new one. */
    editing: Ref<T | null>;
    /** Open the dialog with an empty form. */
    openCreate: () => void;
    /** Open the dialog with the given record's values. */
    openEdit: (record: T) => void;
    /** Close the dialog. */
    closeDialog: () => void;
};

export type UseDebouncedSearchReturn = {
    /** The search text, bound to the search input with v-model. */
    search: Ref<string>;
};

/** What every reorderable admin list composable shares. */
type ReorderableList = {
    /** Move the item at `index` one place up (-1) or down (1) and save. */
    move: (index: number, direction: -1 | 1) => void;
    /** True while the new order is being saved. */
    saving: Ref<boolean>;
};

export type UseProjectListReturn = ReorderableList & {
    /** The projects in their current (possibly just moved) order. */
    projectList: Ref<Project[]>;
    /** The search text, bound to the search input with v-model. */
    search: Ref<string>;
};

export type UseClientListReturn = ReorderableList & {
    /** The clients in their current (possibly just moved) order. */
    clientList: Ref<Client[]>;
    /** The search text, bound to the search input with v-model. */
    search: Ref<string>;
    /** How many clients are shown on the public site. */
    visibleCount: ComputedRef<number>;
};

export type UseSkillListReturn = ReorderableList & {
    /** Every skill of every category, in its current order. */
    skillList: Ref<Skill[]>;
    /** Category name by category ID. */
    categoryNames: ComputedRef<Record<number, string>>;
    /** Category choices for the skill form's select. */
    categoryOptions: ComputedRef<Array<{ value: number; label: string }>>;
};

export type UseExperienceListReturn = ReorderableList & {
    /** The roles in their current (possibly just moved) order. */
    experienceList: Ref<Experience[]>;
};

export type UseEducationListReturn = ReorderableList & {
    /** The education entries in their current (possibly just moved) order. */
    educationList: Ref<Education[]>;
};

export type ScrollProgressOptions = {
    /** Element to track. Omit to track the whole page. */
    target?: MaybeComputedElementRef;
    /**
     * Where tracking starts and ends relative to the target and viewport.
     * Defaults to "from the moment the element's top hits the viewport bottom
     * until its bottom hits the viewport top".
     */
    offset?: ['start end', 'end start'] | [string, string];
    /** Smooth the value with a spring. Off by default — scrubbing should be exact. */
    smooth?: boolean;
};

/** Chrome's install prompt event (not yet in TypeScript's DOM types). */
export type BeforeInstallPromptEvent = Event & {
    prompt: () => Promise<void>;
    userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>;
};

export type UsePwaReturn = {
    /** True while the browser offers to install the admin as an app. */
    canInstall: Readonly<Ref<boolean>>;
    /** Opens the browser's install dialog. */
    install: () => Promise<void>;
};
