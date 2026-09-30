<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import {
    Blocks,
    Building2,
    ExternalLink,
    LayoutTemplate,
    Network,
    PanelBottom,
    Settings2,
    Sparkles,
    Users,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import StudioContentController from '@/actions/App/Http/Controllers/Admin/StudioContentController';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import StudioContentSectionDialog from '@/components/admin/StudioContentSectionDialog.vue';
import type {SectionKey} from '@/components/admin/StudioContentSectionDialog.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Company, StudioContent } from '@/types';

type Props = {
    company: Pick<Company, 'id' | 'name'>;
    /** The merged studio content: defaults plus any stored edits. */
    content: StudioContent;
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Home sections', href: '/admin/studio-sections' },
        ],
    },
});

/**
 * The sections a single save persists, in page order with the ticker. The
 * dialog handles per-section editing; this list is what the admin scans.
 */
const SECTIONS: {
    key: SectionKey | 'ticker';
    title: string;
    description: string;
    icon: typeof Blocks;
    /** Short preview of the current copy. */
    summary: (content: StudioContent) => string;
}[] = [
    {
        key: 'ticker',
        title: 'Scroll ticker',
        description: 'Phrases drifting under the hero.',
        icon: Sparkles,
        summary: (c) => c.ticker.items.join(' · '),
    },
    {
        key: 'clients',
        title: 'Client logos',
        description: 'Heading above the logo wall.',
        icon: Users,
        summary: (c) => c.clients.title,
    },
    {
        key: 'capabilities',
        title: '01 — What we build',
        description: 'Capability cards and the 3D stack copy.',
        icon: Blocks,
        summary: (c) => c.capabilities.title,
    },
    {
        key: 'work',
        title: '02 — Selected work',
        description: 'Case study composition and buttons.',
        icon: PanelBottom,
        summary: (c) => c.work.title,
    },
    {
        key: 'engineering',
        title: '03 — Engineering',
        description: 'The interactive pipeline stages.',
        icon: Network,
        summary: (c) => c.engineering.title,
    },
    {
        key: 'value',
        title: '04 — Why work with us',
        description: 'Value cards and guarantees.',
        icon: Sparkles,
        summary: (c) => c.value.title,
    },
    {
        key: 'about',
        title: '05 — About',
        description: 'Studio story heading and mission label.',
        icon: Building2,
        summary: (c) => c.about.title,
    },
    {
        key: 'founder_bridge',
        title: 'Studio ↔ Founder bridge',
        description: 'The seam between the two sites.',
        icon: LayoutTemplate,
        summary: (c) => c.founder_bridge.studio_heading,
    },
    {
        key: 'founder_work',
        title: "06 — Founder's own work",
        description: 'Founder work preview heading.',
        icon: PanelBottom,
        summary: (c) => c.founder_work.title,
    },
    {
        key: 'cta',
        title: 'Final CTA',
        description: 'The closing call to action.',
        icon: Sparkles,
        summary: (c) => c.cta.title,
    },
    {
        key: 'contact',
        title: '07 — Contact',
        description: 'Contact heading and form labels.',
        icon: Building2,
        summary: (c) => c.contact.title,
    },
];

/**
 * The working copy. Edited in the dialog, persisted by the single save form
 * below — one round trip for any number of section edits, matching how the
 * Company page already saves.
 */
const draft = ref<StudioContent>(
    JSON.parse(JSON.stringify(props.content)) as StudioContent,
);

const dialogOpen = ref(false);
const dialogKey = ref<SectionKey | null>(null);

const activeSection = computed<Record<string, unknown> | null>(() =>
    dialogKey.value
        ? (draft.value[dialogKey.value] as unknown as Record<string, unknown>)
        : null,
);

function openDialog(key: SectionKey) {
    dialogKey.value = key;
    dialogOpen.value = true;
}

function applySave(key: SectionKey, value: Record<string, unknown>) {
    (draft.value as unknown as Record<string, unknown>)[key] = value;
}
</script>

<template>
    <Head title="Home sections" />

    <div class="p-4 sm:p-6">
        <AdminPageHeader
            title="Home sections"
            description="Every fixed section of the studio home page — headings, cards, pipeline stages, the closing CTA — edited here. Composable blocks live under Company."
        >
            <template #actions>
                <Button as-child variant="outline" size="sm">
                    <a href="/" target="_blank" rel="noopener">
                        <ExternalLink class="size-4" />
                        View home
                    </a>
                </Button>
            </template>
        </AdminPageHeader>

        <Form
            v-bind="StudioContentController.update.form()"
            class="mt-2 flex max-w-4xl flex-col gap-3"
            v-slot="{ processing }"
        >
            <input
                type="hidden"
                name="content"
                :value="JSON.stringify(draft)"
            />

            <ul class="flex flex-col gap-2">
                <li
                    v-for="section in SECTIONS"
                    :key="section.key"
                    class="group flex items-center gap-4 rounded-lg border border-border/70 bg-card p-4 transition-colors hover:border-brand/40"
                >
                    <span
                        class="grid size-10 shrink-0 place-items-center rounded-lg bg-primary/10 text-primary"
                    >
                        <component :is="section.icon" class="size-5" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium">
                            {{ section.title }}
                        </p>
                        <p class="truncate text-xs text-muted-foreground">
                            {{ section.summary(draft) }}
                        </p>
                    </div>

                    <Button
                        v-if="section.key !== 'ticker'"
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="openDialog(section.key as SectionKey)"
                    >
                        <Settings2 class="size-4" />
                        Edit
                    </Button>

                    <!-- The ticker edits inline: a plain list of phrases. -->
                    <div v-else class="flex w-full max-w-md items-center gap-2">
                        <div class="min-w-0 flex-1">
                            <Label class="sr-only" for="ticker-items"
                                >Ticker phrases</Label
                            >
                            <Input
                                id="ticker-items"
                                :model-value="draft.ticker.items.join(', ')"
                                placeholder="Comma-separated phrases"
                                @update:model-value="
                                    (value) =>
                                        (draft.ticker.items = String(value)
                                            .split(',')
                                            .map((item) => item.trim())
                                            .filter(Boolean))
                                "
                            />
                        </div>
                    </div>
                </li>
            </ul>

            <div class="mt-4 flex items-center gap-3">
                <Button type="submit" :disabled="processing">
                    {{ processing ? 'Saving…' : 'Save home sections' }}
                </Button>
                <p class="text-xs text-muted-foreground">
                    Section edits are applied when you save; the live page
                    updates immediately after.
                </p>
            </div>
        </Form>

        <StudioContentSectionDialog
            v-model:open="dialogOpen"
            :section-key="dialogKey"
            :content="activeSection"
            @save="applySave"
        />
    </div>
</template>
