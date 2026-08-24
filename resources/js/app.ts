import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import { initializeFlashToast } from '@/lib/flashToast';

/**
 * Prefer the company name shared by the backend (settings table, via the
 * initial Inertia page payload) so every tab title carries the site identity.
 */
function resolveAppName(): string {
    if (typeof document !== 'undefined') {
        try {
            const page = JSON.parse(
                document.getElementById('app')?.dataset.page ?? 'null',
            );
            const siteName = page?.props?.site?.name;

            if (typeof siteName === 'string' && siteName !== '') {
                return siteName;
            }
        } catch {
            // Fall through to the env-based name below.
        }
    }

    return import.meta.env.VITE_APP_NAME || 'Estátuas Oliveira';
}

const appName = resolveAppName();

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
