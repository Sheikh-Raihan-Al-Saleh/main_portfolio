<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Send } from '@lucide/vue';
import { ref } from 'vue';
import ContactController from '@/actions/App/Http/Controllers/ContactController';
import InputError from '@/components/InputError.vue';

/**
 * The public contact form. Both faces post to the same endpoint; the accent
 * comes from the face's tokens.
 */
type Props = {
    replyNote?: string | null;
    messagePlaceholder?: string;
    submitLabel?: string;
    processingLabel?: string;
};

withDefaults(defineProps<Props>(), {
    replyNote: 'Typically replies within 2–3 days',
    messagePlaceholder: 'Tell me a little about what you have in mind…',
    submitLabel: 'Send message',
    processingLabel: 'Sending…',
});

const emit = defineEmits<{ sent: [] }>();

/** Bumped on success so the form remounts and clears itself. */
const formKey = ref(0);

function handleSuccess() {
    formKey.value += 1;
    emit('sent');
}

const fields = [
    {
        id: 'name',
        label: 'Name',
        type: 'text',
        required: true,
        autocomplete: 'name',
        placeholder: 'Ada Lovelace',
    },
    {
        id: 'email',
        label: 'Email',
        type: 'email',
        required: true,
        autocomplete: 'email',
        placeholder: 'you@example.com',
    },
] as const;

const input =
    'mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 text-base outline-none sm:text-sm transition-[border-color,box-shadow] placeholder:text-muted-foreground/70 focus:border-[var(--ring)] focus:ring-4 focus:ring-[var(--brand-glow)]';
</script>

<template>
    <Form
        :key="formKey"
        v-bind="ContactController.store.form()"
        class="flex flex-col gap-5"
        reset-on-success
        v-slot="{ errors, processing }"
        @success="handleSuccess"
    >
        <div class="grid gap-5 sm:grid-cols-2">
            <div v-for="field in fields" :key="field.id">
                <label
                    :for="`contact-${field.id}`"
                    class="text-sm font-medium"
                    >{{ field.label }}</label
                >
                <input
                    :id="`contact-${field.id}`"
                    :name="field.id"
                    :type="field.type"
                    :required="field.required"
                    :autocomplete="field.autocomplete"
                    :placeholder="field.placeholder"
                    :class="input"
                />
                <InputError :message="errors[field.id]" class="mt-1.5" />
            </div>
        </div>

        <div>
            <label for="contact-subject" class="text-sm font-medium">
                Subject
                <span class="font-normal text-muted-foreground"
                    >(optional)</span
                >
            </label>
            <input
                id="contact-subject"
                name="subject"
                type="text"
                placeholder="Project inquiry"
                :class="input"
            />
            <InputError :message="errors.subject" class="mt-1.5" />
        </div>

        <div>
            <label for="contact-message" class="text-sm font-medium"
                >Message</label
            >
            <textarea
                id="contact-message"
                name="message"
                required
                rows="5"
                :placeholder="messagePlaceholder"
                :class="[input, 'resize-y']"
            />
            <InputError :message="errors.message" class="mt-1.5" />
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

        <div class="flex flex-wrap items-center gap-4 pt-1">
            <button
                type="submit"
                :disabled="processing"
                class="ui-btn ui-btn-primary"
            >
                <Send class="size-4" />
                {{ processing ? processingLabel : submitLabel }}
            </button>
            <span
                v-if="replyNote"
                class="inline-flex items-center gap-2 text-sm text-muted-foreground"
            >
                <span class="ui-pulse-dot" />
                {{ replyNote }}
            </span>
        </div>
    </Form>
</template>
