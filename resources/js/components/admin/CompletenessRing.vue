<script setup lang="ts">
import { computed } from 'vue';

/**
 * A single completeness score: a progress ring with the percentage in the
 * middle and the missing fields listed underneath, each linking to the page
 * that fixes it. Pure SVG, no dependencies.
 */
type Props = {
    label: string;
    percent: number;
    missing: string[];
    /** Route the "fix" links point at; missing items append to it. */
    href: string;
    size?: number;
};

const props = withDefaults(defineProps<Props>(), { size: 120 });

const radius = computed(() => (props.size - 16) / 2);
const circumference = computed(() => 2 * Math.PI * radius.value);
const dash = computed(() => (props.percent / 100) * circumference.value);

const ringColor = computed(() => {
    if (props.percent >= 80) {
        return 'text-emerald-500';
    }

    if (props.percent >= 50) {
        return 'text-amber-500';
    }

    return 'text-rose-500';
});

/** The sidebar pages each missing item is edited on. */
const EDIT_HREFS: Record<string, string> = {
    Name: '/admin/company',
    Headline: '/admin/company',
    Tagline: '/admin/company',
    Bio: '/admin/company',
    Mission: '/admin/company',
    Location: '/admin/company',
    'Public email': '/admin/company',
    Logo: '/admin/company',
    'Social links': '/admin/company',
    'Meta description': '/admin/company',
    'Home blocks': '/admin/company',
    Clients: '/admin/clients',
    Avatar: '/admin/profile',
    Roles: '/admin/profile',
    Skills: '/admin/skills',
    Experience: '/admin/experiences',
    Education: '/admin/educations',
    Resume: '/admin/profile',
};

function hrefFor(missing: string): string {
    return EDIT_HREFS[missing] ?? props.href;
}
</script>

<template>
    <div class="flex flex-col items-center gap-3">
        <div
            class="relative"
            :style="{ width: `${props.size}px`, height: `${props.size}px` }"
        >
            <svg
                :width="props.size"
                :height="props.size"
                :viewBox="`0 0 ${props.size} ${props.size}`"
                class="-rotate-90"
                role="img"
                :aria-label="`${props.label}: ${props.percent}% complete`"
            >
                <circle
                    :cx="props.size / 2"
                    :cy="props.size / 2"
                    :r="radius"
                    fill="none"
                    class="stroke-muted"
                    stroke-width="9"
                />
                <circle
                    :cx="props.size / 2"
                    :cy="props.size / 2"
                    :r="radius"
                    fill="none"
                    :class="ringColor"
                    stroke="currentColor"
                    stroke-width="9"
                    stroke-linecap="round"
                    :stroke-dasharray="`${dash} ${circumference - dash}`"
                    class="transition-[stroke-dasharray] duration-700"
                />
            </svg>

            <div
                class="absolute inset-0 grid place-items-center"
            >
                <div class="text-center">
                    <p class="text-2xl font-bold tabular-nums">
                        {{ props.percent }}%
                    </p>
                    <p class="text-[10px] tracking-wide text-muted-foreground uppercase">
                        {{ props.label }}
                    </p>
                </div>
            </div>
        </div>

        <ul v-if="props.missing.length" class="w-full space-y-1">
            <li
                v-for="missing in props.missing.slice(0, 4)"
                :key="missing"
            >
                <a
                    :href="hrefFor(missing)"
                    class="inline-flex w-full items-center gap-1.5 rounded-md px-2 py-1 text-xs text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                >
                    <span
                        class="size-1.5 shrink-0 rounded-full bg-amber-500"
                        aria-hidden="true"
                    />
                    <span class="truncate">Add {{ missing.toLowerCase() }}</span>
                </a>
            </li>
            <li
                v-if="props.missing.length > 4"
                class="px-2 text-xs text-muted-foreground"
            >
                +{{ props.missing.length - 4 }} more
            </li>
        </ul>
        <p v-else class="text-xs text-emerald-600 dark:text-emerald-400">
            Everything is filled in.
        </p>
    </div>
</template>
