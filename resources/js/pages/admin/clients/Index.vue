<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { Eye, EyeOff, Pencil, Plus, Search, Users } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed, ref, watch } from 'vue';
import ClientController from '@/actions/App/Http/Controllers/Admin/ClientController';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import DeleteAction from '@/components/admin/DeleteAction.vue';
import FileField from '@/components/admin/FileField.vue';
import ReorderButtons from '@/components/admin/ReorderButtons.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { useReorder } from '@/composables/useReorder';
import { fadeUp } from '@/lib/motion';
import clientRoutes from '@/routes/admin/clients';
import type { Client } from '@/types';

type Props = {
    clients: Client[];
    filters: { search: string | null };
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Clients', href: clientRoutes.index().url }],
    },
});

const { items, move, saving } = useReorder<Client>(
    () => props.clients,
    clientRoutes.reorder().url,
);

const open = ref(false);
const editing = ref<Client | null>(null);
const search = ref(props.filters.search ?? '');

const visibleCount = computed(() => items.value.filter((c) => c.is_visible).length);

function create() {
    editing.value = null;
    open.value = true;
}

function edit(client: Client) {
    editing.value = client;
    open.value = true;
}

// Debounce so typing doesn't fire a request per keystroke.
let searchTimer: ReturnType<typeof setTimeout> | null = null;

watch(search, (value) => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        router.get(
            clientRoutes.index().url,
            value ? { search: value } : {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 300);
});
</script>

