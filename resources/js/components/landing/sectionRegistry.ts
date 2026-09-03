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
        () => import('@/components/landing/sections/FeaturesSection.vue'),
    ),
    demo_video: defineAsyncComponent(
        () => import('@/components/landing/sections/DemoVideoSection.vue'),
    ),
    demo_embed: defineAsyncComponent(
        () => import('@/components/landing/sections/DemoEmbedSection.vue'),
    ),
    demo_walkthrough: defineAsyncComponent(
        () => import('@/components/landing/sections/WalkthroughSection.vue'),
    ),
    stats: defineAsyncComponent(
        () => import('@/components/landing/sections/StatsSection.vue'),
    ),
    gallery: defineAsyncComponent(
        () => import('@/components/landing/sections/GallerySection.vue'),
    ),
    testimonials: defineAsyncComponent(
        () => import('@/components/landing/sections/TestimonialsSection.vue'),
    ),
    faq: defineAsyncComponent(
        () => import('@/components/landing/sections/FaqSection.vue'),
    ),
    tech: defineAsyncComponent(
        () => import('@/components/landing/sections/TechSection.vue'),
    ),
    cta: defineAsyncComponent(
        () => import('@/components/landing/sections/CtaSection.vue'),
    ),
    richtext: defineAsyncComponent(
        () => import('@/components/landing/sections/RichTextSection.vue'),
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
