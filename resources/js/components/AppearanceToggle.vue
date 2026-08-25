<script setup lang="ts">
import { Monitor, Moon, Sun } from '@lucide/vue';
import { computed } from 'vue';
import type { Appearance } from '@/composables/useAppearance';
import { useAppearance } from '@/composables/useAppearance';
import { useT } from '@/composables/useT';

const { appearance, updateAppearance } = useAppearance();
const { t } = useT();

const icons = { light: Sun, dark: Moon, system: Monitor } as const;

const next: Record<Appearance, Appearance> = {
    light: 'dark',
    dark: 'system',
    system: 'light',
};

const currentLabel = computed(() =>
    t(`app.settings.appearance.themes.${appearance.value}`),
);

function cycle(): void {
    updateAppearance(next[appearance.value]);
}
</script>

<template>
    <button
        type="button"
        class="flex w-full items-center gap-2"
        :title="currentLabel"
        :aria-label="`${t('app.admin.nav.appearance')}: ${currentLabel}`"
        @click="cycle"
    >
        <component :is="icons[appearance]" />
        <span>{{ t('app.admin.nav.appearance') }}</span>
    </button>
</template>
