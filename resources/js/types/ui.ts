export type Appearance = 'light' | 'dark' | 'system';
export type ResolvedAppearance = 'light' | 'dark';

export type AdminTheme = 'colorful' | 'aesthetic' | 'modern';
export type ThemeAppearance = Appearance | AdminTheme;

export type AppVariant = 'header' | 'sidebar';

export type FlashToast = {
    type: 'success' | 'info' | 'warning' | 'error';
    message: string;
};
