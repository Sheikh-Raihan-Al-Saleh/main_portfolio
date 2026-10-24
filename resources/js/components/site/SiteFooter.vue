<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUp, ArrowUpRight } from '@lucide/vue';
import SocialIcon from '@/components/portfolio/SocialIcon.vue';
import BrandMark from '@/components/site/BrandMark.vue';
import { useFooterContent } from '@/composables/useFooterContent';
import type { PublicNav } from '@/composables/usePublicNav';

/**
 * The public footer, shared by both faces. Everything in it is edited in
 * Admin → Company / Founder → Footer for whichever record owns the page.
 */
type Props = {
    nav: PublicNav;
};

defineProps<Props>();

const {
    columns,
    legalLinks,
    socialLinks,
    isAvailable,
    statusText,
    copyright,
    backToTop,
} = useFooterContent();

function scrollToTop(): void {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<template>
    <footer class="relative overflow-hidden border-t border-border bg-card">
        <div
            class="ui-blob ui-blob-a -bottom-40 left-1/4 size-[28rem] opacity-25"
            aria-hidden="true"
        />

        <div
            class="site-container relative grid gap-12 py-16 lg:grid-cols-12 lg:py-20"
        >
            <div class="flex flex-col gap-5 lg:col-span-4">
                <Link
                    :href="nav.homeHref.value"
                    :aria-label="nav.owner.value?.name ?? 'Home'"
                    class="inline-flex w-fit"
                >
                    <BrandMark
                        :logo-url="nav.brandLogoUrl.value"
                        :name="nav.owner.value?.name"
                        :initials="nav.initials.value"
                        initials-class="footer-logo-icon rounded-xl bg-gradient-to-br from-[var(--grad-from)] to-[var(--grad-to)] text-white"
                    />
                </Link>
                <p
                    v-if="nav.owner.value?.tagline"
                    class="max-w-sm text-sm leading-relaxed text-muted-foreground"
                >
                    {{ nav.owner.value.tagline }}
                </p>
                <p
                    v-if="statusText"
                    class="inline-flex w-fit items-center gap-2.5 rounded-full border border-border bg-background px-3 py-1.5 text-xs font-medium"
                >
                    <span
                        :class="
                            isAvailable
                                ? 'ui-pulse-dot'
                                : 'size-2 rounded-full bg-muted-foreground'
                        "
                    />
                    {{ statusText }}
                </p>
                <div v-if="socialLinks.length" class="flex gap-2">
                    <a
                        v-for="link in socialLinks"
                        :key="link.label"
                        :href="link.href"
                        target="_blank"
                        rel="noopener noreferrer"
                        :aria-label="link.label"
                        class="grid size-10 place-items-center rounded-full border border-border bg-background text-muted-foreground transition-colors hover:border-brand hover:text-brand"
                    >
                        <SocialIcon :name="link.name" class="size-4" />
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-8 sm:grid-cols-4 lg:col-span-8">
                <div v-for="column in columns" :key="column.title">
                    <h2 class="text-sm font-semibold">{{ column.title }}</h2>
                    <ul class="mt-4 grid gap-2.5 text-sm">
                        <li v-for="item in column.links" :key="item.label">
                            <a
                                v-if="item.href"
                                :href="item.href"
                                :target="item.external ? '_blank' : undefined"
                                :rel="
                                    item.external
                                        ? 'noopener noreferrer'
                                        : undefined
                                "
                                class="inline-flex items-center gap-1 text-muted-foreground transition-colors hover:text-foreground"
                            >
                                {{ item.label }}
                                <ArrowUpRight
                                    v-if="item.external"
                                    class="size-3 opacity-60"
                                />
                            </a>
                            <span v-else class="text-muted-foreground">{{
                                item.label
                            }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="relative border-t border-border">
            <div
                class="site-container flex flex-col gap-4 py-6 text-xs text-muted-foreground sm:flex-row sm:items-center sm:justify-between"
            >
                <p v-if="copyright">{{ copyright }}</p>
                <div
                    v-if="legalLinks.length"
                    class="flex flex-wrap gap-x-5 gap-y-1"
                >
                    <template
                        v-for="(link, i) in legalLinks"
                        :key="`${link.label}-${i}`"
                    >
                        <a
                            v-if="link.href"
                            :href="link.href"
                            :target="link.external ? '_blank' : undefined"
                            :rel="
                                link.external
                                    ? 'noopener noreferrer'
                                    : undefined
                            "
                            class="hover:text-foreground"
                            >{{ link.label }}</a
                        >
                        <span v-else>{{ link.label }}</span>
                    </template>
                </div>
                <a
                    href="#top"
                    class="inline-flex items-center gap-1.5 font-medium text-foreground hover:text-brand"
                    @click.prevent="scrollToTop"
                >
                    <ArrowUp class="size-3.5" />
                    {{ backToTop }}
                </a>
            </div>
        </div>
    </footer>
</template>
