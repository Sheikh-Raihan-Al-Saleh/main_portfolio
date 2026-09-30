<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight } from '@lucide/vue';
import { computed } from 'vue';
import ScrollReveal from '@/components/motion/ScrollReveal.vue';
import { getInitials } from '@/composables/useInitials';
import { useStudioContent } from '@/composables/useStudioContent';
import type { Company, Profile } from '@/types';

/**
 * The seam between the studio and the founder.
 *
 * The site presents two identities and a reader arriving from the studio side
 * should understand why there is a second one — and which of the two they are
 * hiring. It is deliberately *not* the founder card: no portrait beyond the
 * avatar chip, no biography, no contact details. All copy renders from
 * `company.studio_content.founder_bridge`, edited in the admin's Home
 * Sections page; `{{name}}`/`{{role}}` tokens resolve to the live records.
 */
type Props = {
    company: Company;
    profile: Profile;
};

const props = defineProps<Props>();

const content = useStudioContent(props.company, 'founder_bridge');

const founderName = computed(() => props.profile.name ?? 'The founder');

const founderRole = computed(
    () =>
        props.profile.roles?.[0] ??
        props.profile.headline ??
        'Software engineer',
);

/** Resolve the editor's {{token}}s against the company and profile. */
function resolve(text: string, role: string): string {
    return text
        .replaceAll('{{name}}', props.company.name)
        .replaceAll('{{role}}', role);
}

const initials = computed(() => getInitials(founderName.value));

const studioHeading = computed(() => resolve(content.value.studio_heading, ''));
const studioText = computed(() => content.value.studio_text);
const founderHeading = computed(() => founderName.value);
const founderText = computed(() =>
    resolve(content.value.founder_text, founderRole.value),
);
</script>

<template>
    <section
        class="bg-noise cv-auto relative scroll-mt-20 border-t border-border py-14 sm:py-16"
    >
        <div class="container-laravel section-dashed-xl">
            <ScrollReveal :distance="24">
                <div
                    class="grid items-center gap-6 lg:grid-cols-[1fr_auto_1fr] lg:gap-4"
                >
                    <!-- The company -->
                    <div class="studio-panel studio-panel-pad">
                        <p
                            class="font-mono text-[11px] tracking-[0.18em] text-brand uppercase"
                        >
                            {{ content.studio_label }}
                        </p>
                        <h3
                            class="mt-2 text-lg font-semibold tracking-tight text-balance"
                        >
                            {{ studioHeading }}
                        </h3>
                        <p
                            class="mt-2 text-sm leading-relaxed text-pretty text-muted-foreground"
                        >
                            {{ studioText }}
                        </p>
                        <Link
                            href="/about"
                            class="group mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-brand"
                        >
                            {{ content.studio_link }}
                            <ArrowUpRight
                                class="size-3.5 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                                aria-hidden="true"
                            />
                        </Link>
                    </div>

                    <!-- The seam -->
                    <div
                        class="relative hidden items-center lg:flex lg:flex-col"
                        aria-hidden="true"
                    >
                        <div class="studio-bridge-link w-24 xl:w-32" />
                        <div class="studio-bridge-node" />
                    </div>

                    <!-- The engineer -->
                    <div class="studio-panel studio-panel-pad">
                        <p
                            class="font-mono text-[11px] tracking-[0.18em] text-muted-foreground uppercase"
                        >
                            {{ content.founder_label }}
                        </p>
                        <div class="mt-2 flex items-center gap-3">
                            <img
                                v-if="profile.avatar_url"
                                :src="profile.avatar_url"
                                :alt="founderName"
                                loading="lazy"
                                class="size-9 shrink-0 rounded-lg object-cover"
                            />
                            <span
                                v-else
                                class="grid size-9 shrink-0 place-items-center rounded-lg bg-foreground font-display text-xs font-bold text-background"
                            >
                                {{ initials }}
                            </span>

                            <h3 class="text-lg font-semibold tracking-tight">
                                {{ founderHeading }}
                            </h3>
                        </div>
                        <p
                            class="mt-2 text-sm leading-relaxed text-pretty text-muted-foreground"
                        >
                            {{ founderText }}
                        </p>
                        <Link
                            href="/founder"
                            class="group mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-brand"
                        >
                            {{ content.founder_link }}
                            <ArrowUpRight
                                class="size-3.5 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                                aria-hidden="true"
                            />
                        </Link>
                    </div>
                </div>
            </ScrollReveal>
        </div>
    </section>
</template>
