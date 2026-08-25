<script setup lang="ts">
import { Monitor, Moon, Sun } from '@lucide/vue';
import { computed } from 'vue';
import { useAppearance } from '@/composables/useAppearance';
import { useT } from '@/composables/useT';

const { appearance, updateAppearance } = useAppearance();
const { t } = useT();

const tabs = computed(
    () =>
        [
            {
                value: 'light',
                Icon: Sun,
                label: t('app.settings.appearance.themes.light'),
            },
            {
                value: 'dark',
                Icon: Moon,
                label: t('app.settings.appearance.themes.dark'),
            },
            {
                value: 'system',
                Icon: Monitor,
                label: t('app.settings.appearance.themes.system'),
            },
        ] as const,
);
</script>

<template>
    <div class="inline-flex gap-1 rounded-lg bg-muted p-1">
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            @click="updateAppearance(value)"
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                appearance === value
                    ? 'bg-background text-foreground shadow-xs'
                    : 'text-muted-foreground hover:bg-muted-foreground/10 hover:text-foreground',
            ]"
        >
            <component :is="Icon" class="-ml-1 h-4 w-4" />
            <span class="ml-1.5 text-sm">{{ label }}</span>
        </button>
    </div>
</template>
