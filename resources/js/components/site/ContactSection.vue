<script setup lang="ts">
import { Mail, MapPin, Phone } from '@lucide/vue';
import ContactForm from '@/components/site/ContactForm.vue';
import Reveal from '@/components/site/Reveal.vue';
import SectionHeading from '@/components/site/SectionHeading.vue';
import SocialLinks from '@/components/site/SocialLinks.vue';
import type { ContactSectionProps } from '@/types';

/**
 * The contact block shared by both faces: heading and direct details on the
 * left, the form on the right.
 */
withDefaults(defineProps<ContactSectionProps>(), {
    highlight: null,
    description: null,
    email: null,
    phone: null,
    location: null,
    socials: () => [],
    emailHeading: 'Prefer email?',
    emailNote: null,
    placeholder: undefined,
    submitLabel: undefined,
    replyNote: null,
});
</script>

<template>
    <section id="contact" class="section scroll-mt-24">
        <div class="container-site grid gap-16 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <SectionHeading
                    :eyebrow="eyebrow"
                    :title="title"
                    :highlight="highlight"
                    :description="description"
                />

                <Reveal :delay="0.1" class="mt-12 grid gap-0">
                    <a
                        v-if="email"
                        :href="`mailto:${email}`"
                        class="rule group flex items-center gap-4 py-5"
                    >
                        <Mail class="size-5 text-brand" aria-hidden="true" />
                        <span class="link-underline text-lg">{{ email }}</span>
                    </a>
                    <a
                        v-if="phone"
                        :href="`tel:${phone.replace(/\s+/g, '')}`"
                        class="rule flex items-center gap-4 py-5"
                    >
                        <Phone class="size-5 text-brand" aria-hidden="true" />
                        <span class="link-underline text-lg">{{ phone }}</span>
                    </a>
                    <p
                        v-if="location"
                        class="rule flex items-center gap-4 py-5"
                    >
                        <MapPin class="size-5 text-brand" aria-hidden="true" />
                        <span class="text-lg">{{ location }}</span>
                    </p>
                </Reveal>

                <p v-if="emailNote" class="body-copy mt-6 max-w-md text-sm">
                    {{ emailNote }}
                </p>
                <SocialLinks :links="socials" class="mt-8" />
            </div>

            <Reveal :delay="0.15" class="lg:col-span-6 lg:col-start-7">
                <div class="paper-card p-6 sm:p-10">
                    <ContactForm
                        :site="site"
                        :message-placeholder="placeholder"
                        :submit-label="submitLabel"
                        :reply-note="replyNote"
                    />
                </div>
            </Reveal>
        </div>
    </section>
</template>
