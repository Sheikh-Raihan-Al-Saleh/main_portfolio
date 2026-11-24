import { router } from '@inertiajs/vue3';
import { readonly, ref } from 'vue';
import { toast } from 'vue-sonner';
import { route } from '@/composables/useRoute';
import type { BeforeInstallPromptEvent, UsePwaReturn } from '@/types';

/**
 * The admin panel as an installable app (see resources/pwa/sw.js).
 *
 * Registers the service worker, offers the browser's install prompt, and
 * keeps the app current: the worker is checked for a new version every hour
 * and whenever the window comes back into view. When a deploy lands, the new
 * worker takes over by itself and the next page visit loads fresh, so open
 * forms are never thrown away mid-edit; a toast offers to reload sooner.
 */

const UPDATE_CHECK_MS = 60 * 60 * 1000;

const installPrompt = ref<BeforeInstallPromptEvent | null>(null);
const canInstall = ref(false);

let started = false;
let updateReady = false;

export function usePwa(): UsePwaReturn {
    if (!started && typeof window !== 'undefined') {
        started = true;
        linkManifest();
        listenForInstallPrompt();
        void registerWorker();
    }

    return { canInstall: readonly(canInstall), install };
}

async function install(): Promise<void> {
    const prompt = installPrompt.value;

    if (!prompt) {
        return;
    }

    await prompt.prompt();
    const { outcome } = await prompt.userChoice;

    installPrompt.value = null;
    canInstall.value = false;

    if (outcome === 'accepted') {
        toast.success('Admin installed as an app.');
    }
}

/**
 * The manifest link is server-rendered on admin pages; this adds it when the
 * admin was reached by an in-app visit (after logging in, say), so the
 * browser still offers to install.
 */
function linkManifest(): void {
    if (document.querySelector('link[rel="manifest"]')) {
        return;
    }

    const link = document.createElement('link');
    link.rel = 'manifest';
    link.href = route('pwaManifest');
    document.head.append(link);
}

function listenForInstallPrompt(): void {
    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        installPrompt.value = event as BeforeInstallPromptEvent;
        canInstall.value = true;
    });

    window.addEventListener('appinstalled', () => {
        installPrompt.value = null;
        canInstall.value = false;
    });
}

async function registerWorker(): Promise<void> {
    if (!('serviceWorker' in navigator) || !window.isSecureContext) {
        return;
    }

    // A page that already had a worker is being updated, not installed.
    const hadController = Boolean(navigator.serviceWorker.controller);

    navigator.serviceWorker.addEventListener('controllerchange', () => {
        if (hadController) {
            onUpdateReady();
        }
    });

    try {
        const registration = await navigator.serviceWorker.register(
            route('pwaServiceWorker'),
            { scope: '/', updateViaCache: 'none' },
        );

        const check = () => registration.update().catch(() => undefined);

        window.setInterval(check, UPDATE_CHECK_MS);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                check();
            }
        });
    } catch {
        // No worker (private mode, blocked by policy): the admin still works.
    }
}

/** A new version is live: load it on the next visit, or now if asked. */
function onUpdateReady(): void {
    if (updateReady) {
        return;
    }

    updateReady = true;

    router.on('before', (event) => {
        const visit = event.detail.visit;

        if (visit.method !== 'get') {
            return;
        }

        window.location.href = visit.url.href;

        return false;
    });

    toast('A new version of the admin is ready.', {
        description: 'It loads on your next page change.',
        duration: 15000,
        action: {
            label: 'Reload now',
            onClick: () => window.location.reload(),
        },
    });
}
