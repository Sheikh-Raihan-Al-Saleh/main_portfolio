<script setup lang="ts">
import { Mail, MessageCircle, Sparkles } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import ContactForm from '@/components/portfolio/ContactForm.vue';
import SectionHeading from '@/components/portfolio/SectionHeading.vue';
import SocialIcon from '@/components/portfolio/SocialIcon.vue';
import { useStudioContent } from '@/composables/useStudioContent';
import { fadeUp, inViewOnce, stagger } from '@/lib/motion';
import type { Company } from '@/types';

type Props = {
    company: Company;
};

const props = defineProps<Props>();

const content = useStudioContent(props.company, 'contact');

const socialLinks = computed(() => {
    const socials = props.company.socials ?? {};

    return [
        { href: socials.github, name: 'github' as const, label: 'github' },
        {
            href: socials.linkedin,
            name: 'linkedin' as const,
            label: 'linkedin',
        },
        { href: socials.x, name: 'x' as const, label: 'x' },
        { href: socials.website, name: 'website' as const, label: 'website' },
    ].filter((link) => typeof link.href === 'string' && link.href.length > 0);
});

const whatsappUrl = computed(() => {
    const phone = props.company.phone?.replace(/[^0-9]/g, '');

    return phone ? `https://wa.me/${phone}` : null;
});

const statusText = computed(
    () =>
        props.company.status_text ??
        (props.company.accepting_projects
            ? 'Accepting new projects'
            : 'Not taking new work right now'),
);

const intro = computed(() =>
    [content.value.description, props.company.mission]
        .filter(Boolean)
        .join(' '),
);

function handleSuccess() {
    toast.success('Message sent', {
        description: `Thanks for reaching out — ${props.company.name} will get back to you.`,
    });
}
</script>

<template>
    <section
        id="contact"
        class="bg-noise cv-auto relative scroll-mt-20 border-t border-border pt-20 sm:pt-24"
    >
        <div class="corner-dot corner-dot-tl" aria-hidden="true" />
        <div class="corner-dot corner-dot-tr" aria-hidden="true" />

        <div class="container-laravel section-dashed-xl">
            <SectionHeading
                index="07"
                :eyebrow="content.eyebrow"
                :title="content.title"
                :highlight="content.highlight"
                :description="intro"
            />

            <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:gap-12">
                <motion.div
                    :variants="stagger(0.1)"
                    initial="hidden"
                    while-in-view="visible"
                    :in-view-options="inViewOnce"
                    class="rounded-lg bg-neutral-900 p-5 text-white shadow-lg sm:p-6 dark:bg-card dark:text-foreground"
                >
                    <div class="flex flex-col gap-6">
                        <motion.div :variants="fadeUp">
                            <p
                                class="inline-flex items-center gap-2 rounded-lg bg-white/10 px-3 py-1.5 font-mono text-xs text-white/70 dark:bg-muted dark:text-muted-foreground"
                            >
                                <Sparkles class="size-3 text-brand" />
                                <span
                                    class="size-1.5 rounded-full"
                                    :class="
                                        company.accepting_projects
                                            ? 'bg-emerald-500 shadow-[0_0_6px_2px_rgba(16,185,129,0.4)]'
                                            : 'bg-white/40 dark:bg-muted-foreground'
                                    "
                                />
                                {{ statusText }}
                            </p>
                        </motion.div>

                        <motion.div
                            :variants="fadeUp"
                            class="flex flex-col gap-4"
                        >
                            <h3 class="text-2xl font-bold tracking-tight">
                                {{ content.email_heading }}
                            </h3>
                            <a
                                v-if="company.public_email"
                                :href="`mailto:${company.public_email}`"
                                class="group inline-flex flex-wrap items-center gap-3 text-xl font-semibold [overflow-wrap:anywhere] text-brand transition-colors duration-200 hover:text-brand/80 sm:text-2xl"
                            >
                                <span
                                    class="grid size-11 place-items-center rounded-lg bg-white/10 text-brand transition-transform duration-200 group-hover:-translate-y-0.5 dark:bg-muted"
                                >
                                    <Mail class="size-5" />
                                </span>
                                {{ company.public_email }}
                            </a>
                            <p
                                class="text-sm leading-relaxed text-white/60 dark:text-muted-foreground"
                            >
                                {{ content.email_note }}
                            </p>
                        </motion.div>

                        <motion.div v-if="whatsappUrl" :variants="fadeUp">
                            <a
                                :href="whatsappUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2.5 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-2.5 text-sm font-semibold text-emerald-400 transition-all duration-200 hover:bg-emerald-500/20 hover:text-emerald-300 dark:border-emerald-500/20 dark:bg-emerald-500/5 dark:text-emerald-400 dark:hover:bg-emerald-500/10"
                            >
                                <MessageCircle class="size-4" />
                                Chat on WhatsApp
                            </a>
                        </motion.div>

                        <motion.div
                            v-if="socialLinks.length"
                            :variants="fadeUp"
                            class="mt-auto flex flex-wrap gap-2"
                        >
                            <a
                                v-for="link in socialLinks"
                                :key="link.label"
                                :href="link.href ?? undefined"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-lg border border-white/20 bg-white/5 px-4 py-2 text-xs font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:border-brand hover:text-brand dark:border-border dark:bg-card dark:text-foreground dark:hover:text-brand"
                            >
                                <SocialIcon :name="link.name" class="size-4" />
                                {{ link.label }}
                            </a>
                        </motion.div>
                    </div>
                </motion.div>

                <motion.div
                    :variants="fadeUp"
                    initial="hidden"
                    while-in-view="visible"
                    :in-view-options="inViewOnce"
                >
                    <div class="rounded-lg border border-border p-5 sm:p-6">
                        <ContactForm
                            :recipient="company.public_email"
                            :message-placeholder="content.form_placeholder"
                            :submit-label="content.form_submit"
                            processing-label="Sending…"
                            :reply-note="content.form_reply_note"
                            @sent="handleSuccess"
                        />
                    </div>
                </motion.div>
            </div>
        </div>
    </section>
</template>
