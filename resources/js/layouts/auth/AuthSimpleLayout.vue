<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ShieldCheck } from '@lucide/vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import CircuitBackground from '@/components/site/CircuitBackground.vue';
import CodeTerminal from '@/components/site/CodeTerminal.vue';
import { useSiteScope } from '@/composables/useSiteScope';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();

useSiteScope('admin');

const bootLog = [
    { text: 'ssh admin@studio', kind: 'cmd' as const },
    { text: 'Verifying identity…', kind: 'dim' as const },
    { text: '✓ Secure session ready', kind: 'ok' as const },
];
</script>

<template>
    <div
        data-site="admin"
        class="grid min-h-svh bg-background text-foreground lg:grid-cols-2"
    >
        <!-- Brand panel -->
        <div
            class="relative hidden overflow-hidden bg-gradient-to-br from-[var(--signal)] to-[var(--grad-to)] p-10 text-white lg:flex lg:flex-col"
        >
            <div
                class="absolute inset-0 opacity-70 [--background:transparent] [--foreground:#ffffff] [--grad-from:#ffffff] [--grad-to:#ffffff]"
                aria-hidden="true"
            >
                <CircuitBackground />
            </div>
            <div
                class="ui-blob -top-24 -left-24 size-96 bg-white/25"
                aria-hidden="true"
            />

            <Link
                :href="home()"
                class="relative flex items-center gap-2 font-semibold"
            >
                <span
                    class="grid size-9 place-items-center rounded-xl bg-white/15 backdrop-blur"
                >
                    <AppLogoIcon class="size-5 fill-current text-white" />
                </span>
                Admin console
            </Link>

            <div class="relative mt-auto max-w-md">
                <h2 class="ui-display text-4xl">
                    Run both sites from one place.
                </h2>
                <p class="mt-4 text-white/80">
                    Projects, case studies, clients and the inbox — everything
                    the studio and the portfolio show.
                </p>
                <CodeTerminal
                    title="session"
                    :lines="bootLog"
                    class="mt-8 w-72 border-white/15"
                />
            </div>
        </div>

        <!-- Form -->
        <div
            class="relative flex flex-col items-center justify-center p-6 md:p-10"
        >
            <div
                class="pointer-events-none absolute inset-0 lg:hidden"
                aria-hidden="true"
            >
                <CircuitBackground />
            </div>
            <div class="ui-card relative w-full max-w-sm p-8">
                <div class="mb-8 flex flex-col items-center gap-4 text-center">
                    <Link
                        :href="home()"
                        class="ui-icon-tile size-12 rounded-2xl"
                    >
                        <AppLogoIcon class="size-6 fill-current" />
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-1.5">
                        <h1 class="admin-title text-2xl font-bold">
                            {{ title }}
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
            <p
                class="relative mt-6 inline-flex items-center gap-1.5 text-xs text-muted-foreground"
            >
                <ShieldCheck class="size-3.5" />
                Protected area · two-factor ready
            </p>
        </div>
    </div>
</template>
