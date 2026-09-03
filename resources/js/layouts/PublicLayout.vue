<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, X } from '@lucide/vue';
import { useWindowScroll } from '@vueuse/core';
import { AnimatePresence, MotionConfig, motion } from 'motion-v';
import { computed, ref } from 'vue';
import ScrollProgressBar from '@/components/motion/ScrollProgressBar.vue';
import FooterSection from '@/components/portfolio/FooterSection.vue';
import ThemeToggle from '@/components/portfolio/ThemeToggle.vue';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetTitle } from '@/components/ui/sheet';
import { Toaster } from '@/components/ui/sonner';
import { useScrollSpy } from '@/composables/useScrollSpy';
import { cn } from '@/lib/utils';
import type { Profile } from '@/types';

const page = usePage();

const profile = computed(() => page.props.profile as Profile | undefined);
const isHome = computed(() => page.url === '/' || page.url.startsWith('/?'));
const isProjectsRoute = computed(() => page.url.startsWith('/projects'));

const sections = [
    { id: 'about', label: 'About' },
    { id: 'skills', label: 'Skills' },
    { id: 'projects', label: 'Work' },
    { id: 'experience', label: 'Experience' },
    { id: 'contact', label: 'Contact' },
];

const activeSection = useScrollSpy(sections.map((s) => s.id));
const mobileOpen = ref(false);

const { y: scrollY } = useWindowScroll();
const scrolled = computed(() => scrollY.value > 12);

const initials = computed(() =>
    profile.value?.name
        ?.split(' ')
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join(''),
);

function sectionHref(id: string): string {
    return isHome.value ? `#${id}` : `/#${id}`;
}

function closeMobile() {
    mobileOpen.value = false;
}
</script>

<template>
    <MotionConfig reduced-motion="user">
        <div class="min-h-screen bg-background text-foreground">
            <a
                href="#main"
                class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-100 focus:rounded-md focus:bg-background focus:px-4 focus:py-2 focus:text-foreground focus:ring-2 focus:ring-ring"
            >
                Skip to content
            </a>

            <!-- Laravel-style clean nav -->
            <header
                :class="
                    cn(
                        'sticky top-0 z-50 transition-all duration-200',
                        scrolled
                            ? 'border-b border-border bg-background/95 backdrop-blur-sm'
                            : 'border-transparent bg-transparent',
                    )
                "
            >
                <div
                    class="mx-auto flex h-16 max-w-6xl items-center gap-4 px-4 sm:px-6"
                >
                    <!-- Logo -->
                    <Link
                        href="/"
                        class="group flex items-center gap-2.5"
                        :aria-label="profile?.name ?? 'Home'"
                    >
                        <span
                            class="grid size-9 place-items-center rounded-lg bg-foreground font-display text-sm font-bold text-background transition-opacity duration-150 group-hover:opacity-90"
                        >
                            {{ initials ?? '{ }' }}
                        </span>
                        <span
                            class="hidden text-sm font-semibold tracking-tight sm:inline"
                        >
                            {{ profile?.name ?? 'portfolio' }}
                        </span>
                    </Link>

                    <!-- Section links -->
                    <nav
                        class="ml-auto hidden items-center gap-1 md:flex"
                        aria-label="Primary"
                    >
                        <a
                            v-for="section in sections"
                            :key="section.id"
                            :href="sectionHref(section.id)"
                            :data-active="
                                isHome && activeSection === section.id
                                    ? 'true'
                                    : undefined
                            "
                            class="rounded-lg px-3 py-1.5 text-sm font-medium text-muted-foreground transition-colors duration-150 hover:text-foreground data-[active=true]:text-foreground"
                        >
                            {{ section.label }}
                        </a>
                        <Link
                            href="/projects"
                            :data-active="isProjectsRoute ? 'true' : undefined"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium text-muted-foreground transition-colors duration-150 hover:text-foreground data-[active=true]:text-foreground"
                        >
                            Archive
                        </Link>
                    </nav>

                    <!-- Actions -->
                    <div class="ml-auto flex items-center gap-2 md:ml-0">
                        <ThemeToggle class="hidden sm:inline-flex" />
                        <Button
                            v-if="profile?.resume_url"
                            as-child
                            size="sm"
                            class="btn-laravel-primary hidden sm:inline-flex"
                        >
                            <a href="/resume" target="_blank" rel="noopener"
                                >Resume</a
                            >
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="md:hidden"
                            aria-label="Open menu"
                            @click="mobileOpen = true"
                        >
                            <Menu class="size-5" />
                        </Button>
                    </div>
                </div>

                <ScrollProgressBar />
            </header>

            <main id="main" class="relative">
                <AnimatePresence mode="wait">
                    <motion.div
                        :key="page.url"
                        :initial="{ opacity: 0, y: 12 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :exit="{ opacity: 0, y: -12 }"
                        :transition="{
                            duration: 0.35,
                            ease: [0.16, 1, 0.3, 1],
                        }"
                    >
                        <slot />
                    </motion.div>
                </AnimatePresence>

                <FooterSection />
            </main>

            <!-- Mobile sheet -->
            <Sheet v-model:open="mobileOpen">
                <SheetContent
                    side="right"
                    class="w-72 border-border bg-background"
                >
                    <SheetTitle
                        class="flex items-center justify-between px-4 pt-4 text-sm font-semibold"
                    >
                        <span>{{
                            profile?.name?.split(' ')[0] ?? 'Menu'
                        }}</span>
                        <button
                            type="button"
                            class="rounded-lg p-1 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                            aria-label="Close menu"
                            @click="closeMobile"
                        >
                            <X class="size-4" />
                        </button>
                    </SheetTitle>
                    <nav class="mt-4 flex flex-col gap-1 px-2">
                        <a
                            v-for="section in sections"
                            :key="section.id"
                            :href="sectionHref(section.id)"
                            class="rounded-lg px-4 py-2.5 text-sm transition-colors hover:bg-muted"
                            @click="closeMobile"
                        >
                            {{ section.label }}
                        </a>
                        <Link
                            href="/projects"
                            class="rounded-lg px-4 py-2.5 text-sm transition-colors hover:bg-muted"
                            @click="closeMobile"
                        >
                            Archive
                        </Link>
                    </nav>
                    <div
                        class="mt-6 flex items-center justify-between gap-2 border-t border-border px-4 pt-4"
                    >
                        <ThemeToggle />
                        <a
                            v-if="profile?.resume_url"
                            href="/resume"
                            target="_blank"
                            rel="noopener"
                            class="text-sm font-medium text-brand hover:underline"
                        >
                            resume.pdf
                        </a>
                    </div>
                </SheetContent>
            </Sheet>

            <Toaster position="bottom-right" rich-colors />
        </div>
    </MotionConfig>
</template>
