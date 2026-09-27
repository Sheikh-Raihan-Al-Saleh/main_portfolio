import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { ComputedRef } from 'vue';
import type { Company, FooterOwner, Profile, Project } from '@/types';

type Scope = 'company' | 'personal';

export type SiteOwner = {
    /** Which of the two sites the current page belongs to. */
    scope: ComputedRef<Scope>;
    /** The record that owns the branding, or undefined on a bare install. */
    owner: ComputedRef<FooterOwner | undefined>;
    company: ComputedRef<Company | undefined>;
    profile: ComputedRef<Profile | undefined>;
};

/**
 * Resolves who the current public page is being presented as.
 *
 * The site has two faces. The company owns the home route, the About page and
 * the project archive; the founder owns /founder and any personal project. Both
 * share a layout, so branding, navigation and the footer have to be told which
 * one they are currently rendering for.
 *
 * Project routes are ambiguous on their own — `/projects/{slug}` serves both
 * company work and the founder's own — so they are resolved from the project's
 * context rather than the URL.
 */
export function useSiteOwner(): SiteOwner {
    const page = usePage();

    const company = computed(() => page.props.company as Company | undefined);
    const profile = computed(() => page.props.profile as Profile | undefined);

    const scope = computed<Scope>(() => {
        const path = page.url.split('?')[0] ?? '/';

        // The founder's own portfolio is the only route on his side of the site.
        if (path === '/founder' || path === '/resume') {
            return 'personal';
        }

        if (path === '/') {
            return 'company';
        }

        if (path === '/projects') {
            // The archive itself is the company's.
            return 'company';
        }

        if (path.startsWith('/projects/') || path.startsWith('/p/')) {
            const project = page.props.project as Project | undefined;

            return project?.context === 'personal' ? 'personal' : 'company';
        }

        // The company About page, and anything else that falls through.
        return 'company';
    });

    const owner = computed<FooterOwner | undefined>(() => {
        const companyRecord = company.value;
        const profileRecord = profile.value;

        if (scope.value === 'company') {
            return companyRecord ?? profileRecord;
        }

        return profileRecord ?? companyRecord;
    });

    return {
        scope,
        owner,
        company,
        profile,
    };
}
