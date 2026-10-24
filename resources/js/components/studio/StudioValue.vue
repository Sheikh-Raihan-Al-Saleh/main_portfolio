<script setup lang="ts">
import { CircleCheck } from '@lucide/vue';
import Reveal from '@/components/site/Reveal.vue';
import SectionHeader from '@/components/site/SectionHeader.vue';
import { useStudioContent } from '@/composables/useStudioContent';
import { resolveIcon } from '@/lib/iconResolver';
import { cn } from '@/lib/utils';
import type { Company } from '@/types';

/**
 * Why work with the studio: the written guarantees as a row of checks, then
 * the reasons as a bento grid whose first card spans two columns.
 */
type Props = {
    company: Company;
};

const props = defineProps<Props>();

const content = useStudioContent(props.company, 'value');

/** Three of six cards span two columns so the bento fills three full rows. */
function wide(index: number): boolean {
    return index === 0 || index === 3 || index === 4;
}
</script>

<template>
    <section id="why" class="site-section scroll-mt-24">
        <div class="site-container">
            <SectionHeader
                :eyebrow="content.eyebrow"
                :title="content.title"
                :highlight="content.highlight"
                :description="content.description"
            >
                <ul
                    v-if="content.guarantees?.length"
                    class="mt-2 flex flex-wrap justify-center gap-x-6 gap-y-2 text-sm font-medium"
                >
                    <li
                        v-for="guarantee in content.guarantees"
                        :key="guarantee"
                        class="inline-flex items-center gap-2"
                    >
                        <CircleCheck class="size-4 text-brand" />
                        {{ guarantee }}
                    </li>
                </ul>
            </SectionHeader>

            <div class="mt-14 grid gap-5 md:grid-cols-3">
                <Reveal
                    v-for="(item, i) in content.items ?? []"
                    :key="item.title"
                    :delay="(i % 3) * 0.08"
                    :class="wide(i) ? 'md:col-span-2' : ''"
                >
                    <article
                        :class="
                            cn(
                                'group ui-card ui-card-hover relative flex h-full flex-col gap-4 overflow-hidden p-7',
                                wide(i) && 'md:p-9',
                            )
                        "
                    >
                        <div
                            v-if="wide(i)"
                            class="ui-blob ui-blob-a -top-24 -right-24 size-64 opacity-30"
                            aria-hidden="true"
                        />
                        <span class="ui-icon-tile relative">
                            <component
                                :is="resolveIcon(item.icon)"
                                class="size-5"
                                aria-hidden="true"
                            />
                        </span>
                        <h3 class="ui-h3 relative">{{ item.title }}</h3>
                        <p
                            class="relative max-w-md text-sm leading-relaxed text-muted-foreground"
                        >
                            {{ item.summary }}
                        </p>
                    </article>
                </Reveal>
            </div>
        </div>
    </section>
</template>
