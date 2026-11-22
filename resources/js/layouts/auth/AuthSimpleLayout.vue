<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft, ShieldCheck } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import CodeEditor from '@/components/site/CodeEditor.vue';
import CodeRain from '@/components/site/CodeRain.vue';
import CodeTerminal from '@/components/site/CodeTerminal.vue';
import GlitchText from '@/components/site/GlitchText.vue';
import LiquidImage from '@/components/site/LiquidImage.vue';
import { useSiteScope } from '@/composables/useSiteScope';
import type { CodeToken } from '@/lib/code';
import { EASE_OUT } from '@/lib/motion';
import { home } from '@/routes';

/**
 * The frame for every sign-in page (log in, forgot / reset password,
 * two-factor, verify email), built like an engineer's workspace.
 *
 * Left, a dark IDE: the studio's logo in the masthead, an editor window that
 * types out the sign-in code (written with the studio's real name and real
 * project titles), a live terminal session, and the real published covers
 * as a "preview" strip of liquid images, all over Matrix-style code rain
 * that reacts to the pointer, under CRT scanlines. Right, the form on paper
 * under a shell prompt and the page's title, which glitches every few
 * seconds. On phones the IDE becomes a banner.
 *
 * The logo and company name come from the shared Inertia props; the covers
 * from the `showcase` prop the Fortify views pass (FortifyServiceProvider).
 */
defineProps<{
    title?: string;
    description?: string;
}>();

// Portalled toasts and dialogs follow the editorial palette too.
useSiteScope('studio');

type Brand = { name?: string | null; logo_url?: string | null };
type Showcase = { title: string; cover: string }[];

const page = usePage();

const company = computed(() => page.props.company as Brand | undefined);
const profile = computed(() => page.props.profile as Brand | undefined);

const brandName = computed(
    () => company.value?.name ?? profile.value?.name ?? 'Studio',
);
const logoUrl = computed(
    () => profile.value?.logo_url ?? company.value?.logo_url ?? null,
);

const showcase = computed(() =>
    ((page.props.showcase as Showcase | undefined) ?? []).slice(0, 3),
);

const year = new Date().getFullYear();

/** A file-name-safe slug of the studio, for the editor's tabs and paths. */
const slug = computed(
    () =>
        brandName.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '') || 'studio',
);

function str(value: string): CodeToken {
    return { text: `'${value.replace(/'/g, "\\'")}'`, kind: 'str' };
}

/** The sign-in code the editor types, written with the studio's real data. */
const code = computed<CodeToken[][]>(() => {
    const work = showcase.value
        .slice(0, 2)
        .map((item) =>
            item.title.length > 22 ? `${item.title.slice(0, 21)}…` : item.title,
        );

    const workLine: CodeToken[] = [
        { text: '  ' },
        { text: 'work', kind: 'prop' },
        { text: ': [', kind: 'punc' },
    ];

    work.forEach((title, i) => {
        workLine.push(str(title));

        if (i < work.length - 1) {
            workLine.push({ text: ', ', kind: 'punc' });
        }
    });

    workLine.push({ text: '],', kind: 'punc' });

    return [
        [
            {
                text: `// ${brandName.value} — admin console, ${year}`,
                kind: 'com',
            },
        ],
        [
            { text: 'import', kind: 'kw' },
            { text: ' { ', kind: 'punc' },
            { text: 'session' },
            { text: ' } ', kind: 'punc' },
            { text: 'from', kind: 'kw' },
            { text: ' ' },
            str('@studio/auth'),
        ],
        [],
        [
            { text: 'const', kind: 'kw' },
            { text: ' studio ' },
            { text: '=', kind: 'punc' },
            { text: ' {', kind: 'punc' },
        ],
        [
            { text: '  ' },
            { text: 'name', kind: 'prop' },
            { text: ': ', kind: 'punc' },
            str(brandName.value),
            { text: ',', kind: 'punc' },
        ],
        ...(work.length ? [workLine] : []),
        [
            { text: '  ' },
            { text: 'shipping', kind: 'prop' },
            { text: ': ', kind: 'punc' },
            { text: 'true', kind: 'num' },
            { text: ',', kind: 'punc' },
        ],
        [{ text: '}', kind: 'punc' }],
        [],
        [
            { text: 'export async function', kind: 'kw' },
            { text: ' signIn', kind: 'fn' },
            { text: '(', kind: 'punc' },
            { text: 'admin' },
            { text: ': ', kind: 'punc' },
            { text: 'Admin', kind: 'type' },
            { text: ') {', kind: 'punc' },
        ],
        [
            { text: '  ' },
            { text: 'await', kind: 'kw' },
            { text: ' session' },
            { text: '.', kind: 'punc' },
            { text: 'verify', kind: 'fn' },
            { text: '(', kind: 'punc' },
            { text: 'admin' },
            { text: ', { ', kind: 'punc' },
            { text: 'twoFactor', kind: 'prop' },
            { text: ': ', kind: 'punc' },
            { text: 'true', kind: 'num' },
            { text: ' })', kind: 'punc' },
        ],
        [
            { text: '  ' },
            { text: 'return', kind: 'kw' },
            { text: ' studio' },
            { text: '.', kind: 'punc' },
            { text: 'open', kind: 'fn' },
            { text: '(', kind: 'punc' },
            str('/admin'),
            { text: ')', kind: 'punc' },
        ],
        [{ text: '}', kind: 'punc' }],
    ];
});

