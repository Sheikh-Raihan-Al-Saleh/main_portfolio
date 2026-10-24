import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { ComputedRef } from 'vue';
import { getInitials } from '@/composables/useInitials';
import { useScrollSpy } from '@/composables/useScrollSpy';
import { useSiteOwner } from '@/composables/useSiteOwner';
import type { FooterOwner, Profile } from '@/types';

export type NavItem = { id: string; label: string };

export type PublicNav = {
    isCompanySite: ComputedRef<boolean>;
    /** In-page chapter links for the page being rendered. */
    sections: ComputedRef<NavItem[]>;
    /** Whole-page links, minus anything the section links already cover. */
    pageLinks: ComputedRef<NavItem[]>;
    activeSection: ComputedRef<string | null>;
    /** The route that owns the current face's sections. */
    homeHref: ComputedRef<string>;
    brandLogoUrl: ComputedRef<string | null>;
    initials: ComputedRef<string>;
    owner: ComputedRef<FooterOwner | undefined>;
    profile: ComputedRef<Profile | undefined>;
    sectionHref: (id: string) => string;
    pageLinkHref: (id: string) => string | null;
    isCurrentPageLink: (id: string) => boolean;
};

/**
 * Navigation for whichever face of the public site is being rendered.
 *
 * Both the studio header and the founder header read from here, so the rules
 * for which links exist, where they point and which one is lit live in one
 * place. Call it once per layout: it owns a scroll-spy observer.
 */
export function usePublicNav(): PublicNav {
    const page = usePage();
    const { scope, owner, profile, company } = useSiteOwner();

    const isCompanySite = computed(() => scope.value === 'company');
    const isHome = computed(
        () => page.url === '/' || page.url.startsWith('/?'),
    );
    const isProjectsRoute = computed(() => page.url.startsWith('/projects'));
    const isAboutRoute = computed(() => page.url.startsWith('/about'));
    const isFounderRoute = computed(() => page.url.startsWith('/founder'));

    /**
     * Only sections that exist on the page are listed — a link to a missing
     * anchor is worse than no link. The client strip renders nothing without
     * clients, so it is only offered when there are some.
     */
    const hasClients = computed(
        () =>
            ((page.props as { clients?: unknown[] }).clients?.length ?? 0) > 0,
    );

    const sections = computed<NavItem[]>(() => {
        if (!isCompanySite.value) {
            return [
                { id: 'about', label: 'About' },
                { id: 'skills', label: 'Skills' },
                { id: 'projects', label: 'Work' },
                { id: 'experience', label: 'Experience' },
                { id: 'contact', label: 'Contact' },
            ];
        }

        const clients = hasClients.value
            ? [{ id: 'clients', label: 'Clients' }]
            : [];

        if (isAboutRoute.value) {
            return [
                ...clients,
                { id: 'founder', label: 'Founder' },
                { id: 'contact', label: 'Contact' },
            ];
        }

        if (isHome.value) {
            return [
                { id: 'work', label: 'Work' },
                ...clients,
                { id: 'contact', label: 'Contact' },
            ];
        }

        // The archive and project pages have no chapters of their own, so these
        // point back at the home page sections they name.
        return [
            { id: 'work', label: 'Work' },
            { id: 'contact', label: 'Contact' },
        ];
    });

    function pageLinkHref(id: string): string | null {
        switch (id) {
            case 'about':
                return isAboutRoute.value ? null : '/about';
            case 'archive':
                return isProjectsRoute.value ? null : '/projects';
            case 'founder':
                return isFounderRoute.value ? null : '/founder';
            case 'studio':
                return isCompanySite.value ? null : '/';
            default:
                return null;
        }
    }

    function isCurrentPageLink(id: string): boolean {
        switch (id) {
            case 'about':
                return isAboutRoute.value;
            case 'archive':
                return isProjectsRoute.value;
            case 'founder':
                return isFounderRoute.value;
            default:
                return false;
        }
    }

    const pageLinks = computed<NavItem[]>(() => {
        const links = isCompanySite.value
            ? [
                  { id: 'about', label: 'About' },
                  { id: 'archive', label: 'Archive' },
                  { id: 'founder', label: 'Founder' },
              ]
            : [{ id: 'studio', label: 'Studio' }];

        const sectionLabels = sections.value.map((section) => section.label);

        return links.filter(
            (link) =>
                !sectionLabels.includes(link.label) &&
                pageLinkHref(link.id) !== null,
        );
    });

    const navSectionIds = computed(() =>
        sections.value.map((section) => section.id),
    );

    /**
     * Chapters without a navbar link of their own. They are observed anyway so
     * the highlight switches off inside them instead of leaving the previous
     * section lit.
     */
    const unlinkedRegions = computed(() =>
        isHome.value
            ? [
                  'capabilities',
                  'engineering',
                  'why',
                  'about',
                  'founder-work',
                  'start',
              ]
            : [],
    );

    const observedId = useScrollSpy(() => [
        ...navSectionIds.value,
        ...unlinkedRegions.value,
    ]);

    const activeSection = computed(() =>
        navSectionIds.value.includes(observedId.value ?? '')
            ? observedId.value
            : null,
    );

    const homeHref = computed(() => (isCompanySite.value ? '/' : '/founder'));

    /** Sections scroll on the page that owns them, and jump there from elsewhere. */
    function sectionHref(id: string): string {
        if (isHome.value || isAboutRoute.value || isFounderRoute.value) {
            return `#${id}`;
        }

        return `${homeHref.value}#${id}`;
    }

    /**
     * The website logo from Admin → Founder → Website logo, with the studio
     * logo as a fallback for older uploads. Null means the initials mark.
     */
    const brandLogoUrl = computed(
        () => profile.value?.logo_url ?? company.value?.logo_url ?? null,
    );

    const initials = computed(() => getInitials(owner.value?.name));

    return {
        isCompanySite,
        sections,
        pageLinks,
        activeSection,
        homeHref,
        brandLogoUrl,
        initials,
        owner,
        profile,
        sectionHref,
        pageLinkHref,
        isCurrentPageLink,
    };
}
