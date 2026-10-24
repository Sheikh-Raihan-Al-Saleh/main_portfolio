<script setup lang="ts">
import { ArrowUpRight, Mail, MapPin, MessageCircle } from '@lucide/vue';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import SocialIcon from '@/components/portfolio/SocialIcon.vue';
import ContactForm from '@/components/site/ContactForm.vue';
import Reveal from '@/components/site/Reveal.vue';
import SectionHeader from '@/components/site/SectionHeader.vue';
import type { Social } from '@/types';

/**
 * The contact section for either face: the direct lines in a column of
 * tiles, the form in a card beside them. The caller supplies the owner's
 * details and copy, so the studio and the founder each speak for themselves.
 */
type Props = {
    eyebrow?: string;
    title: string;
    highlight?: string;
    description?: string;
    email?: string | null;
    phone?: string | null;
    location?: string | null;
    socials?: Social | null;
    messagePlaceholder?: string;
    submitLabel?: string;
    replyNote?: string | null;
    toastTitle?: string;
    toastDescription?: string;
};

const props = withDefaults(defineProps<Props>(), {
    eyebrow: '',
    highlight: '',
    description: '',
    email: null,
    phone: null,
    location: null,
    socials: null,
    messagePlaceholder: undefined,
    submitLabel: undefined,
    replyNote: undefined,
    toastTitle: 'Message sent',
    toastDescription: 'Thanks for reaching out — expect a reply soon.',
});

const whatsappUrl = computed(() => {
    const phone = props.phone?.replace(/[^0-9]/g, '');

    return phone ? `https://wa.me/${phone}` : null;
});

const socialLinks = computed(() => {
    const socials = props.socials ?? {};

    return (
        [
            { href: socials.github, name: 'github', label: 'GitHub' },
            { href: socials.linkedin, name: 'linkedin', label: 'LinkedIn' },
            { href: socials.x, name: 'x', label: 'X' },
            { href: socials.website, name: 'website', label: 'Website' },
        ] as const
    ).filter((link) => typeof link.href === 'string' && link.href.length > 0);
});

function handleSent() {
    toast.success(props.toastTitle, { description: props.toastDescription });
}
</script>

<template>
    <section id="contact" class="site-section scroll-mt-24">
        <div class="site-container">
            <SectionHeader
                :eyebrow="eyebrow"
                :title="title"
                :highlight="highlight"
                :description="description"
            />

            <div class="mt-14 grid gap-6 lg:grid-cols-12">
                <Reveal class="flex flex-col gap-4 lg:col-span-4">
                    <a
                        v-if="email"
                        :href="`mailto:${email}`"
                        class="group ui-card ui-card-hover flex items-center gap-4 p-5"
                    >
                        <span class="ui-icon-tile"
                            ><Mail class="size-5"
                        /></span>
                        <span class="min-w-0">
                            <span class="block text-xs text-muted-foreground"
                                >Email</span
                            >
                            <span class="block truncate font-semibold">{{
                                email
                            }}</span>
                        </span>
                        <ArrowUpRight
                            class="ml-auto size-4 shrink-0 text-muted-foreground transition-colors group-hover:text-brand"
                        />
                    </a>
                    <a
                        v-if="whatsappUrl"
                        :href="whatsappUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group ui-card ui-card-hover flex items-center gap-4 p-5"
                    >
                        <span class="ui-icon-tile"
                            ><MessageCircle class="size-5"
                        /></span>
                        <span>
                            <span class="block text-xs text-muted-foreground"
                                >WhatsApp</span
                            >
                            <span class="block font-semibold">{{ phone }}</span>
                        </span>
                        <ArrowUpRight
                            class="ml-auto size-4 text-muted-foreground transition-colors group-hover:text-brand"
                        />
                    </a>
                    <div
                        v-if="location"
                        class="ui-card flex items-center gap-4 p-5"
                    >
                        <span class="ui-icon-tile"
                            ><MapPin class="size-5"
                        /></span>
                        <span>
                            <span class="block text-xs text-muted-foreground"
                                >Location</span
                            >
                            <span class="block font-semibold">{{
                                location
                            }}</span>
                        </span>
                    </div>
                    <div
                        v-if="socialLinks.length"
                        class="flex flex-wrap gap-2 pt-1"
                    >
                        <a
                            v-for="link in socialLinks"
                            :key="link.label"
                            :href="link.href ?? undefined"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="ui-btn ui-btn-secondary ui-btn-sm"
                        >
                            <SocialIcon :name="link.name" class="size-4" />
                            {{ link.label }}
                        </a>
                    </div>
                </Reveal>

                <Reveal :delay="0.1" class="lg:col-span-8">
                    <div class="ui-card p-6 sm:p-8 lg:p-10">
                        <ContactForm
                            :message-placeholder="messagePlaceholder"
                            :submit-label="submitLabel"
                            :reply-note="replyNote"
                            @sent="handleSent"
                        />
                    </div>
                </Reveal>
            </div>
        </div>
    </section>
</template>
