/**
 * Admin form types: form tabs and the landing-block field schema.
 */
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

/**
 * Landing block editor: one field of a block's `data` payload
 * (see components/admin/landing/sectionSchema.ts).
 */
export type FieldKind =
    | 'text'
    | 'textarea'
    | 'number'
    | 'select'
    | 'switch'
    | 'media'
    | 'repeater'
    | 'stringList';

export type Field = {
    key: string;
    label: string;
    kind: FieldKind;
    placeholder?: string;
    hint?: string;
    required?: boolean;
    /** select only */
    options?: { value: string; label: string }[];
    /** media only: the upload directory the backend accepts. */
    directory?: 'steps' | 'gallery' | 'video' | 'poster' | 'avatar';
    /** media only: accept video files as well as images. */
    video?: boolean;
    /** repeater only */
    fields?: Field[];
    /** repeater only: label for the add button. */
    addLabel?: string;
};

export type SectionSchema = {
    /** Short explanation shown at the top of the editor. */
    description: string;
    fields: Field[];
    /** Payload used when adding a fresh section of this type. */
    defaults: Record<string, unknown>;
};
