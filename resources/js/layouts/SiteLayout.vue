<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import CookieConsentBanner from '@/components/site/CookieConsentBanner.vue';
import SiteFooter from '@/components/site/SiteFooter.vue';
import SiteHeader from '@/components/site/SiteHeader.vue';
import WhatsAppButton from '@/components/site/WhatsAppButton.vue';
import { useCompany } from '@/composables/useCompany';

const props = defineProps<{
    title: string;
    description?: string | null;
    ogImage?: string | null;
}>();

const { company } = useCompany();

const fullTitle = computed(() => `${props.title} · ${company.value.name}`);
</script>

<template>
    <Head :title="title">
        <meta v-if="description" name="description" :content="description" />
        <meta property="og:title" :content="fullTitle" />
        <meta
            v-if="description"
            property="og:description"
            :content="description"
        />
        <meta property="og:type" content="website" />
        <meta v-if="ogImage" property="og:image" :content="ogImage" />
    </Head>

    <div
        class="flex min-h-screen flex-col bg-site-surface font-site text-site-on-surface antialiased"
    >
        <SiteHeader />
        <main class="flex-1">
            <slot />
        </main>
        <SiteFooter />
        <WhatsAppButton floating />
        <CookieConsentBanner />
    </div>
</template>
