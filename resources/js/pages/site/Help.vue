<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useCompany } from '@/composables/useCompany';
import { useT } from '@/composables/useT';
import SiteLayout from '@/layouts/SiteLayout.vue';
import { contact, privacy, terms } from '@/routes';

defineProps<{
    faq: { question: string; answer: string }[];
}>();

const { t } = useT();
const { whatsappUrl, whatsappUrlWithMessage } = useCompany();
</script>

<template>
    <SiteLayout
        :title="t('app.site.meta.help_title')"
        :description="t('app.site.help.intro')"
    >
        <div class="mx-auto max-w-3xl px-4 py-16 md:px-6 md:py-24">
            <p
                class="text-xs font-semibold tracking-[0.2em] text-site-tertiary uppercase"
            >
                {{ t('app.site.help.subtitle') }}
            </p>
            <h1
                class="mt-4 font-display text-4xl text-site-primary md:text-5xl"
            >
                {{ t('app.site.help.title') }}
            </h1>
            <p class="mt-6 text-lg leading-relaxed text-site-on-surface">
                {{ t('app.site.help.intro') }}
            </p>

            <h2 class="mt-14 font-display text-2xl text-site-on-surface">
                {{ t('app.site.help.faq_title') }}
            </h2>
            <section
                v-for="item in faq"
                :key="item.question"
                class="mt-8"
            >
                <h3 class="font-display text-xl text-site-primary">
                    {{ item.question }}
                </h3>
                <p
                    class="mt-3 leading-relaxed whitespace-pre-line text-site-on-surface"
                >
                    {{ item.answer }}
                </p>
            </section>

            <div class="mt-16 rounded-site-card bg-site-tertiary-container p-8">
                <h2
                    class="font-display text-2xl text-site-on-tertiary-container"
                >
                    {{ t('app.site.help.contact_title') }}
                </h2>
                <p
                    class="mt-3 text-sm leading-relaxed text-site-on-tertiary-container/90"
                >
                    {{ t('app.site.help.contact_text') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a
                        v-if="whatsappUrl"
                        :href="whatsappUrlWithMessage ?? whatsappUrl"
                        target="_blank"
                        rel="noopener"
                        class="inline-block rounded-site bg-site-surface px-6 py-2.5 text-sm font-medium text-site-primary transition-colors hover:bg-site-surface-container"
                    >
                        {{ t('app.site.help.whatsapp_button') }}
                    </a>
                    <Link
                        :href="contact()"
                        class="inline-block rounded-site bg-site-surface px-6 py-2.5 text-sm font-medium text-site-primary transition-colors hover:bg-site-surface-container"
                    >
                        {{ t('app.site.help.contact_button') }}
                    </Link>
                </div>
            </div>

            <p class="mt-10 text-sm text-site-on-surface-variant">
                {{ t('app.site.help.legal_text') }}
                <Link
                    :href="terms()"
                    class="text-site-primary transition-colors hover:underline"
                >
                    {{ t('app.site.help.legal_terms') }}
                </Link>
                ·
                <Link
                    :href="privacy()"
                    class="text-site-primary transition-colors hover:underline"
                >
                    {{ t('app.site.help.legal_privacy') }}
                </Link>
            </p>
        </div>
    </SiteLayout>
</template>
