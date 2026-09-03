<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Mail, MessageSquare } from '@lucide/vue';
import { motion } from 'motion-v';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import { Button } from '@/components/ui/button';
import { fadeUp } from '@/lib/motion';
import messageRoutes from '@/routes/admin/messages';
import type { ContactMessage, Paginated } from '@/types';

type Props = {
    messages: Paginated<ContactMessage>;
    filters: { filter: string | null };
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Messages', href: messageRoutes.index() }],
    },
});

/** Laravel ships pagination labels containing `&laquo;` / `&raquo;` entities. */
function paginationLabel(label: string): string {
    return label.replace('&laquo;', '‹').replace('&raquo;', '›').trim();
}

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString(undefined, {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Messages" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="Messages"
            description="Enquiries submitted through the contact form."
        >
            <template #actions>
                <Button
                    as-child
                    :variant="
                        filters.filter === 'unread' ? 'default' : 'outline'
                    "
                    size="sm"
                    :class="
                        filters.filter === 'unread'
                            ? 'bg-blue-500 text-white hover:bg-blue-600'
                            : ''
                    "
                >
                    <Link
                        :href="
                            messageRoutes.index({ query: { filter: 'unread' } })
                        "
                    >
                        <Mail class="size-4" />
                        Unread only
                    </Link>
                </Button>
                <Button
                    as-child
                    :variant="filters.filter ? 'outline' : 'default'"
                    size="sm"
                >
                    <Link :href="messageRoutes.index()">All</Link>
                </Button>
            </template>
        </AdminPageHeader>

        <motion.div
            :variants="fadeUp"
            initial="hidden"
            animate="visible"
            class="divide-y divide-border overflow-hidden rounded-xl border border-border"
        >
            <Link
                v-for="message in messages.data"
                :key="message.id"
                :href="messageRoutes.show(message.id)"
                class="flex items-start gap-4 px-5 py-4 transition-colors hover:bg-accent/50"
            >
                <span
                    :class="[
                        'mt-2 size-2 shrink-0 rounded-full',
                        message.read_at
                            ? 'bg-muted-foreground/30'
                            : 'bg-blue-500',
                    ]"
                    :aria-label="message.read_at ? 'Read' : 'Unread'"
                />

                <div class="min-w-0 flex-1">
                    <p
                        :class="[
                            'truncate',
                            message.read_at ? 'font-medium' : 'font-semibold',
                        ]"
                    >
                        {{ message.subject || '(no subject)' }}
                    </p>
                    <p class="truncate text-sm text-muted-foreground">
                        {{ message.name }} · {{ message.email }}
                    </p>
                    <p class="mt-1 line-clamp-2 text-sm text-muted-foreground">
                        {{ message.message }}
                    </p>
                </div>

                <span class="shrink-0 text-xs text-muted-foreground">
                    {{ formatDate(message.created_at) }}
                </span>
            </Link>

            <div
                v-if="!messages.data.length"
                class="flex flex-col items-center gap-2 py-16 text-center text-sm text-muted-foreground"
            >
                <MessageSquare class="size-8 text-muted-foreground/30" />
                <p>
                    {{
                        filters.filter === 'unread'
                            ? 'No unread messages.'
                            : 'No messages yet.'
                    }}
                </p>
            </div>
        </motion.div>

        <nav
            v-if="messages.last_page > 1"
            class="mt-6 flex flex-wrap justify-center gap-1"
            aria-label="Pagination"
        >
            <Button
                v-for="link in messages.links"
                :key="link.label"
                as-child
                :variant="link.active ? 'default' : 'outline'"
                size="sm"
                :disabled="!link.url"
            >
                <Link v-if="link.url" :href="link.url">
                    {{ paginationLabel(link.label) }}
                </Link>
                <span v-else>{{ paginationLabel(link.label) }}</span>
            </Button>
        </nav>
    </div>
</template>
