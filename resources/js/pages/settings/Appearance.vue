<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { useT } from '@/composables/useT';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';

defineOptions({ layout: [AppLayout, SettingsLayout] });

setLayoutProps({
    breadcrumbs: [
        {
            title: 'app.settings.appearance.title',
            href: edit(),
        },
    ],
});

const { t } = useT();
</script>

<template>
    <Head :title="t('app.settings.appearance.title')" />

    <h1 class="sr-only">{{ t('app.settings.appearance.title') }}</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            :title="t('app.settings.appearance.heading')"
            :description="t('app.settings.appearance.heading_description')"
        />
        <AppearanceTabs />
    </div>
</template>
