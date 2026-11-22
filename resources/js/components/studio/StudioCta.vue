<script setup lang="ts">
import { ArrowDown, Mail } from '@lucide/vue';
import { computed } from 'vue';
import MagneticButton from '@/components/motion/MagneticButton.vue';
import Reveal from '@/components/site/Reveal.vue';
import { useStudioContent } from '@/composables/useStudioContent';
import type { Company } from '@/types';

/**
 * The close, as a poster in the studio's accent: the status label, the ask
 * in giant condensed type, the lead and two buttons, directly above the form
 * it points at.
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
    <section id="start" class="scroll-mt-24">
        <Reveal>
            <div
                class="relative overflow-hidden bg-[var(--accent-fill)] text-[var(--accent-ink)]"
            >
                <div
                    class="site-container grid gap-10 py-20 lg:grid-cols-12 lg:py-28"
                >
                    <p
                        class="ui-label inline-flex items-center gap-2 lg:col-span-12"
                    >
                        <span
                            :class="
                                company.accepting_projects
                                    ? 'ui-pulse-dot'
                                    : 'size-2 rounded-full bg-current opacity-60'
                            "
                        />
                        {{ statusText }}
                    </p>

                    <h2
                        class="ui-display text-[clamp(3.5rem,11vw,11rem)] lg:col-span-12"
                    >
                        {{ content.title }}
                    </h2>

                    <p
                        class="max-w-xl text-lg leading-relaxed opacity-85 lg:col-span-6"
                    >
                        {{ content.description }}
                    </p>

                    <div
                        class="flex flex-wrap items-end gap-3 lg:col-span-6 lg:justify-end"
                    >
                        <MagneticButton>
                            <a
                                href="#contact"
                                class="ui-btn bg-[var(--accent-ink)] text-[var(--accent-fill)] hover:opacity-90"
                            >
                                {{ content.button_label }}
                                <ArrowDown class="size-4" />
                            </a>
                        </MagneticButton>
                        <a
                            v-if="company.public_email"
                            :href="`mailto:${company.public_email}`"
                            class="ui-btn border border-current hover:bg-[var(--accent-ink)] hover:text-[var(--accent-fill)]"
                        >
                            <Mail class="size-4" />
                            {{ content.email_label }}
                        </a>
                    </div>
                </div>
            </div>
        </Reveal>
    </section>
</template>
