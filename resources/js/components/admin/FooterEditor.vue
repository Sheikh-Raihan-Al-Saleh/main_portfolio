<script setup lang="ts">
import { Plus, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { FooterConfig } from '@/types';

type Props = {
    footer: FooterConfig | null;
};

const props = defineProps<Props>();

type LinkRow = {
    label: string;
    url: string;
};

type ColumnRow = {
    title: string;
    links: LinkRow[];
};

function toLink(link?: {
    label?: string | null;
    url?: string | null;
}): LinkRow {
    return { label: link?.label ?? '', url: link?.url ?? '' };
}

function toColumn(col?: {
    title?: string | null;
    links?: { label?: string | null; url?: string | null }[] | null;
}): ColumnRow {
    return {
        title: col?.title ?? '',
        links: (col?.links ?? []).map(toLink),
    };
}

const statusText = ref(props.footer?.status_text ?? '');
const statusTextUnavailable = ref(props.footer?.status_text_unavailable ?? '');
const copyright = ref(props.footer?.copyright ?? '');
const backToTop = ref(props.footer?.back_to_top ?? '');
const columns = ref<ColumnRow[]>(props.footer?.columns?.map(toColumn) ?? []);
const legalLinks = ref<LinkRow[]>(props.footer?.legal_links?.map(toLink) ?? []);

function addColumn() {
    columns.value.push({ title: '', links: [{ label: '', url: '' }] });
}

function removeColumn(index: number) {
    columns.value.splice(index, 1);
}

function addLink(column: ColumnRow) {
    column.links.push({ label: '', url: '' });
}

function removeLink(column: ColumnRow, index: number) {
    column.links.splice(index, 1);
}

function addLegalLink() {
    legalLinks.value.push({ label: '', url: '' });
}

function removeLegalLink(index: number) {
    legalLinks.value.splice(index, 1);
}

const nameToken = '{{name}}';

const tokens = [
    '{{year}}',
    '{{name}}',
    '{{headline}}',
    '{{tagline}}',
    '{{email}}',
    '{{phone}}',
    '{{location}}',
    '{{whatsapp}}',
    '{{github}}',
    '{{linkedin}}',
    '{{x}}',
    '{{website}}',
];
</script>

<template>
    <div class="flex flex-col gap-6">
        <p
            class="rounded-lg border border-dashed border-border bg-muted/40 p-3 text-xs leading-relaxed text-muted-foreground"
        >
            Every field is rendered verbatim from this config. Tokens like
            <code class="rounded bg-muted px-1 py-0.5 font-mono text-[10px]">{{
                nameToken
            }}</code>
            are replaced with live profile data; a link whose tokens resolve
            empty (e.g. no phone) is hidden automatically. Available:
            {{ tokens.join(' ') }}.
        </p>

        <div class="grid gap-6 sm:grid-cols-2">
            <div class="grid gap-2">
                <Label for="footer-status-text">Available status</Label>
                <Input
                    id="footer-status-text"
                    v-model="statusText"
                    name="footer[status_text]"
                    placeholder="Available for select projects"
                />
                <p class="text-xs text-muted-foreground">
                    Shown next to the availability dot when “Available for work”
                    is on.
                </p>
            </div>

            <div class="grid gap-2">
                <Label for="footer-status-text-off">Unavailable status</Label>
                <Input
                    id="footer-status-text-off"
                    v-model="statusTextUnavailable"
                    name="footer[status_text_unavailable]"
                    placeholder="Currently unavailable"
                />
            </div>
        </div>

        <div class="grid gap-2">
            <Label for="footer-copyright">Copyright line</Label>
            <Input
                id="footer-copyright"
                v-model="copyright"
                name="footer[copyright]"
                placeholder="© {{year}} {{name}}. All rights reserved."
            />
        </div>

        <div class="grid gap-2 sm:max-w-xs">
            <Label for="footer-back-top">Back to top label</Label>
            <Input
                id="footer-back-top"
                v-model="backToTop"
                name="footer[back_to_top]"
                placeholder="Back to top"
            />
        </div>

        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold">Link columns</p>
                    <p class="text-xs text-muted-foreground">
                        Rendered side by side in the footer middle section.
                    </p>
                </div>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="addColumn"
                >
                    <Plus class="size-4" />
                    Add column
                </Button>
            </div>

            <div
                v-for="(column, ci) in columns"
                :key="ci"
                class="rounded-lg border border-border p-4"
            >
                <div class="flex items-start gap-3">
                    <div class="grid flex-1 gap-2">
                        <Label :for="`footer-col-title-${ci}`">Title</Label>
                        <Input
                            :id="`footer-col-title-${ci}`"
                            v-model="column.title"
                            :name="`footer[columns][${ci}][title]`"
                            placeholder="Quick Links"
                        />
                    </div>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        class="mt-6 shrink-0 text-muted-foreground hover:text-destructive"
                        aria-label="Remove column"
                        @click="removeColumn(ci)"
                    >
                        <Trash2 class="size-4" />
                    </Button>
                </div>

                <div class="mt-4 flex flex-col gap-2">
                    <div
                        v-for="(link, li) in column.links"
                        :key="li"
                        class="grid grid-cols-1 gap-2 sm:grid-cols-[1fr_1.4fr_auto]"
                    >
                        <Input
                            v-model="link.label"
                            :name="`footer[columns][${ci}][links][${li}][label]`"
                            placeholder="Label (e.g. About)"
                        />
                        <Input
                            v-model="link.url"
                            :name="`footer[columns][${ci}][links][${li}][url]`"
                            :placeholder="'URL (e.g. #about, /projects, {{email}})'"
                        />
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="text-muted-foreground hover:text-destructive"
                            aria-label="Remove link"
                            @click="removeLink(column, li)"
                        >
                            <Trash2 class="size-4" />
                        </Button>
                    </div>
                </div>

                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="mt-3"
                    @click="addLink(column)"
                >
                    <Plus class="size-4" />
                    Add link
                </Button>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold">Legal links</p>
                    <p class="text-xs text-muted-foreground">
                        Rendered in the footer bottom bar.
                    </p>
                </div>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="addLegalLink"
                >
                    <Plus class="size-4" />
                    Add link
                </Button>
            </div>

            <div
                v-for="(link, index) in legalLinks"
                :key="index"
                class="grid grid-cols-1 gap-2 sm:grid-cols-[1fr_1.4fr_auto]"
            >
                <Input
                    v-model="link.label"
                    :name="`footer[legal_links][${index}][label]`"
                    placeholder="Label (e.g. Privacy Policy)"
                />
                <Input
                    v-model="link.url"
                    :name="`footer[legal_links][${index}][url]`"
                    placeholder="URL (e.g. /privacy)"
                />
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="text-muted-foreground hover:text-destructive"
                    aria-label="Remove link"
                    @click="removeLegalLink(index)"
                >
                    <Trash2 class="size-4" />
                </Button>
            </div>
        </div>
    </div>
</template>
