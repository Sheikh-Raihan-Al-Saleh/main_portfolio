<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ArrowUpRight } from '@lucide/vue';
import { ref } from 'vue';
import ContactController from '@/actions/App/Http/Controllers/Contact/ContactController';
import InputError from '@/components/common/InputError.vue';
import type { ContactFormProps } from '@/types';

/**
 * The public contact form, used by both faces of the site. `site` records
 * which one the enquiry came from, so it reaches the right inbox.
 */

withDefaults(defineProps<ContactFormProps>(), {
    messagePlaceholder: 'A few lines about what you have in mind…',
    submitLabel: 'Send message',
    replyNote: null,
});

/** Bumped on success so the form remounts empty. */
const formKey = ref(0);

const field =
    'peer w-full border-0 border-b border-line bg-transparent px-0 pt-7 pb-3 text-lg text-ink outline-none transition-colors placeholder:text-transparent focus:border-ink';
const label =
    'pointer-events-none absolute top-1 left-0 text-xs font-medium tracking-[0.12em] text-stone uppercase transition-colors peer-focus:text-brand-text';
</script>

<template>
    <Form
        :key="formKey"
        v-bind="ContactController.store.form()"
        class="grid gap-8"
        reset-on-success
        v-slot="{ errors, processing }"
        @success="formKey++"
    >
        <input type="hidden" name="site" :value="site" />

        <div class="grid gap-8 sm:grid-cols-2">
            <div class="relative">
                <input
                    id="contact-name"
                    name="name"
                    type="text"
                    required
                    autocomplete="name"
                    placeholder="Name"
                    :class="field"
                />
                <label for="contact-name" :class="label">Your name</label>
                <InputError :message="errors.name" class="mt-2" />
            </div>
            <div class="relative">
                <input
                    id="contact-email"
                    name="email"
                    type="email"
                    required
                    autocomplete="email"
                    placeholder="Email"
                    :class="field"
                />
                <label for="contact-email" :class="label">Email</label>
                <InputError :message="errors.email" class="mt-2" />
            </div>
        </div>

        <div class="relative">
            <input
                id="contact-subject"
                name="subject"
                type="text"
                placeholder="Subject"
                :class="field"
            />
            <label for="contact-subject" :class="label"
                >Subject (optional)</label
            >
            <InputError :message="errors.subject" class="mt-2" />
        </div>

        <div class="relative">
            <textarea
                id="contact-message"
                name="message"
                required
                rows="4"
                :placeholder="messagePlaceholder"
                :class="[
                    field,
                    'resize-y placeholder:text-stone/60 focus:placeholder:text-stone',
                ]"
            />
            <label for="contact-message" :class="label">Message</label>
            <InputError :message="errors.message" class="mt-2" />
        </div>

        <!-- Honeypot: hidden from people, attractive to bots. -->
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

        <div class="flex flex-wrap items-center gap-5">
            <button type="submit" class="btn btn-ink" :disabled="processing">
                {{ processing ? 'Sending…' : submitLabel }}
                <ArrowUpRight class="btn-arrow size-4" aria-hidden="true" />
            </button>
            <p v-if="replyNote" class="meta">{{ replyNote }}</p>
        </div>
    </Form>
</template>
