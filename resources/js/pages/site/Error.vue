<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useT } from '@/composables/useT';
import SiteLayout from '@/layouts/SiteLayout.vue';
import { home } from '@/routes';

defineProps<{ status: number }>();

const { t } = useT();
</script>

<template>
    <SiteLayout :title="t('app.site.errors.not_found_title')">
        <div class="mx-auto flex max-w-2xl flex-col items-center px-4 py-28 text-center">
            <p class="font-display text-7xl text-site-outline">{{ status }}</p>
            <h1 class="mt-6 font-display text-3xl text-site-on-surface">{{ t('app.site.errors.not_found_title') }}</h1>
            <p class="mt-3 text-site-on-surface-variant">{{ t('app.site.errors.not_found_text') }}</p>
            <Link :href="home()" class="mt-8 rounded-site bg-site-primary px-7 py-3 text-sm font-medium text-site-on-primary">
                {{ t('app.site.errors.back_home') }}
            </Link>
        </div>
    </SiteLayout>
</template>
