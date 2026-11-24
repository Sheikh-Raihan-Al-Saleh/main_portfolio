export type Appearance = 'light' | 'dark' | 'system';
export type ResolvedAppearance = 'light' | 'dark';

export type AdminTheme = 'colorful' | 'aesthetic' | 'modern';
export type ThemeAppearance = Appearance | AdminTheme;

export type AppVariant = 'header' | 'sidebar';

export type FlashToast = {
    type: 'success' | 'info' | 'warning' | 'error';
    message: string;
};

/**
 * Parameters of a named route: one value for a single `{parameter}`, a list
 * in order, or an object by name. Keys that are not route parameters become
 * the query string ({ filter: 'unread' } -> ?filter=unread).
 */
export type RouteParameters =
    string | number | Array<string | number> | Record<string, unknown>;

/** The attributes an Inertia <Form> needs to submit to a named route. */
export type RouteFormAttributes = {
    action: string;
    method: 'get' | 'post';
};

/** PHP's upload limits in bytes, shared as `uploadLimits`; null = unlimited. */
export type UploadLimits = {
    file: number | null;
    request: number | null;
};

/** A piece of a headline; `highlight` parts are set in the accent italic. */
export type TextPart = { text: string; highlight: boolean };
