<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, ArrowRight, Check, Save } from '@lucide/vue';
import { computed, nextTick, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { FormTab } from './types';

/**
 * Splits a long admin form into tabs, so each screen shows one topic instead
 * of a page-long scroll.
 *
 * Every tab's fields stay mounted (panels are hidden with v-show), so the
 * surrounding <Form> still submits all of them together. Each tab lists the
 * field names it owns; when validation fails, those tabs get an error badge
 * and the first one with an error is opened automatically. A sticky bar keeps
 * Save in reach wherever the editor has scrolled to.
 *
 * Use inside an Inertia <Form v-slot="{ errors, processing }"> and pass both.
 * Panel content goes in `#tab-<id>` slots.
 */
type Props = {
    tabs: FormTab[];
    errors?: Record<string, string>;
    processing?: boolean;
    submitLabel?: string;
    cancelHref?: string | null;
};

const props = withDefaults(defineProps<Props>(), {
    errors: () => ({}),
    processing: false,
    submitLabel: 'Save changes',
    cancelHref: null,
});

const active = ref(props.tabs[0]?.id ?? '');
const dirty = ref(false);

function errorCount(tab: FormTab): number {
    const owned = tab.fields ?? [];

    return Object.keys(props.errors).filter((key) =>
        owned.some(
            (field) =>
                key === field ||
                key.startsWith(`${field}.`) ||
                key.startsWith(`${field}[`),
        ),
    ).length;
}

const totalErrors = computed(() => Object.keys(props.errors).length);

const index = computed(() =>
    props.tabs.findIndex((tab) => tab.id === active.value),
);
const current = computed(() => props.tabs[index.value]);
const previous = computed(() => props.tabs[index.value - 1] ?? null);
const next = computed(() => props.tabs[index.value + 1] ?? null);

function open(id: string) {
    active.value = id;
}

/**
 * The browser blocks submit on an empty `required` field, but cannot focus
 * one inside a hidden tab, so nothing would happen. Open that tab first, then
 * let the browser show its message on the field.
 */
function onInvalid(event: Event) {
    const field = event.target as HTMLInputElement | null;
    const panel = field?.closest<HTMLElement>('[data-tab]');

    if (!field || !panel?.dataset.tab || panel.dataset.tab === active.value) {
        return;
    }

    active.value = panel.dataset.tab;
    nextTick(() => field.reportValidity());
}

/** Jump to the first tab that failed validation. */
function showFirstError() {
    const failing = props.tabs.find((tab) => errorCount(tab) > 0);

    if (failing) {
        active.value = failing.id;
    }
}

watch(
    () => props.errors,
    (errors) => {
        if (Object.keys(errors).length > 0) {
            showFirstError();
        }
    },
);

// A save that comes back without errors clears the unsaved marker.
watch(
    () => props.processing,
    (now, before) => {
        if (before && !now && totalErrors.value === 0) {
            dirty.value = false;
        }
    },
);
</script>

<template>
    <div
        class="flex flex-col gap-6"
        @input="dirty = true"
        @invalid.capture="onInvalid"
        @change="dirty = true"
    >
        <div class="grid gap-6 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <!-- Tab list: pills on small screens, a sticky rail on large -->
            <nav
                aria-label="Form sections"
                class="lg:sticky lg:top-24 lg:self-start"
            >
                <div
                    class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 lg:hidden"
                >
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        type="button"
                        :aria-current="active === tab.id ? 'step' : undefined"
                        :class="
                            cn(
                                'relative inline-flex shrink-0 items-center gap-2 rounded-full border px-3.5 py-2 text-sm font-medium transition-colors',
                                active === tab.id
                                    ? 'border-transparent bg-primary text-primary-foreground'
                                    : 'border-border bg-card text-muted-foreground',
                            )
                        "
                        @click="open(tab.id)"
                    >
                        <component
                            :is="tab.icon"
                            v-if="tab.icon"
                            class="size-4"
                        />
                        {{ tab.label }}
                        <span
                            v-if="errorCount(tab)"
                            class="grid size-4 place-items-center rounded-full bg-destructive text-[0.6rem] text-white"
                            >{{ errorCount(tab) }}</span
                        >
                    </button>
                </div>

                <ol class="ui-card hidden flex-col gap-1 p-2 lg:flex">
                    <li v-for="(tab, i) in tabs" :key="tab.id">
                        <button
                            type="button"
                            :aria-current="
                                active === tab.id ? 'step' : undefined
                            "
                            :class="
                                cn(
                                    'group flex w-full items-start gap-3 rounded-xl px-3 py-2.5 text-left transition-colors',
                                    active === tab.id
                                        ? 'bg-brand-subtle'
                                        : 'hover:bg-muted',
                                )
                            "
                            @click="open(tab.id)"
                        >
                            <span
                                :class="
                                    cn(
                                        'grid size-8 shrink-0 place-items-center rounded-lg transition-colors',
                                        active === tab.id
                                            ? 'bg-gradient-to-br from-[var(--signal)] to-[var(--grad-to)] text-white'
                                            : 'bg-muted text-muted-foreground group-hover:text-foreground',
                                    )
                                "
                            >
                                <component
                                    :is="tab.icon"
                                    v-if="tab.icon"
                                    class="size-4"
                                />
                                <span v-else class="text-xs font-semibold">{{
                                    i + 1
                                }}</span>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span
                                    :class="
                                        cn(
                                            'flex items-center gap-2 text-sm font-semibold',
                                            active === tab.id
                                                ? 'text-brand'
                                                : 'text-foreground',
                                        )
                                    "
                                >
                                    {{ tab.label }}
                                    <span
                                        v-if="errorCount(tab)"
                                        class="ml-auto grid size-5 place-items-center rounded-full bg-destructive text-[0.65rem] text-white"
                                        :aria-label="`${errorCount(tab)} errors`"
                                        >{{ errorCount(tab) }}</span
                                    >
                                </span>
                                <span
                                    v-if="tab.description"
                                    class="mt-0.5 block text-xs leading-snug text-muted-foreground"
                                >
                                    {{ tab.description }}
                                </span>
                            </span>
                        </button>
                    </li>
                </ol>
            </nav>

            <!-- Panels (all mounted, so every field submits) -->
            <div class="min-w-0">
                <div
                    v-for="tab in tabs"
                    v-show="active === tab.id"
                    :key="tab.id"
                    :data-tab="tab.id"
                    class="flex flex-col gap-5"
                >
                    <header class="flex items-start gap-3">
                        <span v-if="tab.icon" class="ui-icon-tile shrink-0">
                            <component :is="tab.icon" class="size-5" />
                        </span>
                        <div>
                            <p
                                class="text-xs font-medium text-muted-foreground"
                            >
                                Step {{ tabs.indexOf(tab) + 1 }} of
                                {{ tabs.length }}
                            </p>
                            <h2 class="admin-title text-xl font-bold">
                                {{ tab.label }}
                            </h2>
                            <p
                                v-if="tab.description"
                                class="mt-0.5 text-sm text-muted-foreground"
                            >
                                {{ tab.description }}
                            </p>
                        </div>
                    </header>

                    <slot :name="`tab-${tab.id}`" />
                </div>

                <!-- Walk the steps without hunting for the rail -->
                <div class="mt-6 flex items-center justify-between gap-3">
                    <Button
                        v-if="previous"
                        type="button"
                        variant="ghost"
                        size="sm"
                        @click="open(previous.id)"
                    >
                        <ArrowLeft class="size-4" />
                        {{ previous.label }}
                    </Button>
                    <span v-else />
                    <Button
                        v-if="next"
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="open(next.id)"
                    >
                        Next: {{ next.label }}
                        <ArrowRight class="size-4" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Sticky save bar -->
        <div
            class="ui-glass sticky bottom-4 z-20 flex flex-wrap items-center justify-between gap-3 rounded-2xl px-4 py-3 shadow-[var(--shadow-lift)]"
        >
            <button
                v-if="totalErrors"
                type="button"
                class="inline-flex items-center gap-2 text-sm font-medium text-destructive"
                @click="showFirstError"
            >
                <AlertCircle class="size-4" />
                {{ totalErrors }}
                {{ totalErrors === 1 ? 'field needs' : 'fields need' }}
                attention
            </button>
            <p
                v-else-if="dirty"
                class="inline-flex items-center gap-2 text-sm text-muted-foreground"
            >
                <span class="size-2 rounded-full bg-amber-500" />
                Unsaved changes
            </p>
            <p
                v-else
                class="inline-flex items-center gap-2 text-sm text-muted-foreground"
            >
                <Check class="size-4 text-emerald-500" />
                {{ current?.label }} · everything saved together
            </p>

            <div class="flex items-center gap-2">
                <Button
                    v-if="cancelHref"
                    as-child
                    type="button"
                    variant="ghost"
                >
                    <Link :href="cancelHref">Cancel</Link>
                </Button>
                <Button type="submit" :disabled="processing" class="min-w-32">
                    <Save class="size-4" />
                    {{ processing ? 'Saving…' : submitLabel }}
                </Button>
            </div>
        </div>
    </div>
</template>