const tabs = computed(() => ['auth.ts', 'projects.ts', 'clients.ts']);

const status = computed(() => [
    '⎇ main',
    '✓ 0 problems',
    'TypeScript',
    'UTF-8',
    `~/${slug.value}`,
]);

const session = computed(() => [
    { text: `cd ~/${slug.value} && php artisan serve`, kind: 'cmd' as const },
    { text: 'Server running on [http://127.0.0.1:8000]', kind: 'dim' as const },
    { text: 'npm run build', kind: 'cmd' as const },
    { text: '✓ built in 11.2s', kind: 'ok' as const },
    { text: 'Waiting for admin sign-in…', kind: 'dim' as const },
]);
</script>

<template>
    <div
        data-site="studio"
        class="grid min-h-svh bg-background text-foreground lg:grid-cols-12"
    >
        <!-- The IDE -->
        <aside
            class="relative flex flex-col overflow-hidden bg-[#06080d] text-[#e6edf3] lg:col-span-7 lg:min-h-svh"
        >
            <!-- Code rain behind the windows, a vignette so they stay legible,
                 and CRT scanlines with a slow scan bar over everything -->
            <div
                class="pointer-events-none absolute inset-0 opacity-80 [--rain-head:#ffffff] [--rain-tail:#5b7bff]"
            >
                <CodeRain />
            </div>
            <div
                class="auth-vignette pointer-events-none absolute inset-0"
                aria-hidden="true"
            />
            <div
                class="auth-scanlines pointer-events-none absolute inset-0 z-10"
                aria-hidden="true"
            />

            <div
                class="relative flex flex-1 flex-col gap-8 p-6 sm:p-10 lg:p-12"
            >
                <!-- Masthead -->
                <div
                    class="ui-label flex items-center justify-between gap-4 border-b border-white/10 pb-4"
                >
                    <Link
                        :href="home()"
                        class="inline-flex items-center gap-3 text-white"
                    >
                        <span
                            v-if="logoUrl"
                            class="inline-flex items-center rounded-sm bg-white px-2.5 py-1.5"
                        >
                            <img
                                :src="logoUrl"
                                :alt="brandName"
                                class="h-8 w-auto max-w-36 object-contain"
                            />
                        </span>
                        <span
                            v-else
                            class="grid size-9 place-items-center bg-[var(--accent-fill)] text-[var(--accent-ink)]"
                        >
                            <AppLogoIcon class="size-5 fill-current" />
                        </span>
                        <span class="hidden sm:inline">{{ brandName }}</span>
                    </Link>
                    <span
                        class="hidden items-center gap-2 text-white/60 sm:inline-flex"
                    >
                        <span class="size-1.5 rounded-full bg-emerald-400" />
                        ssh · admin@{{ slug }}
                    </span>
                </div>

                <!-- On phones, one prompt line instead of the whole IDE -->
                <p
                    class="flex items-center gap-2 font-mono text-sm text-white/75 lg:hidden"
                >
                    <span class="text-[#82aaff]">$</span>
                    ssh admin@{{ slug }}
                    <span
                        class="auth-caret inline-block h-4 w-2 bg-[var(--accent-fill)]"
                        aria-hidden="true"
                    />
                </p>

                <!-- Editor and terminal -->
                <div
                    class="relative hidden flex-1 flex-col justify-center lg:flex"
                >
                    <motion.div
                        :initial="{ opacity: 0, y: 30 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{
                            duration: 1,
                            ease: EASE_OUT,
                            delay: 0.1,
                        }"
                    >
                        <CodeEditor
                            :tabs="tabs"
                            :lines="code"
                            :status="status"
                            class="max-w-2xl"
                        />
                    </motion.div>

                    <motion.div
                        :initial="{ opacity: 0, x: 30 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :transition="{
                            duration: 1,
                            ease: EASE_OUT,
                            delay: 0.5,
                        }"
                        class="relative mt-4 w-full max-w-sm self-end"
                    >
                        <CodeTerminal
                            title="zsh — admin"
                            :lines="session"
                            :pace="0.7"
                            class="border-white/10 bg-[#010409]/90"
                        />
                    </motion.div>
                </div>

                <!-- Real work, as a live preview strip -->
                <div
                    v-if="showcase.length"
                    class="hidden flex-col gap-3 lg:flex"
                    data-testid="auth-showcase"
                >
                    <p class="font-mono text-xs text-[#7f8bb3]">
                        // preview — live work
                    </p>
                    <div class="grid grid-cols-3 gap-3">
                        <motion.figure
                            v-for="(item, i) in showcase"
                            :key="item.cover"
                            :initial="{ opacity: 0, y: 24 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{
                                duration: 1,
                                ease: EASE_OUT,
                                delay: 0.8 + i * 0.12,
                            }"
                            class="overflow-hidden rounded-md border border-white/10 bg-[#0d1117]"
                        >
                            <div
                                class="flex items-center gap-1.5 border-b border-white/10 px-2.5 py-1.5"
                                aria-hidden="true"
                            >
                                <span
                                    class="size-1.5 rounded-full bg-white/25"
                                />
                                <span
                                    class="size-1.5 rounded-full bg-white/25"
                                />
                                <span
                                    class="size-1.5 rounded-full bg-white/25"
                                />
                            </div>
                            <LiquidImage
                                :src="item.cover"
                                :alt="item.title"
                                class="aspect-[16/10] bg-white/5"
                            />
                            <figcaption
                                class="truncate px-2.5 py-1.5 font-mono text-[11px] text-white/60"
                            >
                                {{ item.title }}
                            </figcaption>
                        </motion.figure>
                    </div>
                </div>
            </div>
        </aside>

        <!-- The form -->
        <main
            class="relative flex flex-col justify-between gap-10 p-6 sm:p-10 lg:col-span-5 lg:p-12"
        >
            <div class="ui-label flex items-center justify-between gap-4">
                <Link
                    :href="home()"
                    class="inline-flex items-center gap-2 text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ArrowLeft class="size-3.5" />
                    Back to site
                </Link>
                <span class="text-muted-foreground">(01) — Sign in</span>
            </div>

            <motion.div
                :initial="{ opacity: 0, y: 24 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.9, ease: EASE_OUT, delay: 0.15 }"
                class="mx-auto w-full max-w-md"
            >
                <div
                    class="mb-10 flex flex-col gap-4 border-t border-border pt-6"
                >
                    <p
                        class="flex items-center gap-2 font-mono text-sm text-muted-foreground"
                    >
                        <span class="ui-accent">$</span>
                        sudo {{ slug }} --login
                        <span
                            class="auth-caret inline-block h-4 w-2 bg-foreground"
                            aria-hidden="true"
                        />
                    </p>
                    <h1 class="ui-display text-[clamp(2.75rem,5vw,4.5rem)]">
                        <GlitchText :text="title ?? ''" />
                    </h1>
                    <p v-if="description" class="text-muted-foreground">
                        {{ description }}
                    </p>
                </div>

                <div class="auth-form">
                    <slot />
                </div>
            </motion.div>

            <p
                class="ui-label flex items-center justify-between gap-4 text-muted-foreground"
            >
                <span class="inline-flex items-center gap-1.5">
                    <ShieldCheck class="size-3.5" />
                    TLS · CSRF · two-factor ready
                </span>
                <span>© {{ year }}</span>
            </p>
        </main>
    </div>
