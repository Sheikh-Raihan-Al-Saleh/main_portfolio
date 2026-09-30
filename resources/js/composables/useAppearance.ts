import type { ComputedRef, Ref } from 'vue';
import { computed, onMounted, ref } from 'vue';
import type { Appearance, ResolvedAppearance, ThemeAppearance } from '@/types';

export type { Appearance, ResolvedAppearance, ThemeAppearance };

const ADMIN_THEMES = ['colorful', 'aesthetic', 'modern'] as const;

export type UseAppearanceReturn = {
    appearance: Ref<ThemeAppearance>;
    resolvedAppearance: ComputedRef<ResolvedAppearance>;
    updateAppearance: (value: ThemeAppearance) => void;
};

export function updateTheme(value: ThemeAppearance): void {
    if (typeof window === 'undefined') {
        return;
    }

    const isDark =
        value === 'system'
            ? window.matchMedia('(prefers-color-scheme: dark)').matches
            : value === 'dark';

    const isAdminTheme = (ADMIN_THEMES as readonly string[]).includes(value);

    document.documentElement.classList.toggle('dark', isDark);

    for (const theme of ADMIN_THEMES) {
        document.documentElement.classList.toggle(theme, value === theme);
    }

    // Admin color palettes are defined for light surfaces only.
    if (isAdminTheme) {
        document.documentElement.classList.remove('dark');
    }
}

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;

    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const mediaQuery = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

const getStoredAppearance = (): ThemeAppearance | null => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem('appearance') as ThemeAppearance | null;
};

const prefersDark = (): boolean => {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
};

const handleSystemThemeChange = () => {
    const currentAppearance = getStoredAppearance();

    updateTheme(currentAppearance || 'system');
};

export function initializeTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }

    // Initialize theme from saved preference or default to system...
    const savedAppearance = getStoredAppearance();
    updateTheme(savedAppearance || 'system');

    // Set up system theme change listener...
    mediaQuery()?.addEventListener('change', handleSystemThemeChange);
}

const appearance = ref<ThemeAppearance>('system');

export function useAppearance(): UseAppearanceReturn {
    onMounted(() => {
        const savedAppearance = getStoredAppearance();

        if (savedAppearance) {
            appearance.value = savedAppearance;
        }
    });

    const resolvedAppearance = computed<ResolvedAppearance>(() => {
        if (appearance.value === 'system') {
            return prefersDark() ? 'dark' : 'light';
        }

        if (appearance.value === 'dark') {
            return 'dark';
        }

        return 'light';
    });

    function updateAppearance(value: ThemeAppearance) {
        appearance.value = value;

        // Store in localStorage for client-side persistence...
        localStorage.setItem('appearance', value);

        // Store in cookie for SSR...
        setCookie('appearance', value);

        updateTheme(value);
    }

    return {
        appearance,
        resolvedAppearance,
        updateAppearance,
    };
}
