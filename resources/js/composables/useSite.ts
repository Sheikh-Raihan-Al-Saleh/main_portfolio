import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { Brand, SiteFounder, SiteStudio } from '@/types';

/**
 * The shared site data every page receives (see HandleInertiaRequests):
 * the brand (company name + logos), the studio and the founder.
 */
export function useSite() {
    const page = usePage();

    const brand = computed(() => page.props.brand as Brand);
    const studio = computed(() => page.props.studio as SiteStudio);
    const founder = computed(() => page.props.founder as SiteFounder);

    /**
     * Which face of the site the current page belongs to. Project pages take
     * the face of the project's owner, so personal work wears the founder's
     * accent.
     */
    const face = computed<'studio' | 'founder'>(() => {
        if (page.component === 'public/founder/Index') {
            return 'founder';
        }

        const project = page.props.project as
            { owner?: string; owner_type?: string } | undefined;
        const owner = project?.owner ?? project?.owner_type;

        return owner === 'founder' ? 'founder' : 'studio';
    });

    return { brand, studio, founder, face };
}

/**
 * Just the brand, for components outside the public site (admin sidebar,
 * login page, loader).
 */
export function useBrand() {
    const page = usePage();

    return computed(() => page.props.brand as Brand);
}
