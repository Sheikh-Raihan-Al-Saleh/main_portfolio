<script setup lang="ts">
import SocialIcon from '@/components/site/SocialIcon.vue';
import { cn } from '@/lib/utils';
import type { SocialLink } from '@/types';
import type { SocialLinksProps } from '@/types';

/**
 * A row of round social-profile buttons.
 */
const props = defineProps<SocialLinksProps>();

const labels: Record<SocialLink['platform'], string> = {
    github: 'GitHub',
    linkedin: 'LinkedIn',
    x: 'X',
    dribbble: 'Dribbble',
    youtube: 'YouTube',
    whatsapp: 'WhatsApp',
    website: 'Website',
};
</script>

<template>
    <ul v-if="links.length" :class="cn('flex flex-wrap gap-2', props.class)">
        <li v-for="link in links" :key="link.platform">
            <a
                :href="link.url"
                target="_blank"
                rel="noopener noreferrer"
                class="grid size-11 place-items-center rounded-full border border-line text-ink transition-colors hover:border-ink hover:bg-ink hover:text-paper"
                :aria-label="link.label ?? labels[link.platform]"
            >
                <SocialIcon :name="link.platform" />
            </a>
        </li>
    </ul>
</template>
