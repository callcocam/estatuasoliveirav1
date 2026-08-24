<script setup lang="ts">
import { useCompany } from '@/composables/useCompany';
import { useT } from '@/composables/useT';
import SiteLayout from '@/layouts/SiteLayout.vue';
import { contact } from '@/routes';
import { Link } from '@inertiajs/vue3';

const { t } = useT();
const { company } = useCompany();
</script>

<template>
    <SiteLayout :title="t('app.site.meta.about_title')" :description="company.about ?? t('app.site.about.subtitle')">
        <div class="mx-auto max-w-4xl px-4 py-16 md:px-6 md:py-24">
            <p class="text-xs font-semibold tracking-[0.2em] text-site-tertiary uppercase">{{ t('app.site.about.subtitle') }}</p>
            <h1 class="mt-4 font-display text-4xl text-site-primary md:text-5xl">{{ t('app.site.about.title') }}</h1>

            <div class="mt-10 space-y-6 text-lg leading-relaxed text-site-on-surface">
                <p v-if="company.about" class="whitespace-pre-line">{{ company.about }}</p>
                <p>{{ t('app.site.about.craft_text') }}</p>
            </div>

            <div class="mt-16 grid gap-6 md:grid-cols-2">
                <div class="rounded-site-card bg-site-surface-container-low p-8">
                    <h2 class="font-display text-2xl text-site-on-surface">{{ t('app.site.about.craft_title') }}</h2>
                    <p class="mt-3 text-sm leading-relaxed text-site-on-surface-variant">{{ t('app.site.about.craft_card_text') }}</p>
                </div>
                <div class="rounded-site-card bg-site-tertiary-container p-8">
                    <h2 class="font-display text-2xl text-site-on-tertiary-container">{{ t('app.site.about.visit_title') }}</h2>
                    <p class="mt-3 text-sm leading-relaxed text-site-on-tertiary-container/90">{{ t('app.site.about.visit_text') }}</p>
                    <p v-if="company.address" class="mt-4 text-sm font-medium text-site-on-tertiary-container">{{ company.address }}</p>
                    <Link
                        :href="contact()"
                        class="mt-6 inline-block rounded-site bg-site-surface px-6 py-2.5 text-sm font-medium text-site-primary transition-colors hover:bg-site-surface-container"
                    >
                        {{ t('app.site.about.visit_button') }}
                    </Link>
                </div>
            </div>
        </div>
    </SiteLayout>
</template>