</template>

<style scoped>
/* Darkens the rain toward the middle, where the editor sits. */
.auth-vignette {
    background: radial-gradient(
        ellipse 70% 60% at 40% 45%,
        rgb(6 8 13 / 0.85),
        rgb(6 8 13 / 0.35) 60%,
        transparent 100%
    );
}

/* CRT scanlines, plus a soft bright bar that sweeps down the screen. */
.auth-scanlines {
    background: repeating-linear-gradient(
        to bottom,
        rgb(255 255 255 / 0.025) 0 1px,
        transparent 1px 3px
    );
}

.auth-scanlines::after {
    content: '';
    position: absolute;
    inset-inline: 0;
    top: 0;
    height: 18%;
    background: linear-gradient(
        to bottom,
        transparent,
        rgb(129 147 255 / 0.06),
        transparent
    );
    animation: auth-scan 7s linear infinite;
}

@keyframes auth-scan {
    from {
        transform: translateY(-100%);
    }
    to {
        transform: translateY(600%);
    }
}

.auth-caret {
    animation: auth-caret 1s steps(1) infinite;
}

@keyframes auth-caret {
    50% {
        opacity: 0;
    }
}

/* The shadcn controls inside the form, dressed for the editorial system. */
.auth-form :deep(input:not([type='checkbox'])) {
    height: 3rem;
    border-radius: 0.25rem;
    background: transparent;
    font-family: var(--font-mono);
    font-size: 0.95rem;
}

.auth-form :deep(label) {
    font-family: var(--font-mono);
    font-size: 0.72rem;
    font-weight: 500;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.auth-form :deep(button[type='submit']) {
    height: 3.25rem;
    border-radius: 9999px;
    background: var(--accent-fill);
    color: var(--accent-ink);
    font-family: var(--font-mono);
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    transition:
        background-color 0.25s ease,
        color 0.25s ease;
}

.auth-form :deep(button[type='submit']:hover) {
    background: var(--foreground);
    color: var(--background);
}

@media (prefers-reduced-motion: reduce) {
    .auth-caret,
    .auth-scanlines::after {
        animation: none;
    }
}
</style>
