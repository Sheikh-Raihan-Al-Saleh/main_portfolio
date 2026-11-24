import { router } from '@inertiajs/vue3';

/**
 * Page titles end with the company name ("Work — Acme Studio"), which comes
 * from the shared `brand` prop, not from a build-time env variable, so a
 * rename in Admin → Studio shows up in the browser tab straight away.
 */
let brandName = readInitialBrand();

/** The brand name from the first page's JSON, before Inertia boots. */
function readInitialBrand(): string {
    try {
        const element =
            document.querySelector('script[data-page]') ??
            document.getElementById('app');
        const raw = element?.textContent?.trim().startsWith('{')
            ? element.textContent
            : (element as HTMLElement | null)?.dataset.page;
        const page = raw ? JSON.parse(raw) : null;

        return page?.props?.brand?.name ?? '';
    } catch {
        return '';
    }
}

/** Keep the name current after every Inertia visit. */
export function trackBrandName(): void {
    router.on('navigate', (event) => {
        const brand = event.detail.page.props.brand as
            { name?: string } | undefined;
        brandName = brand?.name ?? brandName;
    });
}

/** Build a document title: "Page — Brand", or just the brand. */
export function pageTitle(title: string): string {
    if (!brandName) {
        return title;
    }

    return title && title !== brandName ? `${title} — ${brandName}` : brandName;
}
