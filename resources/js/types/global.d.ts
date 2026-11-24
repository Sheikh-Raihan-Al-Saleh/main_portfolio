import type { Auth } from '@/types/auth';
import type { UploadLimits } from '@/types/ui';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            /** PHP upload limits in bytes (null = unlimited); null for guests. */
            uploadLimits?: UploadLimits | null;
            [key: string]: unknown;
        };
    }
}

// The route list printed into the page by the @routes Blade directive
// (Ziggy) as a global `const`: a global binding, not a property of window.
declare global {
    const Ziggy: {
        routes: Record<string, { uri: string; methods: string[] }>;
    };
}

declare module 'vue' {
    interface ComponentCustomProperties {
        $inertia: typeof Router;
        $page: Page;
        $headManager: ReturnType<typeof createHeadManager>;
    }
}
