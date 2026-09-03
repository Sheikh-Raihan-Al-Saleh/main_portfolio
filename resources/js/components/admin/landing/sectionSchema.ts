import type { LandingSectionType } from '@/types';

/**
 * Declarative description of each section type's `data` payload.
 *
 * The admin editor renders fields from this rather than from eleven bespoke
 * forms. It mirrors App\Enums\LandingSectionType::rules() — that enum is what
 * actually validates the payload; this is only the UI for producing it.
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

export const sectionSchemas: Record<LandingSectionType, SectionSchema> = {
    features: {
        description: 'A grid of short feature cards.',
        fields: [
            {
                key: 'items',
                label: 'Features',
                kind: 'repeater',
                addLabel: 'Add feature',
                fields: [
                    {
                        key: 'icon',
                        label: 'Lucide icon name',
                        kind: 'text',
                        placeholder: 'Zap',
                        hint: 'Any icon name from lucide.dev. Falls back to a sparkle.',
                    },
                    {
                        key: 'title',
                        label: 'Title',
                        kind: 'text',
                        required: true,
                    },
                    { key: 'text', label: 'Description', kind: 'textarea' },
                ],
            },
        ],
        defaults: { items: [{ icon: '', title: '', text: '' }] },
    },

    demo_video: {
        description:
            'A video demo. Third-party players load only after the visitor clicks play.',
        fields: [
            {
                key: 'provider',
                label: 'Source',
                kind: 'select',
                required: true,
                options: [
                    { value: 'upload', label: 'Uploaded file' },
                    { value: 'youtube', label: 'YouTube' },
                    { value: 'vimeo', label: 'Vimeo' },
                ],
            },
            {
                key: 'video_url',
                label: 'Video URL',
                kind: 'text',
                placeholder: 'https://www.youtube.com/watch?v=...',
                hint: 'For YouTube and Vimeo.',
            },
            {
                key: 'video_path',
                label: 'Video file',
                kind: 'media',
                directory: 'video',
                video: true,
                hint: 'For the uploaded-file source. MP4 or WebM.',
            },
            {
                key: 'poster_path',
                label: 'Poster image',
                kind: 'media',
                directory: 'poster',
                hint: 'Shown before playback starts.',
            },
            { key: 'caption', label: 'Caption', kind: 'text' },
            {
                key: 'autoplay',
                label: 'Autoplay muted when scrolled into view',
                kind: 'switch',
                hint: 'Uploaded files only. Ignored under reduced motion.',
            },
        ],
        defaults: {
            provider: 'youtube',
            video_url: '',
            video_path: null,
            poster_path: null,
            caption: '',
            autoplay: false,
        },
    },

    demo_embed: {
        description:
            'The live application in a sandboxed frame. Loads only on click.',
        fields: [
            {
                key: 'url',
                label: 'Demo URL',
                kind: 'text',
                required: true,
                placeholder: 'https://demo.example.com',
                hint: 'Must be https.',
            },
            {
                key: 'aspect',
                label: 'Aspect ratio',
                kind: 'select',
                required: true,
                options: [
                    { value: '16/9', label: 'Widescreen (16:9)' },
                    { value: '4/3', label: 'Classic (4:3)' },
                    { value: 'mobile', label: 'Mobile (9:16)' },
                ],
            },
            {
                key: 'chrome',
                label: 'Frame',
                kind: 'select',
                required: true,
                options: [
                    { value: 'browser', label: 'Browser window' },
                    { value: 'device', label: 'Phone' },
                    { value: 'none', label: 'No frame' },
                ],
            },
            {
                key: 'allow_fullscreen',
                label: 'Allow fullscreen',
                kind: 'switch',
            },
        ],
        defaults: {
            url: '',
            aspect: '16/9',
            chrome: 'browser',
            allow_fullscreen: true,
        },
    },

    demo_walkthrough: {
        description:
            'Screenshots that advance as the visitor scrolls, with a caption per step.',
        fields: [
            {
                key: 'frame',
                label: 'Frame',
                kind: 'select',
                required: true,
                options: [
                    { value: 'browser', label: 'Browser window' },
                    { value: 'laptop', label: 'Laptop' },
                    { value: 'phone', label: 'Phone' },
                ],
            },
            {
                key: 'steps',
                label: 'Steps',
                kind: 'repeater',
                addLabel: 'Add step',
                fields: [
                    {
                        key: 'image_path',
                        label: 'Screenshot',
                        kind: 'media',
                        directory: 'steps',
                        required: true,
                    },
                    {
                        key: 'title',
                        label: 'Step title',
                        kind: 'text',
                        required: true,
                    },
                    { key: 'caption', label: 'Caption', kind: 'textarea' },
                ],
            },
        ],
        defaults: {
            frame: 'browser',
            steps: [{ image_path: '', title: '', caption: '' }],
        },
    },

    stats: {
        description: 'Headline numbers that count up when scrolled into view.',
        fields: [
            {
                key: 'items',
                label: 'Stats',
                kind: 'repeater',
                addLabel: 'Add stat',
                fields: [
                    {
                        key: 'value',
                        label: 'Value',
                        kind: 'number',
                        required: true,
                    },
                    {
                        key: 'suffix',
                        label: 'Suffix',
                        kind: 'text',
                        placeholder: '%',
                    },
                    {
                        key: 'label',
                        label: 'Label',
                        kind: 'text',
                        required: true,
                    },
                ],
            },
        ],
        defaults: { items: [{ value: 0, suffix: '', label: '' }] },
    },

    gallery: {
        description: 'An image grid with a lightbox.',
        fields: [
            {
                key: 'images',
                label: 'Images',
                kind: 'repeater',
                addLabel: 'Add image',
                fields: [
                    {
                        key: '',
                        label: 'Image',
                        kind: 'media',
                        directory: 'gallery',
                        required: true,
                    },
                ],
            },
        ],
        defaults: { images: [] },
    },

    testimonials: {
        description: 'Quotes from clients or users.',
        fields: [
            {
                key: 'items',
                label: 'Testimonials',
                kind: 'repeater',
                addLabel: 'Add testimonial',
                fields: [
                    {
                        key: 'quote',
                        label: 'Quote',
                        kind: 'textarea',
                        required: true,
                    },
                    {
                        key: 'name',
                        label: 'Name',
                        kind: 'text',
                        required: true,
                    },
                    { key: 'role', label: 'Role', kind: 'text' },
                    {
                        key: 'avatar_path',
                        label: 'Avatar',
                        kind: 'media',
                        directory: 'avatar',
                    },
                ],
            },
        ],
        defaults: {
            items: [{ quote: '', name: '', role: '', avatar_path: null }],
        },
    },

    faq: {
        description: 'Collapsible questions and answers.',
        fields: [
            {
                key: 'items',
                label: 'Questions',
                kind: 'repeater',
                addLabel: 'Add question',
                fields: [
                    {
                        key: 'question',
                        label: 'Question',
                        kind: 'text',
                        required: true,
                    },
                    {
                        key: 'answer',
                        label: 'Answer',
                        kind: 'textarea',
                        required: true,
                    },
                ],
            },
        ],
        defaults: { items: [{ question: '', answer: '' }] },
    },

    tech: {
        description:
            'The technology list. Scrolls as a marquee past eight items.',
        fields: [
            {
                key: 'items',
                label: 'Technologies',
                kind: 'stringList',
                placeholder: 'Type a name and press Enter',
            },
        ],
        defaults: { items: [] },
    },

    cta: {
        description: 'A closing call to action.',
        fields: [
            {
                key: 'label',
                label: 'Button label',
                kind: 'text',
                required: true,
            },
            {
                key: 'url',
                label: 'Button URL',
                kind: 'text',
                required: true,
                hint: 'Start with / for an internal link.',
            },
            { key: 'note', label: 'Small print', kind: 'text' },
        ],
        defaults: { label: '', url: '/#contact', note: '' },
    },

    richtext: {
        description: 'Free-form markdown. Raw HTML is stripped when rendered.',
        fields: [
            {
                key: 'markdown',
                label: 'Markdown',
                kind: 'textarea',
                required: true,
            },
        ],
        defaults: { markdown: '' },
    },
};
