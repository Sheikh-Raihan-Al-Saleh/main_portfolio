<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, MailOpen, Reply } from '@lucide/vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import DeleteAction from '@/components/admin/DeleteAction.vue';
import { Button } from '@/components/ui/button';
import messageRoutes from '@/routes/admin/messages';
import type { ContactMessage } from '@/types';

type Props = {
    message: ContactMessage;
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Messages', href: messageRoutes.index() }],
    },
});

function formatDateTime(value: string): string {
    return new Date(value).toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

function markUnread() {
    router.patch(messageRoutes.unread(props.message.id).url);
}

const mailtoUrl = `mailto:${props.message.email}?subject=${encodeURIComponent(
    `Re: ${props.message.subject || 'Your message'}`,
)}`;
</script>

<template>
    <Head :title="message.subject || 'Message'" />

    <div class="p-4 sm:p-6">
        <Button as-child variant="ghost" size="sm" class="mb-4 -ml-2">
            <Link :href="messageRoutes.index()">
                <ArrowLeft class="size-4" />
                Back to messages
            </Link>
        </Button>

        <AdminPageHeader
            :title="message.subject || '(no subject)'"
            :description="`From ${message.name} · ${formatDateTime(message.created_at)}`"
        >
            <template #actions>
                <Button as-child size="sm">
                    <a :href="mailtoUrl">
                        <Reply class="size-4" />
                        Reply
                    </a>
                </Button>
                <Button variant="outline" size="sm" @click="markUnread">
                    <MailOpen class="size-4" />
                    Mark unread
                </Button>
                <DeleteAction
                    :url="messageRoutes.destroy(message.id).url"
                    label="message"
                    description="This permanently removes the message. This cannot be undone."
                />
            </template>
        </AdminPageHeader>

        <div class="grid max-w-4xl gap-6 lg:grid-cols-[2fr_1fr]">
            <article class="rounded-xl border border-border bg-card p-6">
                <p class="text-sm leading-relaxed whitespace-pre-wrap">
                    {{ message.message }}
                </p>
            </article>

            <dl
                class="h-fit rounded-xl border border-border bg-card p-6 text-sm"
            >
                <div class="mb-4">
                    <dt
                        class="text-xs tracking-wide text-muted-foreground uppercase"
                    >
                        From
                    </dt>
                    <dd class="mt-0.5 font-medium">{{ message.name }}</dd>
                </div>
                <div class="mb-4">
                    <dt
                        class="text-xs tracking-wide text-muted-foreground uppercase"
                    >
                        Email
                    </dt>
                    <dd class="mt-0.5">
                        <a
                            :href="`mailto:${message.email}`"
                            class="break-all text-primary hover:underline"
                        >
                            {{ message.email }}
                        </a>
                    </dd>
                </div>
                <div v-if="message.ip_address" class="mb-4">
                    <dt
                        class="text-xs tracking-wide text-muted-foreground uppercase"
                    >
                        IP address
                    </dt>
                    <dd class="mt-0.5 font-mono text-xs">
                        {{ message.ip_address }}
                    </dd>
                </div>
                <div v-if="message.user_agent">
                    <dt
                        class="text-xs tracking-wide text-muted-foreground uppercase"
                    >
                        User agent
                    </dt>
                    <dd
                        class="mt-0.5 text-xs break-words text-muted-foreground"
                    >
                        {{ message.user_agent }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>
