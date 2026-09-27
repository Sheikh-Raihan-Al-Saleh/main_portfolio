<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Send } from '@lucide/vue';
import { ref } from 'vue';
import ContactController from '@/actions/App/Http/Controllers/ContactController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

type Props = {
    /**
     * Who the message is addressed to. Shown next to the submit button so the
     * sender knows which inbox it lands in.
     */
    recipient?: string | null;
    /** Reassurance about response time, shown beside the submit button. */
    replyNote?: string | null;
    messagePlaceholder?: string;
    submitLabel?: string;
    processingLabel?: string;
};

const props = withDefaults(defineProps<Props>(), {
    recipient: null,
    replyNote: 'typically replies within 2–3 days',
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
                :placeholder="messagePlaceholder"
                class="rounded-lg border-border bg-card"
            />
            <InputError :message="errors.message" />
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

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <Button
                type="submit"
                size="lg"
                :disabled="processing"
                class="btn-laravel-primary rounded-lg px-8 text-sm font-semibold"
            >
                <Send class="size-4" />
                {{ processing ? processingLabel : submitLabel }}
            </Button>
            <span
                v-if="props.replyNote"
                class="text-xs text-muted-foreground"
            >
                <span
                    class="size-1.5 rounded-full bg-emerald-500 shadow-[0_0_6px_2px_rgba(16,185,129,0.4)]"
                />
                {{ props.replyNote }}
            </span>
        </div>
    </Form>
</template>
