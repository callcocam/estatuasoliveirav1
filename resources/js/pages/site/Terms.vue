<script setup lang="ts">
import { useT } from '@/composables/useT';
import SiteLayout from '@/layouts/SiteLayout.vue';

defineProps<{ content: string | null }>();

const { t } = useT();
</script>

<template>
    <SiteLayout :title="t('app.site.meta.terms_title')">
        <div class="mx-auto max-w-3xl px-4 py-16 md:px-6 md:py-24">
            <h1 class="font-display text-4xl text-site-primary">
                {{ t('app.site.terms.title') }}
            </h1>
            <div class="mt-10 leading-relaxed text-site-on-surface">
                <p v-if="content" class="whitespace-pre-line">{{ content }}</p>
                <p v-else class="text-site-on-surface-variant">
                    {{ t('app.site.terms.placeholder') }}
                </p>
            </div>
        </div>
    </SiteLayout>
</template>
