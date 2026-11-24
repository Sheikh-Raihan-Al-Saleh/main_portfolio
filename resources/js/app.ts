import { createInertiaApp } from '@inertiajs/vue3';
import { defineAsyncComponent } from 'vue';
import PublicLayout from '@/components/Layout/PublicLayout.vue';
import { pageTitle, trackBrandName } from '@/composables/brandTitle';
import { initializeFlashToast } from '@/composables/flashToast';
import { initializeTheme } from '@/composables/useAppearance';

// Only the public shell ships in the entry bundle. The admin, auth and
// settings shells (sidebar, dropdowns, dialogs) load on demand, so visitors
// to the public site never download them.
const AppLayout = defineAsyncComponent(
    () => import('@/components/Layout/AppLayout.vue'),
);
const AuthLayout = defineAsyncComponent(
    () => import('@/components/Layout/AuthLayout.vue'),
);
const SettingsLayout = defineAsyncComponent(
    () => import('@/components/Layout/SettingsLayout.vue'),
);

// Pages are grouped by module (pages/<module>/), so the public site pages are
// listed by name; every other non-auth, non-settings page is an admin page.
const publicPages = new Set<string>([
    'studio/HomePage',
    'studio/AboutPage',
    'founder/FounderPage',
    'project/ProjectArchive',
    'project/ShowProject',
    'landingpage/ShowLandingPage',
]);

createInertiaApp({
    title: pageTitle,
    layout: (name) => {
        switch (true) {
            case publicPages.has(name):
                return PublicLayout;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

// Page titles end with the company name, kept current on every visit.
trackBrandName();

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
