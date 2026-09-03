<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Mail, MessageCircle, Send, Sparkles } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import ContactController from '@/actions/App/Http/Controllers/ContactController';
import InputError from '@/components/InputError.vue';
import SectionHeading from '@/components/portfolio/SectionHeading.vue';
import SocialIcon from '@/components/portfolio/SocialIcon.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { contentConfig } from '@/lib/content';
import { fadeUp, inViewOnce, stagger } from '@/lib/motion';
import type { Profile } from '@/types';

type Props = {
    profile: Profile;
};

const props = defineProps<Props>();

const content = computed(() => contentConfig(props.profile));

const formKey = ref(0);

const socialLinks = computed(() => {
    const socials = props.profile.socials ?? {};

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
    const phone = props.profile.phone?.replace(/[^0-9]/g, '');

    return phone ? `https://wa.me/${phone}` : null;
});

function handleSuccess() {
    formKey.value += 1;
    toast.success(content.value.contact.toast_title, {
        description: content.value.contact.toast_description,
    });
}
</script>

<template>
    <section
        id="contact"
        class="bg-noise relative scroll-mt-20 border-t border-border pt-20 sm:pt-24"
    >
        <div class="corner-dot corner-dot-tl" aria-hidden="true" />
        <div class="corner-dot corner-dot-tr" aria-hidden="true" />

        <div class="container-laravel section-dashed-xl">
            <SectionHeading
                index="05"
                :eyebrow="content.sections.contact.eyebrow"
                :title="content.sections.contact.title"
                :highlight="content.sections.contact.highlight"
                :description="content.sections.contact.description"
            />

            <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:gap-12">
                <!-- Info panel — dark card -->
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
                                        profile.available_for_work
                                            ? 'bg-emerald-500 shadow-[0_0_6px_2px_rgba(16,185,129,0.4)]'
                                            : 'bg-white/40 dark:bg-muted-foreground'
                                    "
                                />
                                {{
                                    profile.available_for_work
                                        ? 'Currently taking on new projects'
                                        : 'Selectively available'
                                }}
                            </p>
                        </motion.div>

                        <motion.div
                            :variants="fadeUp"
                            class="flex flex-col gap-4"
                        >
                            <h3 class="text-2xl font-bold tracking-tight">
                                Prefer email?
                            </h3>
                            <a
                                v-if="profile.public_email"
                                :href="`mailto:${profile.public_email}`"
                                class="group inline-flex flex-wrap items-center gap-3 text-xl font-semibold [overflow-wrap:anywhere] text-brand transition-colors duration-200 hover:text-brand/80 sm:text-2xl"
                            >
                                <span
                                    class="grid size-11 place-items-center rounded-lg bg-white/10 text-brand transition-transform duration-200 group-hover:-translate-y-0.5 dark:bg-muted"
                                >
                                    <Mail class="size-5" />
                                </span>
                                {{ profile.public_email }}
                            </a>
                            <p
                                class="text-sm leading-relaxed text-white/60 dark:text-muted-foreground"
                            >
                                Or find me on any of the channels below — the
                                fastest way to reach me is always email.
                            </p>
                        </motion.div>

                        <!-- WhatsApp button -->
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

                <!-- Form -->
                <motion.div
                    :variants="fadeUp"
                    initial="hidden"
                    while-in-view="visible"
                    :in-view-options="inViewOnce"
                >
                    <div class="rounded-lg border border-border p-5 sm:p-6">
                        <Form
                            :key="formKey"
                            v-bind="ContactController.store.form()"
                            class="flex flex-col gap-5"
                            reset-on-success
                            v-slot="{ errors, processing }"
                            @success="handleSuccess"
                        >
                            <div class="grid gap-5 sm:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label
                                        for="contact-name"
                                        class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                                    >
                                        Name
                                    </Label>
                                    <Input
                                        id="contact-name"
                                        name="name"
                                        required
                                        autocomplete="name"
                                        placeholder="Ada Lovelace"
                                        class="rounded-lg border-border bg-card"
                                    />
                                    <InputError :message="errors.name" />
                                </div>

                                <div class="grid gap-2">
                                    <Label
                                        for="contact-email"
                                        class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                                    >
                                        Email
                                    </Label>
                                    <Input
                                        id="contact-email"
                                        name="email"
                                        type="email"
                                        required
                                        autocomplete="email"
                                        placeholder="you@example.com"
                                        class="rounded-lg border-border bg-card"
                                    />
                                    <InputError :message="errors.email" />
                                </div>
                            </div>

                            <div class="grid gap-2">
                                <Label
                                    for="contact-subject"
                                    class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                                >
                                    Subject
                                </Label>
                                <Input
                                    id="contact-subject"
                                    name="subject"
                                    placeholder="Project inquiry"
                                    class="rounded-lg border-border bg-card"
                                />
                                <InputError :message="errors.subject" />
                            </div>

                            <div class="grid gap-2">
                                <Label
                                    for="contact-message"
                                    class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                                >
                                    Message
                                </Label>
                                <Textarea
                                    id="contact-message"
                                    name="message"
                                    required
                                    rows="6"
                                    placeholder="Tell me a little about what you have in mind…"
                                    class="rounded-lg border-border bg-card"
                                />
                                <InputError :message="errors.message" />
                            </div>

                            <div class="hidden" aria-hidden="true">
                                <label for="contact-website">Website</label>
                                <input
                                    id="contact-website"
                                    name="website"
                                    type="text"
                                    tabindex="-1"
                                    autocomplete="off"
                                />
                            </div>

                            <div class="flex items-center gap-3 pt-2">
                                <Button
                                    type="submit"
                                    size="lg"
                                    :disabled="processing"
                                    class="btn-laravel-primary rounded-lg px-8 text-sm font-semibold"
                                >
                                    <Send class="size-4" />
                                    {{
                                        processing ? 'Sending…' : 'Send message'
                                    }}
                                </Button>
                                <span
                                    class="hidden text-xs text-muted-foreground sm:inline"
                                >
                                    <span
                                        class="size-1.5 rounded-full bg-emerald-500 shadow-[0_0_6px_2px_rgba(16,185,129,0.4)]"
                                    />
                                    typically replies within 2–3 days
                                </span>
                            </div>
                        </Form>
                    </div>
                </motion.div>
            </div>
        </div>
    </section>
</template>
