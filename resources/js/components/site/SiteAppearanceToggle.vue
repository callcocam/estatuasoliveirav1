<script setup lang="ts">
import { Monitor, Moon, Sun } from '@lucide/vue';
import { computed } from 'vue';
import type { Appearance } from '@/composables/useAppearance';
import { useAppearance } from '@/composables/useAppearance';
import { useT } from '@/composables/useT';

withDefaults(defineProps<{ compact?: boolean }>(), { compact: false });

const { appearance, updateAppearance } = useAppearance();
const { t } = useT();

const modes = computed(
    () =>
        [
            { value: 'light', Icon: Sun },
            { value: 'dark', Icon: Moon },
            { value: 'system', Icon: Monitor },
        ] as const satisfies readonly { value: Appearance; Icon: unknown }[],
);

function modeName(value: Appearance): string {
    return t(`app.theme.appearance.modes.${value}`);
}
</script>

<template>
    <div
        :class="[
            'inline-flex gap-1 rounded-full border p-1',
            compact ? 'border-current/30' : 'border-site-outline-variant',
        ]"
        role="radiogroup"
        :aria-label="t('app.theme.appearance.label')"
    >
        <button
            v-for="{ value, Icon } in modes"
            :key="value"
            type="button"
            role="radio"
            :aria-checked="appearance === value"
            :aria-label="
                t('app.theme.appearance.activate', { mode: modeName(value) })
            "
            :title="modeName(value)"
            @click="updateAppearance(value)"
            :class="[
                'flex items-center justify-center rounded-full transition-shadow',
                compact ? 'h-5 w-5' : 'h-6 w-6',
                appearance === value
                    ? compact
                        ? 'ring-2 ring-current/60 ring-offset-1 ring-offset-site-primary-container'
                        : 'ring-2 ring-site-primary ring-offset-2 ring-offset-site-surface'
                    : compact
                      ? 'opacity-70 hover:ring-1 hover:ring-current/40'
                      : 'text-site-on-surface-variant hover:ring-1 hover:ring-site-outline-variant',
            ]"
        >
            <component
                :is="Icon"
                :class="compact ? 'h-3 w-3' : 'h-3.5 w-3.5'"
            />
        </button>
    </div>
</template>
