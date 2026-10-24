import type { Component } from 'vue';

/** One tab of a FormTabs form. */
export type FormTab = {
    id: string;
    label: string;
    description?: string;
    icon?: Component;
    /** Field names (or prefixes, e.g. `socials`) that live on this tab. */
    fields?: string[];
};
