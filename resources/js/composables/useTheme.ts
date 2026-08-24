import { usePage } from '@inertiajs/vue3';
import type { ComputedRef } from 'vue';
import { computed, ref } from 'vue';

export type SiteTheme = 'stone' | 'terracotta';

export const siteThemes: SiteTheme[] = ['stone', 'terracotta'];

export type UseThemeReturn = {
    theme: ComputedRef<SiteTheme>;
    themes: SiteTheme[];
    setTheme: (value: SiteTheme) => void;
};

const ONE_YEAR_IN_SECONDS = 365 * 24 * 60 * 60;

/**
 * Escolha feita em runtime (sobrepõe a prop compartilhada até o próximo
 * request, quando o cookie passa a valer no servidor).
 */
const clientTheme = ref<SiteTheme | null>(null);

/**
 * Tema do site (stone | terracotta). O valor inicial vem da prop `theme`
 * compartilhada pelo HandleInertiaRequests (cookie > setting > config); o
 * Blade root já aplica o mesmo valor em data-theme, então não há flash.
 */
export function useTheme(): UseThemeReturn {
    const page = usePage();

    const theme = computed<SiteTheme>(
        () => clientTheme.value ?? ((page.props.theme as SiteTheme) || 'stone'),
    );

    function setTheme(value: SiteTheme): void {
        clientTheme.value = value;

        if (typeof document === 'undefined') {
            return;
        }

        document.documentElement.dataset.theme = value;
        document.cookie = `site_theme=${value};path=/;max-age=${ONE_YEAR_IN_SECONDS};SameSite=Lax`;
    }

    return { theme, themes: siteThemes, setTheme };
}
