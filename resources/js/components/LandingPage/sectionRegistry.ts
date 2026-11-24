import { defineAsyncComponent } from 'vue';
import type { Component } from 'vue';
import type { LandingSectionType } from '@/types';

/**
 * Maps each section type to its renderer.
 *
 * Every entry is async so the heavy blocks — video, live embed, walkthrough —
 * are separate chunks. A landing page that uses none of them never downloads
 * their code.
 */
const registry: Record<LandingSectionType, Component> = {
    features: defineAsyncComponent(
        () => import('@/components/LandingPage/Sections/FeaturesSection.vue'),
    ),
    demo_video: defineAsyncComponent(
        () => import('@/components/LandingPage/Sections/DemoVideoSection.vue'),
    ),
    demo_embed: defineAsyncComponent(
        () => import('@/components/LandingPage/Sections/DemoEmbedSection.vue'),
    ),
    demo_walkthrough: defineAsyncComponent(
        () =>
            import('@/components/LandingPage/Sections/WalkthroughSection.vue'),
    ),
    stats: defineAsyncComponent(
        () => import('@/components/LandingPage/Sections/StatsSection.vue'),
    ),
    gallery: defineAsyncComponent(
        () => import('@/components/LandingPage/Sections/GallerySection.vue'),
    ),
    testimonials: defineAsyncComponent(
        () =>
            import('@/components/LandingPage/Sections/TestimonialsSection.vue'),
    ),
    faq: defineAsyncComponent(
        () => import('@/components/LandingPage/Sections/FaqSection.vue'),
    ),
    tech: defineAsyncComponent(
        () => import('@/components/LandingPage/Sections/TechSection.vue'),
    ),
    cta: defineAsyncComponent(
        () => import('@/components/LandingPage/Sections/CtaSection.vue'),
    ),
    richtext: defineAsyncComponent(
        () => import('@/components/LandingPage/Sections/RichTextSection.vue'),
    ),
};

/**
 * Resolve a section renderer. Returns null for an unknown type so a payload
 * written by a newer deploy degrades to "skip that block" rather than throwing
 * and taking the whole page down.
 */
export function resolveSection(type: LandingSectionType): Component | null {
    return registry[type] ?? null;
}