<template>
    <Head title="Clients" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="Clients"
            description="Logos shown in the trusted-by strip on the company home page and About page."
        >
            <template #actions>
                <Button as-child variant="outline" class="hidden sm:inline-flex">
                    <a href="/" target="_blank" rel="noopener noreferrer">
                        <Users class="size-4" />
                        Preview strip
                    </a>
                </Button>
                <Button @click="create">
                    <Plus class="size-4" />
                    New client
                </Button>
            </template>
        </AdminPageHeader>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-border p-4">
                <p class="text-xs tracking-widest text-muted-foreground uppercase">
                    Total
                </p>
                <p class="mt-1 text-2xl font-bold tabular-nums">{{ items.length }}</p>
            </div>
            <div class="rounded-xl border border-border p-4">
                <p class="text-xs tracking-widest text-muted-foreground uppercase">
                    Visible on site
                </p>
                <p class="mt-1 text-2xl font-bold tabular-nums">{{ visibleCount }}</p>
            </div>
            <div class="rounded-xl border border-border p-4">
                <p class="text-xs tracking-widest text-muted-foreground uppercase">
                    With logo
                </p>
                <p class="mt-1 text-2xl font-bold tabular-nums">
                    {{ items.filter((c) => c.logo_url).length }}
                </p>
            </div>
        </div>

        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative w-full sm:max-w-xs">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="search"
                    name="search"
                    placeholder="Search clients…"
                    class="pl-9"
                />
            </div>
            <p v-if="saving" class="text-sm text-muted-foreground">Saving order…</p>
        </div>

        <motion.div
            :variants="fadeUp"
            initial="hidden"
            animate="visible"
            class="mt-4 overflow-x-auto rounded-xl border border-border"
        >
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-12">Order</TableHead>
                        <TableHead>Client</TableHead>
                        <TableHead class="hidden md:table-cell">Industry</TableHead>
                        <TableHead class="hidden lg:table-cell">Website</TableHead>
                        <TableHead class="text-right">Status</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow v-for="(client, index) in items" :key="client.id">
                        <TableCell>
                            <ReorderButtons
                                :index="index"
                                :total="items.length"
                                :disabled="saving"
                                @move="(_, direction) => move(index, direction)"
                            />
                        </TableCell>

                        <TableCell>
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-md border border-border bg-muted"
                                >
                                    <img
                                        v-if="client.logo_url"
                                        :src="client.logo_url"
                                        :alt="client.name"
                                        class="size-full object-contain p-1"
                                    />
                                    <span class="text-xs font-semibold">
                                        {{ client.name.slice(0, 2).toUpperCase() }}
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-medium">{{ client.name }}</p>
                                    <p
                                        v-if="client.summary"
                                        class="truncate text-xs text-muted-foreground"
                                    >
                                        {{ client.summary }}
                                    </p>
                                </div>
                            </div>
                        </TableCell>

                        <TableCell class="hidden md:table-cell text-muted-foreground">
                            {{ client.industry ?? '—' }}
                        </TableCell>

                        <TableCell class="hidden lg:table-cell">
                            <a
                                v-if="client.website_url"
                                :href="client.website_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                            >
                                {{ client.website_url.replace(/^https?:\/\//, '') }}
                                <ExternalLink class="size-3" />
                            </a>
                            <span v-else class="text-sm text-muted-foreground">—</span>
                        </TableCell>

                        <TableCell class="text-right">
                            <Badge :variant="client.is_visible ? 'default' : 'secondary'">
                                <Eye v-if="client.is_visible" class="size-3" />
                                <EyeOff v-else class="size-3" />
                                {{ client.is_visible ? 'Live' : 'Hidden' }}
                            </Badge>
                        </TableCell>

                        <TableCell class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <Button
                                    variant="ghost"
                                    size="icon-sm"
                                    :aria-label="`Edit ${client.name}`"
                                    @click="edit(client)"
                                >
                                    <Pencil class="size-4" />
                                </Button>
                                <DeleteAction
                                    :url="ClientController.destroy(client.id).url"
                                    :label="client.name"
                                    description="The client and its logo will be removed from the site."
                                />
                            </div>
                        </TableCell>
                    </TableRow>

                    <TableRow v-if="!items.length">
                        <TableCell :colspan="6" class="py-12 text-center">
                            <p class="font-medium">No clients yet</p>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Add the studios, products and teams you have shipped for.
                            </p>
                            <Button size="sm" class="mt-4" @click="create">
                                <Plus class="size-4" />
                                New client
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </motion.div>
    </div>

    <Dialog v-model:open="open">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>
                    {{ editing ? `Edit ${editing.name}` : 'New client' }}
                </DialogTitle>
                <DialogDescription>
                    The logo is shown as-is on a muted background so light and dark
                    artwork both work.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-if="editing"
                v-slot="{ errors, processing }"
                v-bind="ClientController.update.form(editing.id)"
                class="grid gap-4"
                enctype="multipart/form-data"
                preserve-scroll
                :on-success="() => (open = false)"
            >
                <div class="grid gap-2">
                    <Label for="client-name">Name</Label>
                    <Input
                        id="client-name"
                        name="name"
                        :value="editing.name"
                        required
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="client-industry">Industry</Label>
                    <Input
                        id="client-industry"
                        name="industry"
                        :value="editing.industry ?? ''"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="client-website">Website</Label>
                    <Input
                        id="client-website"
                        name="website_url"
                        type="url"
                        placeholder="https://"
                        :value="editing.website_url ?? ''"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="client-summary">Short note</Label>
                    <Textarea
                        id="client-summary"
                        name="summary"
                        rows="2"
                        :value="editing.summary ?? ''"
                    />
                </div>

                <FileField
                    name="logo"
                    label="Logo"
                    remove-name="remove_logo"
                    :current-url="editing.logo_url"
                    accept="image/svg+xml,image/png,image/jpeg,image/gif,image/webp"
                    preview
                    hint="SVG, PNG, JPG, GIF or WebP, up to 2 MB. SVGs must be plain artwork, without scripts."
                />

                <label class="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        name="is_visible"
                        value="1"
                        :checked="editing.is_visible"
                        class="size-4 rounded border-border"
                    />
                    Show on the public site
                </label>

                <div class="flex justify-end gap-2">
                    <Button type="button" variant="ghost" @click="open = false">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        {{ processing ? 'Saving…' : 'Save changes' }}
                    </Button>
                </div>

                <p v-if="Object.keys(errors).length" class="text-sm text-destructive">
                    Fix the highlighted fields and try again.
                </p>
            </Form>

            <Form
                v-else
                v-slot="{ errors, processing }"
                v-bind="ClientController.store.form()"
                class="grid gap-4"
                enctype="multipart/form-data"
                preserve-scroll
                :on-success="() => (open = false)"
            >
                <div class="grid gap-2">
                    <Label for="new-client-name">Name</Label>
                    <Input id="new-client-name" name="name" required />
                </div>

                <div class="grid gap-2">
                    <Label for="new-client-industry">Industry</Label>
                    <Input id="new-client-industry" name="industry" />
                </div>

                <div class="grid gap-2">
                    <Label for="new-client-website">Website</Label>
                    <Input
                        id="new-client-website"
                        name="website_url"
                        type="url"
                        placeholder="https://"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="new-client-summary">Short note</Label>
                    <Textarea id="new-client-summary" name="summary" rows="2" />
                </div>

                <FileField
                    name="logo"
                    label="Logo"
                    accept="image/svg+xml,image/png,image/jpeg,image/gif,image/webp"
                    preview
                    hint="SVG, PNG, JPG, GIF or WebP, up to 2 MB. SVGs must be plain artwork, without scripts."
                />

                <label class="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        name="is_visible"
                        value="1"
                        checked
                        class="size-4 rounded border-border"
                    />
                    Show on the public site
                </label>

                <div class="flex justify-end gap-2">
                    <Button type="button" variant="ghost" @click="open = false">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        {{ processing ? 'Adding…' : 'Add client' }}
                    </Button>
                </div>

                <p v-if="Object.keys(errors).length" class="text-sm text-destructive">
                    Fix the highlighted fields and try again.
                </p>
            </Form>
        </DialogContent>
    </Dialog>
</template>
