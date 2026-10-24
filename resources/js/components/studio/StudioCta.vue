<script setup lang="ts">
import { ArrowDown, Mail } from '@lucide/vue';
import { computed } from 'vue';
import MagneticButton from '@/components/motion/MagneticButton.vue';
import CircuitBackground from '@/components/site/CircuitBackground.vue';
import Reveal from '@/components/site/Reveal.vue';
import { useStudioContent } from '@/composables/useStudioContent';
import type { Company } from '@/types';

/**
 * The close: a gradient panel with the ask, directly above the form it
 * points at.
 */
type Props = {
    company: Company;
};

const props = defineProps<Props>();

const content = useStudioContent(props.company, 'cta');

const statusText = computed(() =>
    props.company.accepting_projects
        ? content.value.status_available ||
          props.company.status_text ||
          'Accepting new projects'
        : content.value.status_unavailable,
);
</script>

<template>
    <section id="start" class="scroll-mt-24 py-10 lg:py-16">
        <div class="site-container">
            <Reveal>
                <div
                    class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-[var(--signal)] to-[var(--grad-to)] px-6 py-16 text-center text-white shadow-[0_40px_80px_-40px_var(--brand-glow)] sm:px-12 lg:py-24"
                >
                    <div
                        class="pointer-events-none absolute inset-0"
                        aria-hidden="true"
                    >
                        <div
                            class="ui-blob -top-32 -left-24 size-96 bg-white/30"
                        />
                        <div
                            class="ui-blob -right-20 -bottom-32 size-96 bg-white/20 [animation-delay:-8s]"
                        />
                        <!-- White circuit traces over the gradient -->
                        <div
                            class="absolute inset-0 opacity-70 [--background:transparent] [--foreground:#ffffff] [--grad-from:#ffffff] [--grad-to:#ffffff]"
                        >
                            <CircuitBackground />
                        </div>
                    </div>

                    <div
                        class="relative mx-auto flex max-w-2xl flex-col items-center gap-6"
                    >
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-xs font-semibold backdrop-blur"
                        >
                            <span
                                :class="
                                    company.accepting_projects
                                        ? 'ui-pulse-dot'
                                        : 'size-2 rounded-full bg-white/60'
                                "
                            />
                            {{ statusText }}
                        </span>
                        <h2 class="ui-display ui-h2">{{ content.title }}</h2>
                        <p class="text-lg leading-relaxed text-white/85">
                            {{ content.description }}
                        </p>
                        <div class="flex flex-wrap justify-center gap-3 pt-2">
                            <MagneticButton>
                                <a
                                    href="#contact"
                                    class="ui-btn bg-white text-[var(--signal)] shadow-lg hover:bg-white/95"
                                >
                                    {{ content.button_label }}
                                    <ArrowDown class="size-4" />
                                </a>
                            </MagneticButton>
                            <a
                                v-if="company.public_email"
                                :href="`mailto:${company.public_email}`"
                                class="ui-btn border border-white/40 text-white hover:bg-white/10"
                            >
                                <Mail class="size-4" />
                                {{ content.email_label }}
                            </a>
                        </div>
                    </div>
                </div>
            </Reveal>
        </div>
    </section>
</template>
