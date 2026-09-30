<script setup lang="ts">
import { ArrowRight, Mail, Sparkles } from '@lucide/vue';
import { computed } from 'vue';
import FloatingObject from '@/components/motion/FloatingObject.vue';
import MagneticButton from '@/components/motion/MagneticButton.vue';
import ParallaxLayer from '@/components/motion/ParallaxLayer.vue';
import PerspectiveGrid from '@/components/motion/PerspectiveGrid.vue';
import ScrollReveal from '@/components/motion/ScrollReveal.vue';
import { Button } from '@/components/ui/button';
import { useStudioContent } from '@/composables/useStudioContent';
import type { Company } from '@/types';

/**
 * The closing call to action.
 *
 * Everything before this has been building an argument; this is where the
 * page stops explaining and asks. The treatment is deliberately calmer than
 * the hero — a receding grid, one line of type, one button. All copy renders
 * from `company.studio_content.cta`, edited in the admin's Home Sections page.
 */
type Props = {
    company: Company;
};

const props = defineProps<Props>();

const content = useStudioContent(props.company, 'cta');

/** The honest status line, so the button is never a false promise. */
const statusText = computed(() =>
    props.company.accepting_projects
        ? (content.value.status_available ||
            props.company.status_text ||
            'Accepting new projects')
        : content.value.status_unavailable,
);

/** Split the heading on its highlight so the accent can be painted. */
const titleParts = computed<{ text: string; accent: boolean }[]>(() => {
    const highlight = content.value.highlight;

    if (!highlight || !content.value.title.includes(highlight)) {
        return [{ text: content.value.title, accent: false }];
    }

    return content.value.title
        .split(highlight)
        .flatMap((part, index, parts) => {
            const segments = [{ text: part, accent: false }];

            if (index < parts.length - 1) {
                segments.push({ text: highlight, accent: true });
            }

            return segments;
        });
});
</script>

<template>
    <section
        id="start"
        class="studio-cta bg-noise relative scroll-mt-20"
        aria-labelledby="studio-cta-title"
    >
        <PerspectiveGrid
            :size="62"
            :tilt="78"
            :drift="0"
            fade="both"
            class="opacity-50"
        />

        <!-- Two accent objects, at different depths, to keep the band alive
             without a single frame of JavaScript. -->
        <FloatingObject
            class="absolute top-16 left-[8%] hidden lg:block"
            :amplitude="16"
            :duration="9"
            :z="-40"
        >
            <span class="block size-2 rounded-full bg-brand/70" />
        </FloatingObject>
        <FloatingObject
            class="absolute right-[10%] bottom-20 hidden lg:block"
            :amplitude="20"
            :duration="11"
            :delay="-3"
            :z="-80"
        >
            <span class="block size-3 rounded-full bg-brand/40" />
        </FloatingObject>

        <div class="container-laravel section-dashed-xl relative py-20 sm:py-28">
            <ParallaxLayer :distance="30" :scale-to="1.015">
                <ScrollReveal :distance="36" :blur="8">
                    <div class="studio-cta-panel">
                        <p
                            class="inline-flex items-center gap-2 rounded-full border border-brand/20 bg-brand/5 px-3 py-1.5 font-mono text-[11px] tracking-[0.18em] text-brand uppercase"
                        >
                            <Sparkles class="size-3" aria-hidden="true" />
                            {{ content.eyebrow }}
                        </p>

                        <h2
                            id="studio-cta-title"
                            class="mx-auto mt-6 max-w-3xl font-display text-3xl leading-[1.08] font-bold tracking-tight text-balance sm:text-5xl"
                        >
                            <template
                                v-for="(part, i) in titleParts"
                                :key="i"
                            >
                                <span
                                    v-if="part.accent"
                                    class="text-gradient-accent"
                                >{{ part.text }}</span>
                                <span v-else>{{ part.text }}</span>
                            </template>
                        </h2>

                        <p
                            class="mx-auto mt-5 max-w-xl leading-relaxed text-pretty text-muted-foreground"
                        >
                            {{ content.description }}
                        </p>

                        <div
                            class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row"
                        >
                            <MagneticButton>
                                <Button
                                    as-child
                                    size="lg"
                                    class="btn-laravel-primary rounded-lg px-7 shadow-lg shadow-brand/10 transition-shadow hover:shadow-brand/25"
                                >
                                    <a href="#contact">
                                        {{ content.button_label }}
                                        <ArrowRight
                                            class="size-4"
                                            aria-hidden="true"
                                        />
                                    </a>
                                </Button>
                            </MagneticButton>

                            <MagneticButton v-if="company.public_email">
                                <Button
                                    as-child
                                    size="lg"
                                    variant="outline"
                                    class="btn-laravel rounded-lg px-7"
                                >
                                    <a :href="`mailto:${company.public_email}`">
                                        <Mail
                                            class="size-4"
                                            aria-hidden="true"
                                        />
                                        {{ content.email_label }}
                                    </a>
                                </Button>
                            </MagneticButton>
                        </div>

                        <p
                            class="mt-6 inline-flex items-center gap-2 font-mono text-xs text-muted-foreground"
                        >
                            <span
                                class="size-1.5 rounded-full"
                                :class="
                                    company.accepting_projects
                                        ? 'bg-emerald-500 shadow-[0_0_6px_2px_rgba(16,185,129,0.4)]'
                                        : 'bg-muted-foreground'
                                "
                                aria-hidden="true"
                            />
                            {{ statusText }}
                        </p>
                    </div>
                </ScrollReveal>
            </ParallaxLayer>
        </div>
    </section>
</template>
