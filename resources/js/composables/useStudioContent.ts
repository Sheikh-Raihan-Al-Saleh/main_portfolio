import { computed } from 'vue';
import type { ComputedRef } from 'vue';
import type { Company, StudioContent } from '@/types';

/**
 * Read one named section of the studio copy off the company record.
 *
 * `studio_content` arrives fully merged from the backend, so this never
 * returns undefined for a known key — but a caller may still pass a partial
 * or absent company (tests, previews), hence the defensive access rather
 * than a direct typed read.
 */
export function useStudioContent<K extends keyof StudioContent>(
    company: Company | undefined | null,
    section: K,
): ComputedRef<StudioContent[K]> {
    return computed(() => {
        const content = company?.studio_content as
            | Record<string, unknown>
            | undefined;

        return (content?.[section] ?? {}) as StudioContent[K];
    });
}
