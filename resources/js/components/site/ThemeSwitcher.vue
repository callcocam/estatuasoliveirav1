<script setup lang="ts">
import { useT } from '@/composables/useT';
import type { SiteTheme } from '@/composables/useTheme';
import { useTheme } from '@/composables/useTheme';

withDefaults(defineProps<{ compact?: boolean }>(), { compact: false });

const { theme, themes, setTheme } = useTheme();
const { t } = useT();

/**
 * Cor de amostra exibida no botão de cada tema (tokens site-primary
 * definidos em resources/css/themes.css por data-theme).
 */
const swatches: Record<SiteTheme, string> = {
    stone: 'bg-[#4a5568]',
    terracotta: 'bg-[#9c4a2f]',
};

function themeName(value: SiteTheme): string {
    return t(`app.theme.names.${value}`);
}
</script>

<template>
    <div
        :class="[
            'inline-flex gap-1 rounded-full border p-1',
            compact ? 'border-current/30' : 'border-site-outline-variant',
        ]"
        role="radiogroup"
        :aria-label="t('app.theme.switcher.label')"
    >
        <button
            v-for="value in themes"
            :key="value"
            type="button"
            role="radio"
            :aria-checked="theme === value"
            :aria-label="
                t('app.theme.switcher.activate', { theme: themeName(value) })
            "
            :title="themeName(value)"
            @click="setTheme(value)"
            :class="[
                'flex items-center justify-center rounded-full transition-shadow',
                compact ? 'h-5 w-5' : 'h-6 w-6',
                theme === value
                    ? compact
                        ? 'ring-2 ring-current/60 ring-offset-1 ring-offset-site-primary-container'
                        : 'ring-2 ring-site-primary ring-offset-2 ring-offset-site-surface'
                    : compact
                      ? 'hover:ring-1 hover:ring-current/40'
                      : 'hover:ring-1 hover:ring-site-outline-variant',
            ]"
        >
            <span
                :class="[
                    'rounded-full',
                    compact ? 'h-3 w-3' : 'h-4 w-4',
                    swatches[value],
                ]"
            />
        </button>
    </div>
</template>
