import { createInertiaApp } from '@inertiajs/vue3';
import { defineAsyncComponent } from 'vue';
import { initializeTheme } from '@/composables/useAppearance';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { pageTitle, trackBrandName } from '@/lib/brandTitle';
import { initializeFlashToast } from '@/lib/flashToast';

// Only the public shell ships in the entry bundle. The admin, auth and
// settings shells (sidebar, dropdowns, dialogs) load on demand, so visitors
// to the public site never download them.
const AppLayout = defineAsyncComponent(() => import('@/layouts/AppLayout.vue'));
const AuthLayout = defineAsyncComponent(
    () => import('@/layouts/AuthLayout.vue'),
);
const SettingsLayout = defineAsyncComponent(
    () => import('@/layouts/settings/Layout.vue'),
);

createInertiaApp({
    title: pageTitle,
    layout: (name) => {
        switch (true) {
            case name.startsWith('public/'):
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
